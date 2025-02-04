<?php
session_start();
include('includes/db.php');

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role']; // 'admin' or 'user'
    $login_valid = false;

    if ($role === 'admin') {
        $finance_code = $_POST['finance_code']; // Only for admin

        // Fetch the admin's data
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Verify finance_code from admin_settings table
        $stmtFinance = $pdo->prepare("SELECT finance_code FROM admin_settings LIMIT 1");
        $stmtFinance->execute();
        $settings = $stmtFinance->fetch();

        if (
            $user &&
            password_verify($password, $user['password']) &&
            $settings &&
            $settings['finance_code'] === $finance_code
        ) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $role;
            $_SESSION['username'] = $user['username'];
            $login_valid = true;
        } else {
            $error = "Invalid username, password, or finance code.";
        }
    } else if ($role === 'user') {
        $office_name = $_POST['office_name']; // Only for user

        // Fetch the user's data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND office_name = ?");
        $stmt->execute([$username, $office_name]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $role;
            $_SESSION['username'] = $user['username'];
            $_SESSION['office_name'] = $user['office_name'];
            $login_valid = true;
        } else {
            $error = "Invalid name, password, or office name.";
        }
    }

    if ($login_valid) {
        // Redirect based on role
        if ($role === 'admin') {
            header('Location: admin/dashboard.php');
            exit();
        } else {
            header('Location: user/dashboard.php');
            exit();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to bottom, #e3f2fd, #bbdefb);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }
        .main-container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .logo-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #4CAF50;
            padding: 20px;
        }
        .logo-container img {
            max-width: 100%;
            max-height: 100%;
            width: 150px;
            height: 150px;
            border-radius: 50%;
        }
        .login-container {
            flex: 2;
            padding: 30px;
            text-align: center;
        }
        .hidden {
            display: none;
        }
        form {
            margin-top: 20px;
        }
        form input, form select {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        form label {
            font-size: 16px;
            color: #555;
            margin-bottom: 8px;
            text-align: left;
            display: block;
        }

        input[type="submit"] {
            margin-top: 10px;
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.2rem;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            font-size: 1rem;
            margin-top: 15px;
        }

        .register-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            font-size: 1.1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .register-btn:hover {
            background-color: #45a049;
        }

        /* Home button styling */
        .home-btn {
            position: absolute;
            top: 15px;
            right: 150px;
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            font-size: 1.1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .home-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <!-- Home button in top left corner -->
    <button class="home-btn" onclick="window.location.href='index.php'">Home</button>

    <!-- Register button in top right corner -->
    <button class="register-btn" onclick="window.location.href='register.php'">Register</button>

    <div class="main-container">
        <!-- Logo Section -->
        <div class="logo-container">
            <img src="images/nisuBG.jpg" alt="Logo"> <!-- Replace 'logo.png' with the path to your logo -->
        </div>

        <!-- Login Form Section -->
        <div class="login-container">
            <h1>Login</h1>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

            <form method="POST" action="login.php">
                <label for="username">Name:</label>
                <input type="text" name="username" required>

                <label for="password">Password:</label>
                <input type="password" name="password" required>

                <label for="role">Role:</label>
                <select name="role" id="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>

                <!-- Office Name for users -->
                <div id="office-name-section" class="hidden">
                    <label for="office_name">Office Name:</label>
                    <input type="text" name="office_name" id="office_name">
                </div>

                <!-- Finance Code for admin -->
                <div id="finance-code-section" class="hidden">
                    <label for="finance_code">Finance Code:</label>
                    <input type="text" name="finance_code" id="finance_code">
                </div>

                <input type="submit" value="Login">
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const officeNameSection = document.getElementById('office-name-section');
            const financeCodeSection = document.getElementById('finance-code-section');

            // Toggle input fields based on role selection
            roleSelect.addEventListener('change', function() {
                if (roleSelect.value === 'admin') {
                    financeCodeSection.classList.remove('hidden');
                    officeNameSection.classList.add('hidden');
                } else if (roleSelect.value === 'user') {
                    officeNameSection.classList.remove('hidden');
                    financeCodeSection.classList.add('hidden');
                }
            });

            // Set initial visibility on page load
            if (roleSelect.value === 'admin') {
                financeCodeSection.classList.remove('hidden');
                officeNameSection.classList.add('hidden');
            } else {
                officeNameSection.classList.remove('hidden');
                financeCodeSection.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
