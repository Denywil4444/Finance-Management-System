<?php
session_start();
include('../includes/db.php');

// Handle logout when "logout" is passed in the URL
if (isset($_GET['logout'])) {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header('Location: ../login.php'); // Redirect to login page
    exit();
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$office_name = $_SESSION['office_name'] ?? '';

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle form submission for username, password, and office name change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $new_password = trim($_POST['password']);
    $new_password_confirm = trim($_POST['password_confirm']);
    $new_office_name = trim($_POST['office_name']);
    
    // Validate username
    if (empty($new_username)) {
        $error_message = "Username cannot be empty.";
    } 
    // Validate password
    elseif ($new_password !== $new_password_confirm) {
        $error_message = "Passwords do not match.";
    } elseif (!empty($new_password) && strlen($new_password) < 6) {
        $error_message = "Password should be at least 6 characters long.";
    }

    if (!isset($error_message)) {
        // Update username, password, and office name in the database
        if (!empty($new_password)) {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT); // Hash the new password
            $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, office_name = ? WHERE id = ?");
            $stmt->execute([$new_username, $new_password_hash, $new_office_name, $user_id]);
        } else {
            // Update only the username and office name if password is not changed
            $stmt = $pdo->prepare("UPDATE users SET username = ?, office_name = ? WHERE id = ?");
            $stmt->execute([$new_username, $new_office_name, $user_id]);
        }

        // Update session data to reflect changes
        $_SESSION['username'] = $new_username;
        $_SESSION['office_name'] = $new_office_name;

        $_SESSION['success_message'] = "Your profile has been updated successfully!";
        header('Location: settings.php'); // Redirect to settings page after successful update
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        /* Container for main content */
        .container {
            width: 30%;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            margin: 20px 0;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
            
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 70%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .form-group input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            font-size: 16px;
        }

        .form-group input[type="submit"]:hover {
            background-color: #45a049;
        }

        button {
            display: block;
            margin: 15px auto;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
        }

        /* Styling for success and error messages */
        .message {
            position: fixed;
            top: 40px;
            left: 340px;
            width: 50%;
            padding: 10px;
            color: white;
            font-size: 16px;
            text-align: center;
            z-index: 1000;
            display: none;
        }

        .success-message {
            background-color: green;
        }

        .error-message {
            background-color: red;
        }

        .message.show {
            display: block;
        }

        /* Dropdown Menu Styling */
        .dropdown {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 100;
        }

        .dropdown-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            margin-top: -10px;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
        }

        .dropdown.show .dropdown-content {
            display: block;
        }
    </style>
</head>
<body>

    <!-- Success and Error Messages -->
    <?php if (isset($error_message)): ?>
        <div class="message error-message show">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php elseif (isset($_SESSION['success_message'])): ?>
        <div class="message success-message show">
            <?php echo $_SESSION['success_message']; ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- Dropdown menu for office name and logout -->
    <div class="dropdown">
        <button class="dropdown-btn" onclick="toggleDropdown()"><?php echo htmlspecialchars($office_name); ?></button>
        <div class="dropdown-content">
            <a href="dashboard.php">Dashboard</a> <!-- Settings link added -->
            <a href="?logout=1">Logout</a> <!-- Logout link now handles logout directly -->
        </div>
    </div>

    <!-- Main content container -->
    <div class="container">
        <h2>Settings</h2>

        <form action="settings.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <div class="form-group">
                <label for="office_name">Office Name:</label>
                <input type="text" name="office_name" value="<?php echo htmlspecialchars($user['office_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">New Password:</label>
                <input type="password" name="password">
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Confirm Password:</label>
                <input type="password" name="password_confirm">
            </div>

            

            <button type="submit">Update Profile</button>
        </form>
    </div>

    <script>
        // Function to toggle dropdown visibility
        function toggleDropdown() {
            const dropdown = document.querySelector('.dropdown');
            dropdown.classList.toggle('show');
        }

        // Close the dropdown if the user clicks anywhere outside of it
        window.onclick = function(event) {
            if (!event.target.matches('.dropdown-btn')) {
                const dropdowns = document.querySelectorAll('.dropdown-content');
                dropdowns.forEach(function(dropdown) {
                    if (dropdown.style.display === 'block') {
                        dropdown.style.display = 'none';
                    }
                });
            }
        }

        // Automatically hide the success/error message after 3 seconds
        setTimeout(function() {
            const message = document.querySelector('.message');
            if (message) {
                message.classList.remove('show');
            }
        }, 3000);
    </script>

</body>
</html>
