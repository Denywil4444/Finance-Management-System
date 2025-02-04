<?php
session_start();
include('../includes/db.php');

// Ensure only admin can access this page
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_unset(); // Remove all session variables
    session_destroy(); // Destroy the session
    header('Location: ../login.php'); // Redirect to login page
    exit();
}

// Fetch the admin username
$stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/sidebar_men.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <title>No Submitted Form</title>
    <style>
        /* Reusing the styles from your original code */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            display: flex;
        }


        .content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }

        h1 {
            margin: 0;
        }

        .container {
            width: 50%;
            margin: 40px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 80px;
        }

        .no-form-message {
            font-size: 18px;
            color: #666;
            text-align: center;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-top: 20px;
        }


        /** Back button style */
        /* Back button */
        .back-btn {
            background-color: #f44336;
            color: white;
            padding: 8px 16px;
            font-size: 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background-color: #d32f2f;
        }
        /** Back button style */


    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Main Content Area -->
    <div class="content">
        <?php include('header.php'); ?>

        <div class="container">
            <button class="back-btn" onclick="history.back()">Back</button>
            <h1>No Submitted Form</h1>
            <p class="no-form-message">No form has been submitted by this office yet.</p>
        </div>

    </div>

    <script>
        // Dropdown functionality for user settings
        document.querySelector('.dropdown-btn').addEventListener('click', function (event) {
            const dropdown = document.querySelector('.dropdown');
            event.stopPropagation();
            dropdown.classList.toggle('show');
        });

        window.onclick = function (event) {
            const dropdown = document.querySelector('.dropdown');
            if (!event.target.matches('.dropdown-btn') && !event.target.closest('.dropdown')) {
                dropdown.classList.remove('show');
            }
        };
    </script>
</body>
</html>
