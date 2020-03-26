<?php



     $message = '';
     include('route.php');
     include('functions.php');
     $config = include ('config.php') ;
     bootstrap();
     include ('header.phtml') ;

     try {
          resolveRoute($config);
     } catch (\Exception $e) {

     }



     // try {
     //      if (validateParams($_POST)) {
     //           registerUser($_POST);
     //           echo "OK";
     //      }
     //      else {
     //           include('loginForm.phtml');
     //      }
     //
     // } catch (Exception $e) {
     //      echo $e -> getMessage();
     //      die();
     // }


 ?>
