<?php
session_start();
include('../includes/db.php');

// Logout functionality
if (isset($_GET['logout'])) {
    session_unset(); // Remove all session variables
    session_destroy(); // Destroy the session
    header('Location: ../login.php'); // Redirect to login page
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

// Add, update, or delete funds based on admin action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $funds = $_POST['funds'];
    $action = $_POST['action'];

    if ($action === 'update') {
        $stmt = $pdo->prepare("UPDATE users SET funds = ? WHERE id = ?");
        $stmt->execute([$funds, $user_id]);
        $_SESSION['message'] = "Funds updated successfully.";
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("UPDATE users SET funds = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['message'] = "Funds deleted successfully.";
    } elseif ($action === 'delete-user') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['message'] = "User deleted successfully.";
    }

    // Redirect to the same page to reload the table and display the message
    header("Location: manage-users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/manage_office.css">
    <link rel="stylesheet" href="../assets/css/sidebar_men.css">
    <link rel="stylesheet" href="../assets/css/headers.css">
</head>
<body>

    <!-- Include Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Main Content Area -->
    <div class="content">
        <!-- Header with Admin's Username in the Dropdown -->
        <?php include('header.php'); ?>

        <!-- Display Success Message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message" id="success-message"><?= $_SESSION['message']; ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- Title for Office Information -->
        <h2>Offices Management</h2>

        <!-- Edit All Button -->
        <!-- Edit All Button -->
            <button type="button" id="edit-all-btn" onclick="toggleAllActions()">Edit All</button>

            <!-- Table displaying Office Details -->
            <table id="offices-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Office Name</th>
                        <th>Funds</th>
                        <th id="actions-header" style="display: none;">Actions</th> <!-- Initially Hidden -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($offices as $office): ?>
                        <tr>
                            <td><?= htmlspecialchars($office['username']) ?></td>
                            <td><?= htmlspecialchars($office['office_name']) ?></td>
                            <td><?= htmlspecialchars(number_format($office['funds'] ?? 0, 2)) ?></td>
                            <td class="actions-column" style="display: none;"> <!-- Initially Hidden -->
                                <!-- Forms for updating, deleting funds, and deleting users -->
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="user_id" value="<?= $office['id'] ?>">
                                    <input type="number" name="funds" value="<?= number_format($office['funds'] ?? 0, 2) ?>" required />
                                    <button type="submit" name="action" value="update">Update</button>
                                </form>

                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="user_id" value="<?= $office['id'] ?>">
                                    <button type="submit" name="action" value="delete">Delete</button>
                                </form>

                                <form method="POST" style="display:inline-block;" onsubmit="return confirmDeleteUser();">
                                    <input type="hidden" name="user_id" value="<?= $office['id'] ?>">
                                    <button type="submit" name="action" value="delete-user">Delete User</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
    </div>

    <script>
        // Disappear the success message after 3 seconds
        window.onload = function() {
            const successMessage = document.getElementById('success-message');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.display = 'none';
                }, 3000); // 3 seconds
            }
        }

        // Toggle the visibility of the update, delete, and delete user forms for all users
        function toggleAllActions() {
            const updateForms = document.querySelectorAll('[id^="update-form-"]');
            const deleteForms = document.querySelectorAll('[id^="delete-form-"]');
            const deleteUserForms = document.querySelectorAll('[id^="delete-user-form-"]');

            updateForms.forEach(form => {
                form.style.display = form.style.display === 'none' || form.style.display === '' ? 'inline-block' : 'none';
            });

            deleteForms.forEach(form => {
                form.style.display = form.style.display === 'none' || form.style.display === '' ? 'inline-block' : 'none';
            });

            deleteUserForms.forEach(form => {
                form.style.display = form.style.display === 'none' || form.style.display === '' ? 'inline-block' : 'none';
            });
        }

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


        function toggleAllActions() {
        const actionsHeader = document.getElementById('actions-header');
        const actionColumns = document.querySelectorAll('.actions-column');

        // Check if currently hidden
        const isHidden = actionsHeader.style.display === 'none';

        // Toggle display for the header and each action column
        actionsHeader.style.display = isHidden ? 'table-cell' : 'none';
        actionColumns.forEach(col => col.style.display = isHidden ? 'table-cell' : 'none');
        }


        // Confirmation for deleting a user
        function confirmDeleteUser() {
        return confirm("Are you sure you want to delete this Office? This action cannot be undone.");
        }

    </script>

</body>
</html>
