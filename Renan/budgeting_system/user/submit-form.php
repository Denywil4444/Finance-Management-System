<?php
session_start();
include('../includes/db.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fund_type = $_POST['fund_type'];
    $code = $_POST['code'];
    $amount = $_POST['amount'];

    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, fund_type, code, amount) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $fund_type, $code, $amount]);
    $success = "Form submitted successfully!";
}
?>
