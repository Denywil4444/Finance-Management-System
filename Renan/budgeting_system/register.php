<?php
session_start();
include('includes/db.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role']; // 'admin' or 'user'
    $office_name = $_POST['office_name'] ?? ''; // Get office name if user
    $fund_type = $_POST['fund_type'] ?? ''; // Get fund type if admin

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Admin Registration
    if ($role === 'admin') {
        // Check if an admin already exists
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $existingAdmin = $stmt->fetch();
        
        if ($existingAdmin) {
            $error = "Admin with this username already exists!";
        } else {
            // Insert new admin into the admins table
            $stmt = $pdo->prepare("INSERT INTO admins (username, password, role, fund_type) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $hashed_password, 'admin', $fund_type]);

            // Check if the insert was successful
            if ($stmt->rowCount()) {
                header('Location: login.php'); // Redirect to login page after successful registration
                exit();
            } else {
                $error = "There was an error during registration.";
            }
        }
    } else {
        // User Registration
        // Check if a user already exists in the users table
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $existingUser = $stmt->fetch();
        
        if ($existingUser) {
            $error = "User with this username already exists!";
        } else {
            // Insert new user with office name into the users table
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, office_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $hashed_password, 'user', $office_name]);

            // Check if the insert was successful
            if ($stmt->rowCount()) {
                header('Location: login.php'); // Redirect to login page after successful registration
                exit();
            } else {
                $error = "There was an error during registration.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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

        .register-container {
            width: 100%;
            max-width: 400px;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        label {
            font-size: 16px;
            color: #555;
            margin-bottom: 8px;
            text-align: left;
            display: block;
        }

        input[type="text"],
        input[type="password"],
        select,
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .login-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            font-size: 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .login-btn:hover {
            background-color: #45a049;
        }

        #office-name-section {
            display: none;
        }
    </style>
</head>
<body>

    <!-- Login button in top right corner -->
    <button class="login-btn" onclick="window.location.href='login.php'">Login</button>

    <div class="register-container">
        <h1>Register</h1>
        
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        
        <form method="POST" action="register.php">
            <label for="username">Username:</label>
            <input type="text" name="username" required>
            
            <label for="password">Password:</label>
            <input type="password" name="password" required>
            
            <label for="role">Role:</label>
            <select name="role" id="role" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>

            <!-- Fund Type dropdown (only for admins) -->
            <div id="fund-type-section" style="display: none;">
                <label for="fund_type">Fund Type:</label>
                <select name="fund_type" required>
                    <option value="Business Related Funds">Business Related Funds</option>
                    <option value="Internally Generated Funds">Internally Generated Fund</option>
                </select>
            </div>

            <!-- Office Name field (only for users) -->
            <div id="office-name-section">
                <label for="office_name">Office Name:</label>
                <input type="text" name="office_name" required>
            </div>

            <input type="submit" name="register" value="Register">
        </form>
    </div>

    <script>
        const roleSelect = document.getElementById('role');
        const officeNameSection = document.getElementById('office-name-section');
        const fundTypeSection = document.getElementById('fund-type-section');

        roleSelect.addEventListener('change', () => {
            if (roleSelect.value === 'user') {
                officeNameSection.style.display = 'block';
                document.querySelector('input[name="office_name"]').setAttribute('required', 'required');
                fundTypeSection.style.display = 'none'; // Hide fund type for user
            } else if (roleSelect.value === 'admin') {
                officeNameSection.style.display = 'none';
                document.querySelector('input[name="office_name"]').removeAttribute('required');
                fundTypeSection.style.display = 'block'; // Show fund type for admin
            }
        });

        // If the page is loaded with the user role selected, show office name input
        if (roleSelect.value === 'user') {
            officeNameSection.style.display = 'block';
        } else if (roleSelect.value === 'admin') {
            fundTypeSection.style.display = 'block';
        }
    </script>

</body>
</html>
