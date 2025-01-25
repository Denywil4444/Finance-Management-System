<?php

@include 'config.php';

session_start();

if(!isset($_SESSION['admin_name'])){
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
            <h3>Hi, <span>admin</span></h3>
            <h1>Welcome<span><?php echo $_SESSION['admin_name'] ?></span></h1>
            <p>this is an admin page</p>
            <a href="login_form.php" class="btn">Login</a>
            <a href="register.php" class="btn">register</a>
            <a href="logout.php" class="btn">Logout</a>

        </div>
    </div>

</html>