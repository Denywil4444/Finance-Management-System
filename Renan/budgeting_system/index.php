<?php
session_start();
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance System</title>
    
    <style>
        /* General reset and font styles */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom, #e3f2fd, #bbdefb);
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 400px;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #2196f3;
        }
        h2 {
            font-size: 1.2rem;
            margin-bottom: 30px;
            color: #555;
        }
        .buttons a {
            display: block;
            margin: 10px 0;
            padding: 12px;
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            font-size: 1rem;
            font-weight: bold;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .buttons a:hover {
            background-color: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to the</h1>
        <h2>Financial Management System</h2>
        <div class="buttons">
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </div>
    </div>
</body>
</html>
