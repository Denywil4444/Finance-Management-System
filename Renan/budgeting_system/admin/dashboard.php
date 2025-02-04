<?php
session_start();
include('../includes/db.php');

// Logout functionality
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch the admin's username
$admin_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

// Fetch all users (offices) along with their usernames, office names, and funds
$stmt = $pdo->query("SELECT id, username, office_name, funds FROM users");
$offices = $stmt->fetchAll();

// Calculate total funds for all offices
$total_funds = 0;
foreach ($offices as $office) {
    $total_funds += $office['funds'] ?? 0;
}

// Set active page class based on the current page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="../assets/css/headers.css">
    <link rel="stylesheet" href="../assets/css/sidebar_men.css">
</head>
<body>
    <!-- Include Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Main Content Area -->
    <div class="content">
        <!-- Include Header -->
        <?php include('header.php'); ?>

        <h2>Dashboard</h2>

        <!-- Total Funds Display -->
        <p><strong>Total Funds for All Offices: </strong><?= number_format($total_funds, 2) ?></p>

        <!-- Table displaying Office Details -->
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Office Name</th>
                    <th>Funds</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($offices as $office): ?>
                    <tr>
                        <td><?= htmlspecialchars($office['username']) ?></td>
                        <td><?= htmlspecialchars($office['office_name']) ?></td>
                        <td><?= htmlspecialchars(number_format($office['funds'] ?? 0, 2)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Add Review Button -->
<div style="text-align: center;">
    <form action="update_form.php" method="GET">
        <button type="submit" name="review" class="review-btn" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Review User Funds
        </button>
    </form>

        <!--<a href="Form.php" name="test" class="test-btn" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Form
        </a>-->
</div>
    </div>

    <script>
        // Dropdown toggle and close functions
        document.querySelector('.dropdown-btn').addEventListener('click', function (event) {
            const dropdown = document.querySelector('.dropdown');
            event.stopPropagation();
            dropdown.classList.toggle('show');
        });

        // Close the dropdown if the user clicks anywhere outside of it
        window.onclick = function (event) {
            const dropdown = document.querySelector('.dropdown');
            if (!event.target.matches('.dropdown-btn') && !event.target.closest('.dropdown')) {
                dropdown.classList.remove('show');
            }
        };
    </script>
</body>
</html>
