<?php
session_start();
include('../includes/db.php'); // Ensure this file contains $pdo

// Ensure the database connection is set
if (!isset($pdo)) {
    die("Database connection is not set. Check db.php!");
}

// Get the current user_id and user_funds id from the query string
$username = isset($_GET['username']) ? $_GET['username'] : '';
$fund_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch distinct usernames from the users table
$user_query = "SELECT u.username FROM users u ORDER BY u.username ASC";
$user_stmt = $pdo->prepare($user_query);
$user_stmt->execute();
$usernames = $user_stmt->fetchAll(PDO::FETCH_COLUMN, 0); // Fetch usernames as an array

// Fetch fund ids (payees) for the selected username
$fund_query = "SELECT uf.id, uf.payee FROM user_funds uf JOIN users u ON uf.user_id = u.id WHERE u.username = ?";
$fund_stmt = $pdo->prepare($fund_query);
$fund_stmt->execute([$username]);
$funds = $fund_stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch payee and fund IDs

// Fetch user_funds for the selected username and fund_id
$stmt = $pdo->prepare("SELECT * FROM user_funds WHERE user_id = (SELECT id FROM users WHERE username = ?) AND id = ?");
$stmt->execute([$username, $fund_id]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Form</title>
    <link rel="stylesheet" href="/budgeting_system/assets/css/test.css">
</head>
<body>
    <h2>Select Username and Fund</h2>
    <!-- Dropdown for selecting username -->
    <form method="get" action="">
        <label for="username">Username: </label>
        <select name="username" id="username" onchange="this.form.submit()">
            <option value="">-- Select a Username --</option>
            <?php foreach ($usernames as $uname) { ?>
                <option value="<?php echo $uname; ?>" <?php if ($uname == $username) echo 'selected'; ?>><?php echo $uname; ?></option>
            <?php } ?>
        </select>

        <?php if ($username) { // Show fund_id dropdown if username is selected ?>
            <label for="id">Funds (Payee): </label>
            <select name="id" id="id" onchange="this.form.submit()">
                <option value="">-- Select a Fund (Payee) --</option>
                <?php foreach ($funds as $fund) { ?>
                    <option value="<?php echo $fund['id']; ?>" <?php if ($fund['id'] == $fund_id) echo 'selected'; ?>><?php echo $fund['payee']; ?></option>
                <?php } ?>
            </select>
        <?php } ?>
    </form>

    <button onclick="window.location.href='dashboard.php'">Back to Dashboard</button>

    <?php if ($result) { // Check if data is found ?>
        <?php foreach ($result as $row) { ?>
            <table>
                <tr>
                    <td class="left-column">
                        <div class="code">
                            <span>NISU-MCFoSR-BO03</span>
                            <span>November 1, 2024</span>
                        </div>
                        <img src="http://localhost/budgeting_system/images/nisuBG.jpg" alt="NISU Logo">
                        <div class="text-container">
                            <span class="title">BUDGET UTILIZATION REQUEST AND STATUS</span>
                            <span class="subtext">NORTHERN ILOILO STATE UNIVERSITY</span>
                            <span class="subtext">Estancia, Iloilo</span>
                        </div>
                    </td>
                    <td class="right-column">
                        <div><label>No. :</label> <span>02-207512-2025-00002</span></div>
                        <div><label>Date:</label> <span>January 30, 2025</span></div>
                        <div>
                            <label>Fund:</label> 
                            <span>06</span>
                        </div>
                        <div class="highlight-container">
                            <div class="highlight"><?php echo htmlspecialchars($row['fund_type']); ?></div>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Table 2 -->
            <table class="second-table">
                <tr>
                    <td class="payee">Payee</td>
                    <td class="payee1"><?php echo htmlspecialchars($row['payee']); ?></td>
                </tr>
                <tr>
                    <td class="payee">Office</td>
                    <td class="payee1">
                        <label><?php echo htmlspecialchars($row['office']); ?></label>
                    </td>
                </tr>
                <tr>
                    <td class="payee">Address</td>
                    <td class="payee1">
                        <label><?php echo htmlspecialchars($row['address']); ?></label>
                    </td>
                </tr>
            </table>

            <!-- Table 3 -->
            <table class="third-table">
                <tr>
                    <td>
                        <label>Responsibility Center</label>
                    </td>
                    <td>
                        <label>Particulars</label>
                    </td>
                    <td>
                        <label>MFO/PAP</label>
                    </td>
                    <td>
                        <label>UACS Code</label>
                    </td>
                    <td>
                        <label>Amount</label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label><?php echo htmlspecialchars($row['responsibility_center']); ?></label>
                    </td>
                    <td>
                        <label><?php echo htmlspecialchars($row['particulars']); ?></label>
                    </td>
                    <td>
                        <label>MFO/PAP</label>
                    </td>
                    <td>
                        <label><?php echo htmlspecialchars($row['uacs_code']); ?></label>
                    </td>
                    <td>
                        <label><?php echo htmlspecialchars($row['amount']); ?></label>
                    </td>
                </tr>
            </table>

            <div class="fourth-table">
                <div class="top-left-box">A.</div>
                <table>
                    <tr>
                        <td>
                            <label>Payee</label>
                        </td>
                        <td>
                            <div class="top-right-box">B.</div>
                            <label>Payee 1</label>
                        </td>
                    </tr>
                </table>
            </div>

        <?php } ?>
    <?php } else { ?>
        <p>No data found for the selected username and fund.</p> <!-- Message if no data is found -->
    <?php } ?>
</body>
</html>
