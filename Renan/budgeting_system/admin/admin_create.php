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

if (!isset($_GET['user_id'])) {
    // Fetch all users with the count of forms submitted
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.role, u.fund_type, COUNT(uf.id) AS forms_submitted 
        FROM admins u 
        LEFT JOIN admin_funds uf ON uf.user_id = u.id 
        GROUP BY u.id
    ");
    $stmt->execute();
    $admins = $stmt->fetchAll();
}

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch the user's username instead of payee
    $stmt = $pdo->prepare("SELECT u.username, uf.* 
                           FROM user_funds uf 
                           JOIN admins u ON uf.user_id = u.id 
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
    // Collect the updated form data
    $payee = $_POST['payee'];
    $office = $_POST['office'];
    $address = $_POST['address'];
    $responsibility_center = $_POST['responsibility_center'];
    $particulars = $_POST['particulars'];
    $uacs_code = $_POST['uacs_code'];
    $amount = $_POST['amount'];

    // Update the user's form in the database
    $stmt = $pdo->prepare("UPDATE admin_funds 
                           SET payee = ?, office = ?, address = ?, responsibility_center = ?, particulars = ?, uacs_code = ?, amount = ? 
                           WHERE id = ?");
    $stmt->execute([$payee, $office, $address, $responsibility_center, $particulars, $uacs_code, $amount, $form['id']]);

    // Set success message in session
    $_SESSION['success_message'] = "Form updated successfully!";
    
    // Redirect to avoid form resubmission
    header("Location: admin_create.php?user_id=$user_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/admin_create.css">
    <link rel="stylesheet" href="../assets/css/sidebar_men.css">
    <link rel="stylesheet" href="../assets/css/headers.css">

    <title>Forms</title>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Main Content Area -->
    <div class="content">
        <!-- Include Header -->
        <?php include('header.php'); ?>

        <div class="container">

<!-- Display message when no form is submitted -->
<?php if (isset($_GET['no_submitted_form']) && $_GET['no_submitted_form'] === 'true'): ?>
    <div class="no-form-message">
        <p>No form has been created by this user yet. Please try another user.</p>
        <button class="back-btn" onclick="window.location.href='admin_create.php';">Go Back</button>
    </div>
<?php endif; ?>

<!-- Modify this condition to hide or modify the "All Admins" title when editing -->
<?php if (!isset($form)): ?>
    <div class="header-container">
        <h1>All Admin Forms</h1>
        <a href="admin_form_creation.php" class="create-btn">Create</a>
    </div>
    <div class="all-users-section">
        <div class="users-list">
            <?php if (isset($admins) && count($admins) > 0): ?>
                <?php foreach ($admins as $admin): ?>
                    <div class='user-item'>
                        <div class='user-details'>
                            <span class='username'><?php echo htmlspecialchars($admin['username']); ?></span>
                            <p class='role'>Role: <?php echo htmlspecialchars($admin['fund_type']); ?></p>
                            <p class="forms-submitted">Forms Created: <?php echo $admin['forms_submitted']; ?></p>
                        </div>
                        <a href='edit_admin_form.php?user_id=<?php echo $admin['id']; ?>' class='edit-btn'>Edit</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <!-- Change title to include office under edit form -->
    <button class="back-btn" onclick="window.history.back()">Back</button>
    <h1>Edit Form for <?php echo isset($form) ? htmlspecialchars($form['username']) : ''; ?></h1>
    <h3>Office: <?php echo isset($form) ? htmlspecialchars($form['office']) : ''; ?></h3>

        

        <!-- Display success message if set -->
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="success-message">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']);
        }
        ?>

        <!-- Only show form if user_id and form data exist -->
        <?php if (isset($forms) && count($forms) > 0): ?>
                <?php foreach ($forms as $form): ?>
                    <div class="form-container">
                        <form method="POST" action="">

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
                        </form>
                    </div>
                <?php endforeach; ?>
        <?php else: ?>
            <!-- Display message when no form exists -->
            <div class="form-container">
            <p class="no-form-message">No form has been submitted by this office yet.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
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