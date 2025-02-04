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
        SELECT u.id, u.username, u.role, COUNT(uf.id) AS forms_submitted 
        FROM users u 
        LEFT JOIN user_funds uf ON uf.user_id = u.id 
        GROUP BY u.id
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();
}

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

    <title>Forms</title>
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
        <a href="update_form.php">View Form</a>
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
                    <p class="forms-submitted">Forms Submitted: <?php echo $user['forms_submitted']; ?></p>
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


/** submit_form.php above */

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

    // Fetch the user's username and form data
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
    $fund_type = $_POST['fund_type'];
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
    echo "Fund Type: $fund_type <br>";
    echo "Form ID: $form_id <br>";

    // Update the user's form in the database
    try {
        $stmt = $pdo->prepare("UPDATE user_funds 
                               SET payee = ?, office = ?, address = ?, responsibility_center = ?, particulars = ?, uacs_code = ?, amount = ?, fund_type = ?
                               WHERE id = ?");
        $stmt->execute([$payee, $office, $address, $responsibility_center, $particulars, $uacs_code, $amount, $fund_type, $form_id]);

        // Check if any rows were updated
        if ($stmt->rowCount() > 0) {
            echo "Form updated successfully!";
        } else {
            echo "No changes were made to the form.";
        }

        // Set success message in session
        $_SESSION['success_message'] = "Form updated successfully!";
        
        // Redirect to avoid form resubmission
        header("Location: edit.php?user_id=$user_id");
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
    $stmt = $pdo->prepare("DELETE FROM user_funds WHERE id = ?");
    $stmt->execute([$delete_id]);

    // Set success message for deletion
    $_SESSION['success_message'] = "Form deleted successfully!";
    
    // Redirect to avoid form resubmission
    header("Location: edit.php?user_id=$user_id");
    exit();
}


///////////// payee for payee list
// Fetch all payees
$payee_stmt = $pdo->prepare("SELECT DISTINCT payee FROM user_funds WHERE user_id = ?");
$payee_stmt->execute([$user_id]);
$payees = $payee_stmt->fetchAll();
///////////// payee for payee list
// Fetch all payees




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/editsss.css">
    <link rel="stylesheet" href="../assets/css/sidebar_men.css">
    <link rel="stylesheet" href="../assets/css/payee_list.css">
    <link rel="stylesheet" href="../assets/css/header.css">

    <title>Edit Form Offices</title>
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
                <button class="back-btn" onclick="window.location.href='submit_form.php'">Back</button>
                <button class="forms-btn" onclick="window.location.href='Form.php'">Forms</button>

                <h1>Edit Form for '<?php echo isset($forms[0]) ? htmlspecialchars($forms[0]['username']) : ''; ?>'</h1>

                <!-- Success Message -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div id="success-message" class="success-message">
                        <?php 
                            echo $_SESSION['success_message']; 
                            unset($_SESSION['success_message']); // Remove message after displaying
                        ?>
                    </div>
                <?php endif; ?>

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
                                            <label for="fund_type">Fund Type:</label>
                                            <input type="text" name="fund_type" value="<?php echo htmlspecialchars($form['fund_type']); ?>" required>
                                        </div>
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

            <?php include('list/payee_list.php'); ?>
            
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

                        // Remove highlight after seconds
                        setTimeout(() => {
                            targetForm.classList.remove("highlight");
                        }, 1000);
                    }
                });
            });
        });




        // Hide success message after 2 seconds
        document.addEventListener("DOMContentLoaded", function () {
            const successMessage = document.getElementById("success-message");
            if (successMessage) {
                successMessage.style.display = "block"; // Show message
                setTimeout(() => {
                    successMessage.style.opacity = "0";
                    setTimeout(() => {
                        successMessage.style.display = "none";
                    }, 500); // Give time for fade out
                }, 2000);
            }
        });

    </script>

</body>
</html>















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

    // Fetch the user's username and form data
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
    $fund_type = $_POST['fund_type'];
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
    echo "Fund Type: $fund_type <br>";
    echo "Form ID: $form_id <br>";

    // Update the user's form in the database
    try {
        $stmt = $pdo->prepare("UPDATE user_funds 
                               SET payee = ?, office = ?, address = ?, responsibility_center = ?, particulars = ?, uacs_code = ?, amount = ?, fund_type = ?
                               WHERE id = ?");
        $stmt->execute([$payee, $office, $address, $responsibility_center, $particulars, $uacs_code, $amount, $fund_type, $form_id]);

        // Check if any rows were updated
        if ($stmt->rowCount() > 0) {
            echo "Form updated successfully!";
        } else {
            echo "No changes were made to the form.";
        }

        // Set success message in session
        $_SESSION['success_message'] = "Form updated successfully!";
        
        // Redirect to avoid form resubmission
        header("Location: edit.php?user_id=$user_id");
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
    $stmt = $pdo->prepare("DELETE FROM user_funds WHERE id = ?");
    $stmt->execute([$delete_id]);

    // Set success message for deletion
    $_SESSION['success_message'] = "Form deleted successfully!";
    
    // Redirect to avoid form resubmission
    header("Location: edit.php?user_id=$user_id");
    exit();
}


///////////// payee for payee list
// Fetch all payees
$payee_stmt = $pdo->prepare("SELECT DISTINCT payee FROM user_funds WHERE user_id = ?");
$payee_stmt->execute([$user_id]);
$payees = $payee_stmt->fetchAll();
///////////// payee for payee list
// Fetch all payees




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/editsss.css">
    <link rel="stylesheet" href="../assets/css/sidebar_men.css">
    <link rel="stylesheet" href="../assets/css/payee_list.css">
    <link rel="stylesheet" href="../assets/css/header.css">

    <title>Edit Form Offices</title>
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
                <button class="back-btn" onclick="window.location.href='submit_form.php'">Back</button>
                <button class="forms-btn" onclick="window.location.href='Form.php'">Forms</button>

                <h1>Edit Form for '<?php echo isset($forms[0]) ? htmlspecialchars($forms[0]['username']) : ''; ?>'</h1>

                <!-- Success Message -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div id="success-message" class="success-message">
                        <?php 
                            echo $_SESSION['success_message']; 
                            unset($_SESSION['success_message']); // Remove message after displaying
                        ?>
                    </div>
                <?php endif; ?>

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
                                            <label for="fund_type">Fund Type:</label>
                                            <input type="text" name="fund_type" value="<?php echo htmlspecialchars($form['fund_type']); ?>" required>
                                        </div>
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

            <?php include('list/payee_list.php'); ?>
            
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

                        // Remove highlight after seconds
                        setTimeout(() => {
                            targetForm.classList.remove("highlight");
                        }, 1000);
                    }
                });
            });
        });




        // Hide success message after 2 seconds
        document.addEventListener("DOMContentLoaded", function () {
            const successMessage = document.getElementById("success-message");
            if (successMessage) {
                successMessage.style.display = "block"; // Show message
                setTimeout(() => {
                    successMessage.style.opacity = "0";
                    setTimeout(() => {
                        successMessage.style.display = "none";
                    }, 500); // Give time for fade out
                }, 2000);
            }
        });

    </script>

</body>
</html>
