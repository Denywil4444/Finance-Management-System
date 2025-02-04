<?php
session_start();
include('../includes/db.php');

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
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

// Handle Admin Username update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_username'])) {
    $new_username = $_POST['admin_username'];

    if (!empty($new_username)) {
        $stmt = $pdo->prepare("UPDATE admins SET username = ? WHERE id = ?");
        $stmt->execute([$new_username, $_SESSION['user_id']]);
        $success = "Username updated successfully!";
    } else {
        $error = "Username cannot be empty!";
    }
}

// Handle Finance Code update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_finance_code'])) {
    $new_finance_code = $_POST['finance_code'];

    if (!empty($new_finance_code)) {
        $stmt = $pdo->prepare("UPDATE admin_settings SET finance_code = ? WHERE id = 1");
        $stmt->execute([$new_finance_code]);
        $success = "Finance code updated successfully!";
    } else {
        $error = "Finance code cannot be empty!";
    }
}

// Handle adding UACS code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_uacs_code'])) {
    $uacs_code = $_POST['uacs_code'];

    if (!empty($uacs_code)) {
        // Check if the UACS Code already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM uacs_codes WHERE code = ?");
        $stmt->execute([$uacs_code]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $error = "UACS Code already exists!";
        } else {
            // Insert the new UACS Code
            $stmt = $pdo->prepare("INSERT INTO uacs_codes (code) VALUES (?)");
            $stmt->execute([$uacs_code]);
            $success = "UACS Code added successfully!";
        }
    } else {
        $error = "UACS Code cannot be empty!";
    }
}


// Handle updating UACS code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_uacs_code'])) {
    $uacs_id = $_POST['uacs_id'];
    $new_uacs_code = $_POST['new_uacs_code'];

    if (!empty($new_uacs_code)) {
        $stmt = $pdo->prepare("UPDATE uacs_codes SET code = ? WHERE id = ?");
        $stmt->execute([$new_uacs_code, $uacs_id]);
        $success = "UACS Code updated successfully!";
    } else {
        $error = "UACS Code cannot be empty!";
    }
}

// Handle deleting UACS code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_uacs_code'])) {
    $uacs_id = $_POST['uacs_id'];

    $stmt = $pdo->prepare("DELETE FROM uacs_codes WHERE id = ?");
    $stmt->execute([$uacs_id]);
    $success = "UACS Code deleted successfully!";
}

// Fetch current Finance Code and Admin Username from the database
$stmt = $pdo->prepare("SELECT finance_code FROM admin_settings WHERE id = 1");
$stmt->execute();
$settings = $stmt->fetch();

$stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();

// Fetch all UACS codes from the database
$uacs_stmt = $pdo->prepare("SELECT * FROM uacs_codes");
$uacs_stmt->execute();
$uacs_codes = $uacs_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="../assets/css/settings.css">
    <link rel="stylesheet" href="../assets/css/sidebar_men.css">
    <link rel="stylesheet" href="../assets/css/headers.css">
</head>
<body>
    <!-- Include Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Main Content Area -->
    <div class="content">
        <!-- Header -->
        <?php include('header.php'); ?>

        <!-- Messages -->
        <?php if (isset($success)) : ?>
            <p class="success message"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <?php if (isset($error)) : ?>
            <p class="error message"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>


         <!-- Title for Settings -->
         <h2>Settings</h2>

        <!-- Admin Username Update -->
        <form method="POST" action="settings.php">
            <label for="admin_username">Admin Username:</label>
            <input type="text" name="admin_username" value="<?= htmlspecialchars($admin['username']) ?>" required>
            <button type="submit" name="update_username">Update Username</button>
        </form>

        <!-- Finance Code Update -->
        <form method="POST" action="settings.php">
            <label for="finance_code">New Finance Code:</label>
            <input type="text" name="finance_code" value="<?= htmlspecialchars($settings['finance_code']) ?>" required>
            <button type="submit" name="update_finance_code">Update Finance Code</button>
        </form>

        <!-- Manage UACS Codes -->
        <h2>Manage Existing UACS Codes</h2>
        <div class="uacs-dropdown">
            <select name="uacs_codes" id="uacs_codes" onchange="populateFields()">
                <option value="" disabled selected>Select a UACS code</option>
                <?php foreach ($uacs_codes as $code): ?>
                    <option value="<?= htmlspecialchars($code['id']) ?>"><?= htmlspecialchars($code['code']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Add UACS Code -->
        <form method="POST" action="settings.php">
            <label for="uacs_code">New UACS Code:</label>
            <input type="text" name="uacs_code" required>
            <button type="submit" name="add_uacs_code">Add UACS Code</button>
        </form>


        <!-- Update/Delete UACS Codes -->
        <form method="POST" action="settings.php">
            <input type="hidden" name="uacs_id" id="uacs_id">
            <label for="new_uacs_code">Edit UACS Code:</label>
            <input type="text" name="new_uacs_code" id="new_uacs_code">
            <button type="submit" name="update_uacs_code">Update</button>
            <button type="submit" name="delete_uacs_code" style="background-color: red;">Delete</button>
        </form>
    </div>

    <script>
        function populateFields() {
            const uacsDropdown = document.getElementById('uacs_codes');
            const selectedOption = uacsDropdown.options[uacsDropdown.selectedIndex];
            const uacsId = selectedOption.value;
            const uacsCode = selectedOption.text;

            document.getElementById('uacs_id').value = uacsId;
            document.getElementById('new_uacs_code').value = uacsCode;
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
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                setTimeout(() => {
                    message.style.opacity = '0'; // Fade out the message
                    message.style.top = '0px'; // Move the message upwards
                    setTimeout(() => {
                        message.remove(); // Remove the message from the DOM after the fade-out
                    }, 500); // Wait for the fade-out animation to complete
                }, 3000); // 3 seconds before starting the fade-out
            });
        });

    </script>

</body>
</html>