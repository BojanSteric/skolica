<?php

#region ValidateForm Functions

function validateRegisterForm($params)
{
    if (!is_array($params)) {
        throw new Exception('Given param is not an array');
    }
    if (isset($params['email']) and isset($params['password']) and isset($params['password-2'])) {
        if ((strlen($params['email']) > 6 and strlen($params['email'] <= 20) and strstr($params['email'], '@', true)) and
            (strlen($params['password']) > 6 and strlen($params['password']) <= 14) and ($params['password-2'] === $params['password'])
        ) {
            return true;
        } else {
            echo "nije ok";
            return false;
        }
    } else {
        return false;
    }
}

function validateUserForm(array $params)
{
    if (isset($params['email']) and isset($params['password']) and
        isset($params['password-2']) and isset($params['firstName']) and
        isset($params['lastName']) and isset($params['username'])) {
        if (
            (strlen($params['email']) > 6 and strlen($params['email'] <= 20) and strstr($params['email'], '@', true)) and
            (strlen($params['password']) > 6 and strlen($params['password']) <= 14) and
            ($params['password-2'] === $params['password']) and
            (strlen($params['firstName']) > 2 and strlen($params['firstName']) < 32) and
            (strlen($params['lastName']) > 2 and strlen($params['lastName']) < 32) and
            (strlen($params['username']) > 2 and strlen($params['username']) < 32)
            // (strlen($params['email']) > 6 and strlen($params['email'] <= 20) and strstr($params['email'], '@', true)) and
            // (strlen($params['password']) > 6 and strlen($params['password']) <= 14) and
            // ($params['password-2'] === $params['password']) and
            // (strlen($params['firstName']) > 2 and strlen($params['firstName']) < 32 and preg_match("/[^a-zA-Z\_-]/i", $params['firstName'])) and
            // (strlen($params['lastName']) > 2 and strlen($params['lastName']) < 32 and preg_match("/[^a-zA-Z\_-]/i", $params['lastName'])) and
            // (strlen($params['username']) > 2 and strlen($params['username']) < 32 and preg_match("/[^a-zA-Z0-9\_-]/i", $params['username']))
        ) {
            return true;
        } else {
            echo "nije ok";
            return false;
        }
    } else {
        return false;
    }
}

function validateArticleForm()
{
    if (isset($_POST['body']) && strlen($_POST['body']) < 1 || isset($_POST['category']) && strlen($_POST['category']) < 1 || isset($_POST['user']) && strlen($_POST['user']) < 1) {
        return false;
    }
    return true;

}

