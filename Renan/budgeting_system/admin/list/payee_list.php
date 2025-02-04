<?php
include('../includes/db.php');

// Ensure `user_id` is set before fetching payees
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch the fund_type for the logged-in admin
    $stmt = $pdo->prepare("SELECT fund_type FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]); // assuming the admin's user_id is stored in the session
    $admin = $stmt->fetch();

    if ($admin) {
        // Fetch all payees with the same fund_type as the admin
        $payee_stmt = $pdo->prepare("
            SELECT DISTINCT payee 
            FROM user_funds 
            WHERE user_id = ? 
            AND fund_type = ?
        ");
        $payee_stmt->execute([$user_id, $admin['fund_type']]);
        $payees = $payee_stmt->fetchAll();
    } else {
        $payees = [];
    }
} else {
    $payees = [];
}
?>

<!------- THIS IS FOR USER ------->

<div class="payee-list-container">
    <h2>Payees</h2>
    <?php if (count($payees) > 0): ?>
        <ul class="payee-list">
            <?php foreach ($payees as $payee): ?>
                <li class="payee-item" data-payee="<?php echo htmlspecialchars($payee['payee']); ?>">
                    <?php echo htmlspecialchars($payee['payee']); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No payees found.</p>
    <?php endif; ?>
</div>
