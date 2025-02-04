<?php
// Set active page class based on the current page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="username">
        <p><?= htmlspecialchars($admin['username']) ?></p>
    </div>
    <a href="dashboard.php" class="<?= ($current_page === 'dashboard.php') ? 'active' : '' ?>">Dashboard</a>
    <a href="manage-users.php" class="<?= ($current_page === 'manage-users.php') ? 'active' : '' ?>">Manage Offices</a>
    
    <a href="#" class="<?= ($current_page === 'submit_form.php, admin_create.php' ) ? 'active' : '' ?>"style="background: none; color: black;">Forms</a>
    <div class="sub-links">
        <a href="admin_create.php" class="<?= ($current_page === 'admin_create.php') ? 'active' : '' ?>">Admin</a>
        <a href="submit_form.php" class="<?= ($current_page === 'submit_form.php') ? 'active' : '' ?>">User</a>
    </div>

    <a href="update_form.php" class="<?= ($current_page === 'update-form.php') ? 'active' : '' ?>">View Form</a>
    <a href="settings.php" class="<?= ($current_page === 'settings.php') ? 'active' : '' ?>">Settings</a>
</div>