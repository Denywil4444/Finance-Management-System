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
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}

// Fetch all UACS codes
$uacs_stmt = $pdo->prepare("SELECT * FROM uacs_codes");
$uacs_stmt->execute();
$uacs_codes = $uacs_stmt->fetchAll();

// Fetch the admin's username
$stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();

// Handle form submission to create a new form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_form_creation'])) {
    // Collect form data
    $payee = $_POST['payee'];
    $office = $_POST['office'];
    $address = $_POST['address'];
    $responsibility_center = $_POST['responsibility_center'];
    $particulars = $_POST['particulars'];
    $uacs_code = $_POST['uacs_code'];
    $amount = $_POST['amount'];
    $user_id = $_SESSION['user_id']; // Assuming form is created by the logged-in admin

    // Insert new form data into the admin_funds table
    try {
        $stmt = $pdo->prepare("INSERT INTO admin_funds (user_id, payee, office, address, responsibility_center, particulars, uacs_code, amount) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $payee, $office, $address, $responsibility_center, $particulars, $uacs_code, $amount]);

        // Set success message
        $_SESSION['success_message'] = "Form created successfully!";
        
        // Redirect to avoid form resubmission
        header("Location: admin_form_creation.php");
        exit();
    } catch (Exception $e) {
        // Error handling for the insert query
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/editsss.css">
    <link rel="stylesheet" href="../assets/css/sidebar_men.css">
    <link rel="stylesheet" href="../assets/css/headers.css">
    <title>Create New Form</title>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include('sidebar.php'); ?>

    <div class="content">
        <!-- Include Header -->
        <?php include('header.php'); ?>

        <div class="content-wrapper">
            <div class="container">
                <button class="back-btn" onclick="window.location.href='admin_create.php'">Back</button>
                <!--<button class="forms-btn" onclick="window.location.href='admin_form.php'">Forms</button>-->

                <h1>Create New Form</h1>

                <!-- Display success message -->
                <?php
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="success-message">' . $_SESSION['success_message'] . '</div>';
                    unset($_SESSION['success_message']);
                }
                ?>

                <!-- Form for creating a new entry -->
                <div class="form-container">
                    <form method="POST" action="">

                        <div class="form-wrapper">
                            <!-- Left Column -->
                            <div class="form-column">
                                <div class="form-group">
                                    <label for="payee">Payee:</label>
                                    <input type="text" name="payee" required>
                                </div>

                                <div class="form-group">
                                    <label for="office">Office:</label>
                                    <input type="text" name="office" required>
                                </div>

                                <div class="form-group">
                                    <label for="address">Address:</label>
                                    <input type="text" name="address" required>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="form-column">
                                <div class="form-group">
                                    <label for="responsibility_center">Responsibility Center:</label>
                                    <input type="text" name="responsibility_center" required>
                                </div>

                                <div class="form-group">
                                    <label for="particulars">Particulars:</label>
                                    <input type="text" name="particulars" required>
                                </div>

                                <div class="form-group">
                                    <label for="uacs_code">UACS Code:</label>
                                    <select name="uacs_code" required>
                                        <?php foreach ($uacs_codes as $code): ?>
                                            <option value="<?php echo $code['code']; ?>">
                                                <?php echo htmlspecialchars($code['code']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="amount">Amount:</label>
                                    <input type="number" name="amount" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <input type="submit" name="admin_form_creation" value="Create">
                        </div>
                    </form>
                </div>
            </div>
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
    </script>

</body>
</html>
