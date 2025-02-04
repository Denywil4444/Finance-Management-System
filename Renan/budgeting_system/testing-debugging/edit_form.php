






/////////////// STOP ////////////









<?php
session_start();
include('../includes/db.php');

// CSRF Token Generation (for POST requests)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Ensure only admin can access this page
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}



// Handle form deletion
if (isset($_GET['delete_form_id'])) {
    $form_id_to_delete = $_GET['delete_form_id'];

    // Fetch the user_id associated with the form to update the user's form_submitted count
    $stmt = $pdo->prepare("SELECT user_id FROM user_funds WHERE id = ?");
    $stmt->execute([$form_id_to_delete]);
    $form = $stmt->fetch();

    if ($form) {
        $user_id = $form['user_id'];

        // Delete the form from the user_funds table
        $stmt = $pdo->prepare("DELETE FROM user_funds WHERE id = ?");
        $stmt->execute([$form_id_to_delete]);

        // Decrement the form_submitted count in the users table
        $stmt = $pdo->prepare("UPDATE users SET form_submitted = form_submitted - 1 WHERE id = ?");
        $stmt->execute([$user_id]);

        // Set success message in session
        $_SESSION['success_message'] = "Form deleted successfully!";
    }

    // Redirect to avoid reloading the page after deletion
    header("Location: submit_form.php");
    exit();
}



// Logout functionality
if (isset($_GET['logout'])) {
    session_unset(); // Remove all session variables
    session_destroy(); // Destroy the session
    header('Location: ../login.php'); // Redirect to login page
    exit();
}

