<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../includes/db.php');

// Fetch the admin's username
$admin_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
?>
<header>
    <h1>Financial Management Services</h1>
    <div class="dropdown">
        <button class="dropdown-btn"><?= htmlspecialchars($admin['username']) ?></button>
        <div class="dropdown-content">
            <a href="settings.php">Settings</a>
            <a href="?logout=true">Logout</a>
        </div>
    </div>
</header>
