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

// Fetch the fund_type of the logged-in admin
$stmt = $pdo->prepare("SELECT fund_type FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();
$admin_fund_type = $admin['fund_type']; // Store the admin's fund_type

if (!isset($_GET['user_id'])) {
    // Fetch all users with the count of funds based on fund_type, matching admin's fund_type
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.role, uf.fund_type, COUNT(uf.id) AS fund_count
        FROM users u
        LEFT JOIN user_funds uf ON uf.user_id = u.id
        WHERE uf.fund_type = ?  -- Filter by the admin's fund_type
        GROUP BY u.id, uf.fund_type
    ");
    $stmt->execute([$admin_fund_type]); // Execute with the admin's fund_type
    $users = $stmt->fetchAll();
}

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch the fund_type from the admin table for the logged-in admin
    $stmt = $pdo->prepare("SELECT fund_type FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch();

    if ($admin) {
        // Fetch the user's username and form data, matching the fund_type from the admin
        $stmt = $pdo->prepare("
            SELECT u.username, uf.*, a.fund_type 
            FROM user_funds uf
            JOIN users u ON uf.user_id = u.id
            JOIN admins a ON a.id = ? 
            WHERE uf.user_id = ? 
            AND uf.fund_type = a.fund_type  -- Match fund_type between user_funds and admins
        ");
        $stmt->execute([$_SESSION['user_id'], $user_id]);
        $forms = $stmt->fetchAll(); // Fetch all forms for the user matching the fund_type

        // If no forms exist for the user, redirect to the 'No Submitted Form' message
        if (empty($forms)) {
            header("Location: no_submitted_form.php?user_id=$user_id");
            exit();
        }
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
    $stmt = $pdo->prepare("UPDATE user_funds 
                           SET payee = ?, office = ?, address = ?, responsibility_center = ?, particulars = ?, uacs_code = ?, amount = ? 
                           WHERE id = ?");
    $stmt->execute([$payee, $office, $address, $responsibility_center, $particulars, $uacs_code, $amount, $form['id']]);

    // Set success message in session
    $_SESSION['success_message'] = "Form updated successfully!";
    
    // Redirect to avoid form resubmission
    header("Location: submit_form.php?user_id=$user_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/submit_form.css">
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
        <p>No form has been submitted by this user yet. Please try another user.</p>
        <button class="back-btn" onclick="window.location.href='submit_form.php';">Go Back</button>
    </div>
<?php endif; ?>

<!-- Modify this condition to hide or modify the "All Users" title when editing -->
<?php if (!isset($form)): ?>
    <h1>All Users</h1>
    <div class="all-users-section">
    <div class="users-list">
    <?php if (isset($users) && count($users) > 0): ?>
        <?php foreach ($users as $user): ?>
            <div class='user-item'>
                <div class='user-details'>
                    <span class='username'><?php echo htmlspecialchars($user['username']); ?></span>
                    <p class='role'>Role: <?php echo htmlspecialchars($user['role']); ?></p>
                    <p class="fund-type">Fund Type: <?php echo htmlspecialchars($user['fund_type']); ?></p>
                    <p class="fund-count">Fund Count: <?php echo $user['fund_count']; ?></p>
                </div>
                <a href='edit.php?user_id=<?php echo $user['id']; ?>' class='edit-btn'>Edit</a>
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
    <h4>Fund Type: <?php echo isset($form) ? htmlspecialchars($form['fund_type']) : ''; ?></h4>

        

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
