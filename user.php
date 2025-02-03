<?php

@include 'config.php';

session_start();

if(!isset($_SESSION['user_name'])){
   header('location:login.php');
}

?>




<!DOCTYPE html>
<html>
    <head>
        <title>Finance Management System</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel=stylesheet href="style.css">
    </head>

    <div class="admin-container">
        <div class="content">
            <h3>Hi, <span>user</span></h3>
            <h1>Welcome<span><?php echo $_SESSION['user_name'] ?></span></h1>
            <p>this is an user page</p>
            <a href="login.php" class="btn">Login</a>
            <a href="register.php" class="btn">register</a>
            <a href="logout.php" class="btn">Logout</a>

        </div>
    </div>

</html>