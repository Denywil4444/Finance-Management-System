
<?php
include('../includes/db.php');

// Ensure `user_id` is set before fetching payees
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

// payee for payee list
$payee_stmt = $pdo->prepare("SELECT DISTINCT payee FROM admin_funds WHERE user_id = ?");
$payee_stmt->execute([$user_id]);
$payees = $payee_stmt->fetchAll();

} else {
    $payees = [];
}
// payee for payee list
// Fetch all payees
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