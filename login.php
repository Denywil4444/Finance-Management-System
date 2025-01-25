<?php

@include 'config.php';

session_start();

if(isset($_POST['submit'])){

   /*$name = mysqli_real_escape_string($conn, $_POST['name']);*/
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = md5($_POST['password']);
   /*$cpass = md5($_POST['cpassword']);*/
   /*$user_type = $_POST['user_type'];*/

   $select = " SELECT * FROM user_form WHERE email = '$email' && password = '$pass' ";

   $result = mysqli_query($conn, $select);

   if(mysqli_num_rows($result) > 0){

      $row = mysqli_fetch_array($result);

      if($row['user_type'] == 'admin'){

         $_SESSION['admin_name'] = $row['name'];
         header('location:admin.php');

      }elseif($row['user_type'] == 'user'){

         $_SESSION['user_name'] = $row['name'];
         header('location:user.php');

      }
     
   }else{
      $error[] = 'incorrect email or password!';
   }

};
?>




<!DOCTYPE html>
<html>
    <head>
        <title>Finance Management System</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>

    <body>
        <img src="images/logo.png" alt="logo" class="logo">
        <div class="login-container">

            <div class="image-container">
            
                <div class="overlay"></div>
                <img src="images/finance.png" alt="money" class="money">
                <p class="money-description"><span>Financial</span> <br> Management System</p>
            </div>

            <div class="login-div">
                <div class="overlay"></div>
                <form action="" method="post">
                    
                    <br>

                    <h1>WELCOME</h1>
                    <p>Sign into your account</p>
                    <br>
                    <?php
                        if(isset($error)){
                                foreach($error as $error){
                                echo '<span class="error-msg">'.$error.'</span>';
                            };
                        };
                    ?>



                    <input type="text" name="email" placeholder="Email">
                    <br>
                    <br>
                    <input type="password" name="password" placeholder="Password">
                    <br>
                    <br>
                    <p class="forgot">Forgot Password</p>
                    <br>
                    <input type="submit" name="submit" value="Login" class="login-button">
                    <br>
                    <br>
                    <p>Not registered yet?<span class="reg"><a href="register.php">Register Now</a></span></p>

                    
                    
                    <p class="left-down">All rights reserved</p>
                    <p class="right-down">Terms and Use Privacy Policy</p>

                </form>

                
                
            </div>
        </div>

    </body>

    
</html>