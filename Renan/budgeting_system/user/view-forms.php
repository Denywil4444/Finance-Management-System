<?php
session_start();
include('../includes/db.php');

if ($_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$forms = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ?");
$forms->execute([$user_id]);
$forms = $forms->fetchAll();
?>

<h1>Submitted Forms</h1>
<table border="1">
    <tr>
        <th>Type</th>
        <th>Code</th>
        <th>Amount</th>
        <th>Status</th>
    </tr>
    <?php foreach ($forms as $form): ?>
    <tr>
        <td><?= htmlspecialchars($form['fund_type']) ?></td>
        <td><?= htmlspecialchars($form['code']) ?></td>
        <td><?= number_format($form['amount'], 2) ?></td>
        <td><?= htmlspecialchars($form['status']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
