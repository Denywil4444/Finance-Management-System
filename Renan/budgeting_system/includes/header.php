<header>
    <nav>
        <a href="../index.php">Home</a>
        <?php if (isset($_SESSION['role'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="../admin/dashboard.php">Dashboard</a>
            <?php else: ?>
                <a href="../user/dashboard.php">Dashboard</a>
            <?php endif; ?>
        <?php endif; ?>
        <a href="../logout.php">Logout</a>
    </nav>
</header>