function validateLoginForm(array $params)
{
    if (isset($params['email']) and isset($params['password'])) {
        if ((strlen($params['email']) > 6 and strlen($params['email'] <= 20) and strstr($params['email'], '@', true)) and
            (strlen($params['password']) > 6 and strlen($params['password']) <= 14)
        ) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

#endregion

#region LOGIN

function login($email, $password)
{
    $user = getUserByEmail($email);
    if (!$user) {
        return false;
    }
    if ($user->password === createPasswordHash($password)) {
        $_SESSION['isLoggedIn'] = true;
        return true;
    }
    return false;
}

function isLoggedIn()
{
    if (isset($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn'] === true) {
        return true;
    }

    return false;
}

function createPasswordHash($password)
{
    return md5($password);
}

function logOut()
{
    unset($_SESSION);
    session_destroy();
}

#endregion

#region User Functions

function saveUser($params)
{
    $userData = [
        'email' => $params['email'],
        'password' => createPasswordHash($params['password']),
        'firstName' => $params['firstName'],
        'lastName' => $params['lastName'],
        'username' => $params['username'],
        'image' => saveImage(),
        'status' => $params['status']
    ];
    $tmp = file_get_contents('storage.json');
    if (strlen($tmp) === 0) {
        $data = [$userData];
    } else {
        $data = json_decode($tmp);
        $data[] = $userData;
    }

    return file_put_contents('storage.json', json_encode($data));
}

function deleteUser($email){
	
	$data = file_get_contents('storage.json');
	$userData = json_decode($data, true);
	$indices = [];
	
	foreach ($userData as $key => $value)
	{
		if ($value['email'] == $email)
		{
			$indices[] = $key;
		}
	}

	foreach ($indices as $i)
	{
		unset($userData[$i]);
	}

	$userData = array_values($userData);
	file_put_contents('storage.json', json_encode($userData));
}

function updateUser($params)
{	
	$data = file_get_contents('storage.json');
	$userData = json_decode($data, true);

	foreach ($userData as $key => $value) {
		if ($value['email'] == $params['email']) {
			$userData[$key]=[
				'email' => $params['email'],
				'password' => createPasswordHash($params['password']),
				'firstName' => $params['firstName'],
				'lastName' => $params['lastName'],
				'username' => $params['username'],
				'image' => saveImage(),
				'status' => $params['status']
			];
		}
	}
	
	file_put_contents('storage.json', json_encode($userData));
	
}

function registerUser($params)
{
    $data = file_get_contents('storage.json');
    $data .= json_encode(['email' => $params['email'], 'password' => $params['password']]) . PHP_EOL;
    file_put_contents('storage.json', $data);
}

function getUserByEmail($email)
{
    foreach (getUsers() as $user) {
        if ($email === $user->email) {
            return $user;
        }
    }

    return false;
}

function getUsers()
{
    $users = file_get_contents('storage.json');
    return json_decode($users);
}

#endregion

#region Other Func
function saveImage()
{
    $fileName = APP_PATH . '/images/' . $_FILES['image']['name'];
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $fileName)) {
        throw new \Exception("Nismo snimili sliku");
    }
    return 'images/' . $_FILES['image']['name'];
}

function bootstrap()
{
    define('APP_PATH', __DIR__);
    session_start();
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

function redirect($baseUrl, $route = '', $statusCode = 302)
{
    header('Location: ' . $baseUrl . $route, $statusCode);
}
#endregion

#region Article Functions

function getLastArticleId(){
    $tmp= file_get_contents('article.json');
    $id=0;
    if(strlen($tmp)!==0){
        $data = json_decode($tmp);
        foreach($data as $ret){
            $id = $ret->articleId;
        }
    }
    return $id;
}

function saveArticleForm($params)
{
    $id = getLastArticleId();

    //$fileName = saveImage();
    $articleData = [
        'articleId' => ++$id,
        'title' => $params['title'],
        'description' => $params['description'],
        'body' => $params['body'],
        'category' => $params['category'],
        'user' => $params['user'],
        //'image' => saveImage()
    ];
    $tmp= file_get_contents('article.json');
    if (strlen($tmp) === 0) {
        $data = [$articleData];
    } else {
        $data = json_decode($tmp);
        $data[] = $articleData;
    }
    return file_put_contents('article.json', json_encode($data));
}

function prepareArticleData($articleData, $articleId){
    foreach ($articleData as $key => $value) {

        if ($value->articleId == $articleId) {

                $articleData[$key]=[
                'articleId' => intval($articleId),
                'title' => $_POST['title'],
                'description' =>$_POST['description'],
                'body' => $_POST['body'],
                'category' => $_POST['category'],
                'user' => $_POST['user']
            ];
        }

    }
    return $articleData;
}

function updateArticle($params)
{
    $data = getArticles();

    $articleData = prepareArticleData($data, $params);

    file_put_contents('article.json', json_encode($articleData));

}

function getArticleByTitle($title)
{
    foreach (getArticles() as $article) {
        if ($title === $article->title) {
            return $article;
        }
    }

    return false;
}

function getArticles()
{
    $articles = file_get_contents('article.json');
    return json_decode($articles);
}

function deleteArticle($articleId){

	$data = file_get_contents('article.json');
	$articleData = json_decode($data, true);
	$indices = [];
	
	foreach ($articleData as $key => $value)
	{
		if ($value['articleId'] == $articleId)
		{
			$indices[] = $key;
		}
	}

	foreach ($indices as $i)
	{
		unset($articleData[$i]);
	}

	$articleData = array_values($articleData);
	file_put_contents('article.json', json_encode($articleData));
}
#endregion

#region Category Functions
function saveCategoryForm($params)
{
    $id = getLastCategoryId($params);

    $userData = [
        'categoryId' => ++$id,
        'category' => $params['category']
    ];
    $tmp = file_get_contents('category.json');
    if (strlen($tmp) === 0) {
        $data = [$userData];
    } else {
        $data = json_decode($tmp);
        $data[] = $userData;
    }
    return file_put_contents('category.json', json_encode($data));
}

function getCategory()
{
    $categories = file_get_contents('category.json');
    return json_decode($categories);
}

function getCategoryByCat($cat){
    foreach(getCategory() as $category){
        if($category->category === $cat){
            return $category;
        }
    }
    return false;
}

function getLastCategoryId(){
    $tmp= file_get_contents('category.json');
    $id=0;
    if(strlen($tmp)!==0){
        $data = json_decode($tmp);
        foreach($data as $ret){
            $id = $ret->categoryId;
        }
    }
    return $id;

}

function deleteCategory($param){
	
	
	$categoryData = getCategory();
	$indices = [];
	
	foreach ($categoryData as $key=>$value)
	{

		if ($value->categoryId == $param['categoryId'])
		{
			$indices[] = $key;
		}
	}

	foreach ($indices as $i)
	{
		unset($categoryData[$i]);
	}

	$categoryData = array_values($categoryData);
	file_put_contents('category.json', json_encode($categoryData));
}

function updateCategory($param1, $param2){
    $categoryData = getCategory();

    foreach ($categoryData as $key => $value) {

        if ($value->categoryId == $param1) {

            $categoryData[$key]=[
                'categoryId' => intval($param1),
                'category' => $param2];
        }
    }

    file_put_contents('category.json', json_encode($categoryData));
}
#endregion

?>
