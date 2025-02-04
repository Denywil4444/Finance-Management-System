<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$office_name = $_SESSION['office_name'] ?? '';

// Map the fund type short form to human-readable value
$fund_type_map = [
    'business' => 'Business Related Funds',
    'internally_generated' => 'Internally Generated Fund',
    // Add more types if needed
];

// Fetch all UACS codes
$uacs_stmt = $pdo->prepare("SELECT * FROM uacs_codes");
$uacs_stmt->execute();
$uacs_codes = $uacs_stmt->fetchAll();

// Fetch the current funds of the user, ensure it's numeric
$fund_stmt = $pdo->prepare("SELECT funds FROM users WHERE id = ?");
$fund_stmt->execute([$user_id]);
$user_funds = floatval($fund_stmt->fetchColumn());  // Ensure it's treated as a float

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $fund_type_short = $_POST['fund_type'];  // Get the short form (business, internally_generated)
    $fund_type = $fund_type_map[$fund_type_short] ?? 'Business Related Funds';  // Map to human-readable value
    
    $payee = $_POST['payee'];
    $office = $_POST['office'];
    $address = $_POST['address'];
    $responsibility_center = $_POST['responsibility_center'];
    $particulars = $_POST['particulars'];
    $uacs_code = $_POST['uacs_code'];
    $amount = floatval($_POST['amount']);  // Ensure the amount is treated as a float

    // Check if user has enough funds
    if ($user_funds >= $amount) {
        // Deduct the amount from the user's funds
        $new_funds = $user_funds - $amount;
        $update_funds_stmt = $pdo->prepare("UPDATE users SET funds = ? WHERE id = ?");
        $update_funds_stmt->execute([$new_funds, $user_id]);

        // Insert data into user_funds table with human-readable fund type
        $stmt = $pdo->prepare("INSERT INTO user_funds (user_id, fund_type, payee, office, address, responsibility_center, particulars, uacs_code, amount)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $fund_type, $payee, $office, $address, $responsibility_center, $particulars, $uacs_code, $amount]);

        // Update the form_submitted count in users table
        $update_stmt = $pdo->prepare("UPDATE users SET form_submitted = form_submitted + 1 WHERE id = ?");
        $update_stmt->execute([$user_id]);

        // Set session success message
        $_SESSION['success_message'] = "Form submitted successfully!";

        // Redirect to display success message
        header("Location: fund_form.php");
        exit();
    } else {
        // If the user doesn't have enough funds
        $_SESSION['error_message'] = "Insufficient funds. Please try again or check your remaining funds.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fund Form</title>
    <link rel="stylesheet" href="/budgeting_system/assets/css/user-fund-form.css">
</head>
<body>

    <div class="container">
        <button class="back-btn" onclick="window.location.href = 'dashboard.php';">Back</button>

        <h1>Fund Form</h1>

        <!-- Display success message if set -->
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="success-message">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']);
        }

        // Display error message if set
        if (isset($_SESSION['error_message'])) {
            echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <form method="POST" action="fund_form.php">

            <div class="form-group">
                <label for="fund_type">Select Fund Type:</label>
                <select name="fund_type" required>
                    <option value="business">Business Related Funds</option>
                    <option value="internally_generated">Internally Generated Fund</option>
                    <!-- Add more options as needed -->
                </select>
            </div>

            <div class="form-group">
                <label for="payee">Payee:</label>
                <input type="text" name="payee" required>
            </div>
            
            <div class="form-group">
                <label for="office">Office:</label>
                <input type="text" name="office" value="<?php echo htmlspecialchars($office_name); ?>" required>
            </div>

            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" name="address" required>
            </div>

            <div class="form-group">
                <label for="responsibility_center">Responsibility Center:</label>
                <input type="text" name="responsibility_center" value="<?php echo htmlspecialchars($office_name); ?>" required>
            </div>

            <div class="form-group">
                <label for="particulars">Particulars:</label>
                <input type="text" name="particulars" required>
            </div>

            <div class="form-group">
                <label for="uacs_code">UACS Code:</label>
                <select name="uacs_code" required>
                    <?php foreach ($uacs_codes as $code): ?>
                        <option value="<?php echo $code['code']; ?>"><?php echo htmlspecialchars($code['code']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="amount">Amount:</label>
                <input type="number" name="amount" required>
            </div>

            <!-- Submit Button -->
            <input type="submit" value="Submit">
        </form>

    </div>

</body>
</html>
