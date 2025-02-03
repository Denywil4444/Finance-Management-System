<?php

@include 'config.php';

if(isset($_POST['submit'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = md5($_POST['password']);
   $cpass = md5($_POST['cpassword']);
   /*$user_type = $_POST['user_type'];*/
   $user_type = isset($_POST['user_type']) ? $_POST['user_type'] : null;


   $select = " SELECT * FROM user_form WHERE email = '$email' && password = '$pass' ";

   $result = mysqli_query($conn, $select);

   if(mysqli_num_rows($result) > 0){

      $error[] = 'user already exist!';

   }else{

      if($pass != $cpass){
         $error[] = 'password not matched!';
      }else{
         $insert = "INSERT INTO user_form(name, email, password, user_type) VALUES('$name','$email','$pass','$user_type')";
         mysqli_query($conn, $insert);
         header('location:login.php');
      }
   }

};


?>






<!DOCTYPE html>
<html>
    <head>
        <title>Finance Management System</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="form.css">
    </head>

    <body>
        <img src="images/logo.png" alt="logo" class="logo">
        <div class="register-container">

            <div class="image-container" >
            <div class="overlay"></div>
                <img src="images/finance.png" alt="money" class="money">
                <p class="money-description"><span>Financial</span> <br> Management Services</p>
            </div>

            <div class="register-div">
                <div class="overlay"></div>
                <form  action="" method="post">
                    
                    <br>

                    <h1>REGISTER NOW</h1>
                    <p>Register your account </p>
                    <br>
                   <?php
                        if(isset($error)){
                            foreach($error as $error){
                            echo '<span class="error-msg" id="error-msg">'.$error.'</span>';
                            };
                        };
                    ?>

                    <img src="images/user.png" class="icons">
                    <input type="text" name="name" placeholder="Enter your name" required>
                    <br>
                    <br>
                    <img src="images/email.png" class="icons">
                    <input type="email" name="email" placeholder="Enter your email" required>
                    <br>
                    <br>
                    <img src="images/password.png" class="icons">
                    <input type="password" name="password" placeholder="Enter your password" required>
                    <br>
                    <br>
                    <img src="images/confirm.png" class="icons">
                    <input type="password" name="cpassword" placeholder="Confirm your password" required>
                    <br>
                    <br>
                    <img src="images/chose.png" class="icons">
                    <select name="user_type" required >
                        <option value="" disabled selected>Select user type</option>
                        <option>user</option>
                        <option>admin</option>
                    </select>
                    <br>
                    <br>
                    
                    <input type="submit" name="submit" value="Register now" class="register-button">
                    <br>
                    <br>
                    <p>Already registered?<span class="log"><a href="login.php">Login now</a></span></p>

            
                    
                    <p class="left-down">All rights reserved</p>
                    <p class="right-down">Terms and Use Privacy Policy</p>

                </form>
                
            </div>
        </div>

        <script>
            // JavaScript to remove the error message after 2 seconds
            setTimeout(() => {
                const errorMsg = document.getElementById('error-msg');
                if (errorMsg) {
                    errorMsg.style.opacity = '0'; // Add a fade-out effect
                    setTimeout(() => {
                        errorMsg.remove();
                    }, 500); // Wait for the fade-out effect to complete
                }
            }, 1000);
        </script>
    </body>

    

</html>