// Fetch all users with the count of forms submitted
if (!isset($_GET['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.role, COUNT(uf.id) AS forms_submitted 
        FROM users u 
        LEFT JOIN user_funds uf ON uf.user_id = u.id 
        GROUP BY u.id
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();
}

// Fetch user-specific form data if `user_id` is set
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch the user's username instead of payee
    $stmt = $pdo->prepare("SELECT u.username, uf.* 
                           FROM user_funds uf 
                           JOIN users u ON uf.user_id = u.id 
                           WHERE uf.user_id = ?");
    $stmt->execute([$user_id]);
    $forms = $stmt->fetchAll(); // Fetch all forms for the user

    // If no forms exist for the user, redirect to the 'No Submitted Form' message
    if (empty($forms)) {
        header("Location: no_submitted_form.php?user_id=$user_id");
        exit();
    }
}

// Fetch all UACS codes
$uacs_stmt = $pdo->prepare("SELECT * FROM uacs_codes");
$uacs_stmt->execute();
$uacs_codes = $uacs_stmt->fetchAll();

$stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();

// Handle form submission to update data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_form'])) {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    // Collect the updated form data
    $payee = $_POST['payee'];
    $office = $_POST['office'];
    $address = $_POST['address'];
    $responsibility_center = $_POST['responsibility_center'];
    $particulars = $_POST['particulars'];
    $uacs_code = $_POST['uacs_code'];
    $amount = $_POST['amount'];

    // Update the user's form in the database
    $stmt = $pdo->prepare("UPDATE user_funds 
                           SET payee = ?, office = ?, address = ?, responsibility_center = ?, particulars = ?, uacs_code = ?, amount = ? 
                           WHERE id = ?");
    $stmt->execute([$payee, $office, $address, $responsibility_center, $particulars, $uacs_code, $amount, $_GET['form_id']]);

    // Set success message in session
    $_SESSION['success_message'] = "Form updated successfully!";

    // Redirect to the current page
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}


?>





<!---------------------------------------                                ------------------------------------------- -->
<!---------------------------------------                                ------------------------------------------- -->
<!---------------------------------------                                ------------------------------------------- -->




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style2.css">
    <title>Forms</title>
    <link rel="stylesheet" href="../assets/css/edit_style.css">
</head>
<body>
    <!-- Sidebar Menu -->
    <div class="sidebar">
        <div class="username">
            <p><?= htmlspecialchars($admin['username']) ?></p>
        </div>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage-users.php">Manage Users</a>
        <a href="submit_form.php" class=" <?= basename($_SERVER['PHP_SELF']) === 'submit_form.php' ? 'active' : '' ?>">Forms</a>
        <a href="update_form.php">Update Form</a>
        <a href="settings.php">Settings</a>
    </div>

    <!-- Main Content Area -->
    <div class="content">
        <!-- Include Header -->
        <?php include('header.php'); ?>

        <div class="container">

        <!-- Display message when no form is submitted -->
        <?php if (isset($_GET['no_submitted_form']) && $_GET['no_submitted_form'] === 'true'): ?>
            <div class="no-form-message">
                <p>No form has been submitted by this user yet. Please try another user.</p>
                <button class="back-btn" onclick="window.location.href='submit_form.php';">Go Back</button>
            </div>
        <?php endif; ?>

        <!-- Display User List if no user is selected for editing -->
        <?php if (!isset($forms)): ?>
            <h1>All Users</h1>
            <div class="users-list">
            <?php if (isset($users) && count($users) > 0): ?>
                <?php foreach ($users as $user): ?>
                    <div class='user-item'>
                        <div class='user-details'>
                            <span class='username'><?php echo htmlspecialchars($user['username']); ?></span>
                            <p class='role'>Role: <?php echo htmlspecialchars($user['role']); ?></p>
                            <p class="forms-submitted">Forms Submitted: <?php echo $user['forms_submitted']; ?></p>
                        </div>
                        <a href='edit_form.php?user_id=<?php echo $user['id']; ?>' class='edit-btn'>Edit</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Display edit form if user_id and form data exist -->
            <button class="back-btn" onclick="window.history.back()">Back</button>
            <button class="new-form-btn" onclick="window.location.href='Form.php'">New Form</button>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div id="success-message" class="success-message">
                    <?php echo $_SESSION['success_message']; ?>
                </div>
                <script>
                    setTimeout(function() {
                        document.getElementById("success-message").style.display = "none";
                    }, 2000); // Hide after 2 seconds
                </script>
                <?php unset($_SESSION['success_message']); ?> <!-- Clear the message after displaying -->
            <?php endif; ?>


            <h1>Edit Form for <?php echo isset($forms) ? htmlspecialchars($forms[0]['username']) : ''; ?></h1>
            <h3>Office: <?php echo isset($forms) ? htmlspecialchars($forms[0]['office']) : ''; ?></h3>

            <!-- Form for editing -->
            <?php if (isset($forms) && count($forms) > 0): ?>
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <div class="form-wrapper">
            <?php foreach ($forms as $form): ?>
                <div class="form-container">
                    <div class="form-group">
                        <label for="payee">Payee:</label>
                        <input type="text" name="payee" value="<?php echo htmlspecialchars($form['payee']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="office">Office:</label>
                        <input type="text" name="office" value="<?php echo htmlspecialchars($form['office']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="address">Address:</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($form['address']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="responsibility_center">Responsibility Center:</label>
                        <input type="text" name="responsibility_center" value="<?php echo htmlspecialchars($form['responsibility_center']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="particulars">Particulars:</label>
                        <input type="text" name="particulars" value="<?php echo htmlspecialchars($form['particulars']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="uacs_code">UACS Code:</label>
                        <select name="uacs_code" required>
                            <?php foreach ($uacs_codes as $code): ?>
                                <option value="<?php echo $code['code']; ?>" <?php echo $code['code'] === $form['uacs_code'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($code['code']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="amount">Amount:</label>
                        <input type="number" name="amount" value="<?php echo htmlspecialchars($form['amount']); ?>" required>
                    </div>

                    <input type="submit" name="update_form" value="Update">
                    <button type="button" class="delete-btn" data-form-id="<?php echo $form['id']; ?>" onclick="confirmDelete(this)">Delete</button>
                    <a href="view.php?user_id=<?php echo urlencode($form['id']); ?>">View</a>
                </div>
            <?php endforeach; ?>
        </div>
    </form>
<?php else: ?>
    <div class="form-container">
        <p class="no-form-message">No form has been submitted by this office yet.</p>
    </div>
<?php endif; ?>

        <?php endif; ?>
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


        function confirmDelete(button) {
            const formId = button.getAttribute('data-form-id');
            const confirmation = confirm("Are you sure you want to delete this form?");
            if (confirmation) {
                // Redirect to delete action (to delete_form.php with form_id)
                window.location.href = "delete_form.php?form_id=" + formId;
            }
        }

    </script>
</body>
</html>
