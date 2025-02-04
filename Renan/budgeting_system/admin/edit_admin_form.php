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

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch the admin's username and form data
    $stmt = $pdo->prepare("SELECT u.username, uf.* 
                           FROM admin_funds uf 
                           JOIN admins u ON uf.user_id = u.id 
                           WHERE uf.user_id = ?");
    $stmt->execute([$user_id]);
    $forms = $stmt->fetchAll(); // Fetch all forms for the admin

    // If no forms exist for the user, redirect to the 'No Submitted Form' message
    if (empty($forms)) {
        header("Location: no_form_created.php?user_id=$user_id");
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
    // Debug: Check if form data is being received
    echo "<pre>";
    print_r($_POST); // Check POST data
    echo "</pre>";

    // Collect the updated form data
    $payee = $_POST['payee'];
    $office = $_POST['office'];
    $address = $_POST['address'];
    $responsibility_center = $_POST['responsibility_center'];
    $particulars = $_POST['particulars'];
    $uacs_code = $_POST['uacs_code'];
    $amount = $_POST['amount'];
    $form_id = $_POST['id']; // Use the 'id' as the unique identifier

    // Debug: Print the form data that will be sent to the query
    echo "Form Data: <br>";
    echo "Payee: $payee <br>";
    echo "Office: $office <br>";
    echo "Address: $address <br>";
    echo "Responsibility Center: $responsibility_center <br>";
    echo "Particulars: $particulars <br>";
    echo "UACS Code: $uacs_code <br>";
    echo "Amount: $amount <br>";
    echo "Form ID: $form_id <br>";

    // Update the admin's form in the database
    try {
        $stmt = $pdo->prepare("UPDATE admin_funds 
                               SET payee = ?, office = ?, address = ?, responsibility_center = ?, particulars = ?, uacs_code = ?, amount = ? 
                               WHERE id = ?");
        $stmt->execute([$payee, $office, $address, $responsibility_center, $particulars, $uacs_code, $amount, $form_id]);

        // Check if any rows were updated
        if ($stmt->rowCount() > 0) {
            echo "Form updated successfully!";
        } else {
            echo "No changes were made to the form.";
        }

        // Set success message in session
        $_SESSION['success_message'] = "Form updated successfully!";
        
        // Redirect to avoid form resubmission
        header("Location: edit_admin_form.php?user_id=$user_id");
        exit();
    } catch (Exception $e) {
        // Error handling for the update query
        echo "Error: " . $e->getMessage(); // Display any errors in the query execution
    }
}

// Handle form deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Delete the selected form from the database
    $stmt = $pdo->prepare("DELETE FROM admin_funds WHERE id = ?");
    $stmt->execute([$delete_id]);

    // Set success message for deletion
    $_SESSION['success_message'] = "Form deleted successfully!";
    
    // Redirect to avoid form resubmission
    header("Location: edit_admin_form.php?user_id=$user_id");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/editsss.css">
    <link rel="stylesheet" href="../assets/css/sidebar_men.css">
    <link rel="stylesheet" href="../assets/css/payees_list.css">
    <link rel="stylesheet" href="../assets/css/headers.css">

    <title>Edit Form Admins</title>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Main Content Area -->
    <div class="content">
        <!-- Include Header -->
        <?php include('header.php'); ?>

        <div class="content-wrapper">
            <div class="container">
                <button class="back-btn" onclick="window.history.back()">Back</button>
                <button class="forms-btn" onclick="window.location.href='admin_form.php'">Forms</button>

                <h1>Edit Form for '<?php echo isset($forms[0]) ? htmlspecialchars($forms[0]['username']) : ''; ?>'</h1>

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
                        <div class="form-container" id="payee-<?php echo htmlspecialchars($form['payee']); ?>">
                            <!-- Inside the form tag -->
                            <form method="POST" action="">
                                <input type="hidden" name="id" value="<?php echo $form['id']; ?>"> <!-- This is the ID of the form in user_funds -->

                                <div class="form-wrapper">
                                    <!-- Left Column -->
                                    <div class="form-column">
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
                                    </div>

                                    <!-- Right Column -->
                                    <div class="form-column">
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
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <input type="submit" name="update_form" value="Update">
                                    <a href="delete_form.php?form_id=<?php echo $form['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this form?')">Delete</a>
                                </div>
                            </form>

                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="form-container">
                        <p class="no-form-message">No form has been submitted by this office yet.</p>
                    </div>
                <?php endif; ?>
            </div>

            <?php include('list/admin_payee_list.php'); ?>
            
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


        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".payee-item").forEach(item => {
                item.addEventListener("click", function () {
                    const payeeName = this.getAttribute("data-payee").trim().toLowerCase();
                    const formContainers = document.querySelectorAll(".form-container");

                    let targetForm = null;

                    formContainers.forEach(form => {
                        const formPayeeInput = form.querySelector("input[name='payee']");
                        if (formPayeeInput && formPayeeInput.value.trim().toLowerCase() === payeeName) {
                            targetForm = form;
                        }
                    });

                    if (targetForm) {
                        const offset = 100; // Adjust this value as needed
                        const targetPosition = targetForm.getBoundingClientRect().top + window.scrollY - offset;

                        window.scrollTo({ top: targetPosition, behavior: "smooth" });

                        targetForm.classList.add("highlight");

                        // Remove highlight after 2 seconds
                        setTimeout(() => {
                            targetForm.classList.remove("highlight");
                        }, 1000);
                    }
                });
            });
        });



    </script>

</body>
</html>
