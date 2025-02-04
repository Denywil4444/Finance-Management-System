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

// Fetch user details including funds
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        /* Container for main content */
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-top: 70px;
            text-align: center;
            position: relative;
        }

        h1, h2 {
            margin: 20px 0;
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

        /* Funds Display */
        .user-funds {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            font-size: 16px;
        }
    </style>
</head>
<body>

    <!-- Dropdown menu for office name and logout -->
    <div class="dropdown">
        <button class="dropdown-btn" onclick="toggleDropdown()"><?php echo htmlspecialchars($office_name); ?></button>
        <div class="dropdown-content">
            <a href="settings.php">Settings</a> <!-- Settings link -->
            <a href="?logout=1">Logout</a> <!-- Logout link now handles logout directly -->
        </div>
    </div>

    <!-- Main content container -->
    <div class="container">
        <!-- User's Funds Display -->
        <div class="user-funds">
            <strong>Remaining Funds:</strong> ₱<?php echo htmlspecialchars(number_format($user['funds'] ?? 0, 2)); ?>
        </div>

        <h2>Welcome, <?php echo htmlspecialchars($office_name); ?>!</h2>

        <h2>Create Fund Form</h2>

        <!-- Direct link to fund_form.php without fund type selection -->
        <form action="fund_form.php" method="GET">
            <button type="submit">Create Form</button>
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
    </script>
</body>
</html>
