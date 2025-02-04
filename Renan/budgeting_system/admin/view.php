<?php
session_start();
include('../includes/db.php');

// Fetch the form ID from the query parameter
if (!isset($_GET['user_id'])) {
    die("Form ID not provided.");
}
$form_id = $_GET['user_id'];

// Fetch user_id associated with the form
$stmt = $pdo->prepare("SELECT user_id FROM user_funds WHERE id = ?");
$stmt->execute([$form_id]);
$form = $stmt->fetch();

if (!$form) {
    die("Form not found.");
}

$user_id = $form['user_id'];

// Fetch all user funds data for the specific user
$stmt = $pdo->prepare("SELECT uf.payee, uf.office, uf.address, uf.uacs_code, uf.amount, uf.responsibility_center, uf.particulars FROM user_funds uf WHERE uf.user_id = ?");
$stmt->execute([$user_id]);
$user_funds = $stmt->fetchAll();

// Group user funds by payee
$payees = [];
foreach ($user_funds as $user) {
    $payees[$user['payee']][] = $user;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Form</title>
        <style>
            body {
                display: flex;
                justify-content: center;
                align-items: center; /* Align the content at the top */
                flex-direction: column; /* Stack tables vertically */
                margin: 0;
                font-family: Arial, sans-serif;
                height: auto; /* Allow height to adjust */
                overflow-y: auto; /* Enable vertical scrolling */
                margin-top: 5%;
                margin-bottom: 5%;
            }
            table {
                border-collapse: collapse;
                width: 100%;
                max-width: 800px;
                table-layout: fixed;
                margin-bottom: 0px; /* Reduced margin between tables */
            }
            tr {
                display: flex;
                width: 100%;
            }
            td {
                padding: 20px;
                border: 1px solid #000;
                vertical-align: middle;
                width: 33%; /* Adjust width to have three columns */
            }
            .left-column {
                display: flex;
                align-items: center;
                text-align: center;
                width: 80%;
            }
            .left-column img {
                max-width: 80px;
                height: auto;
                margin-right: 20px;
            }
            .text-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                width: 100%;
            }
            .text-container .title {
                font-size: 12px; /* Smaller text */
                font-weight: bold;
            }
            .text-container .subtext {
                font-size: 11.5px; /* Smaller text */
                font-weight: normal; /* Not bold */
            }
            
            .right-column {
                display: flex;
                flex-direction: column;
                width: 40%; /* The remaining width */
                padding-left: 1px;
                align-items: center;
            }

            .right-column div {
                display: flex;
                align-items: center;
                width: 100%;
                margin-bottom: 5px;
                justify-content: center; /* Center contents */
                font-size: 10px;
            }
            .right-column label {
                font-weight: bold;
                text-align: left;
                flex: 1;
                padding-right: 10px;
            }
            .right-column input, 
            .right-column span {
                flex: 2;
                text-align: center;
                border: none;
                outline: none;
                font-size: 11px;
                background: transparent;
                width: 100%;
            }
            /* For input elements inside the right column */
            .right-column input {
                border-bottom: 1px solid #000;
                padding: 0.5px; /* Reduce padding to make it smaller */
            }

            /* For the highlight element */
            .highlight {
                background-color:rgb(192, 79, 76); /* Light green background */
                padding: 1px 2px; /* Even smaller padding */
                font-size: 5px; /* Smaller font size */
                font-weight: bold;
                text-align: center;
                display: inline-block; /* Makes the background fit the content width */
                width: auto; /* Makes the width adjust to content, smaller */
                max-width: 150px; /* Limits the maximum width, can be further reduced */
                margin-left: 80px; /* Shift the element to the right */
            }

            .highlight-container {
                display: flex;
                align-items: center;
                justify-content: flex-start; /* Align left */
                width: 100%;
                margin-top: 10px; /* Optional: Adds space between the highlight and other elements */
                margin-left: 10px; /* Adds space to the left to shift it to the right */
                font-size: 5px; /* Smaller text */
            }

            .code {
                margin-bottom: 90px;
                font-size: 9px;
                padding-left: 1px;
            }

            /************************************************* Styling for the second table */
            .second-table td {
                padding: 5px;
                font-size: 13px;
            }

            /* Make the first column smaller */
            .second-table td:first-child {
                width: 23.8%;
            }

            /* Make the other columns larger */
            .second-table td:not(:first-child) {
                width: 100%;
            }
            
            .payee {
                text-align: center;
            }

            .payee1 {
                text-align: center;
            }

            /************************************************* Styling for the third table */

            .third-table td {
                padding: 5px;
                width: 100%;
                text-align: center;
                font-size: 13px;
                position: relative; /* Allow absolute positioning inside */
            }

            .bottom-text {
                position: absolute;
                bottom: 1px; /* Adjust this value if needed */
                left: 115px;
                width: 100%;
                text-align: center;
                font-size: 13px;
                
            }

            .third-table td:first-child{
                width: 160%;
            }

            .third-table td:nth-child(2) {
                width: 300%; /* Set the width of the second column to 60px */
                
            }

            .third-table td:nth-child(3) {
                width: 50%; /* Set the width of the second column to 60px */
                
            }

            .third-table td:nth-child(4) {
                width: 110%; /* Set the width of the second column to 60px */
                /*padding-right: 10%; */
            }

            .third-table td:nth-child(5) {
                width: 150%; /* Set the width of the second column to 60px */
                /*padding-right: 10%; */
            }
        

            /* Increase height for the specific columns in the third table */
            .third-table tr:nth-child(2) td {
                height: 350px; /* Increase height for the second row only */
            }

            /************************************************* Styling for the fourth table */
            .fourth-table {
                position: relative; /* Allow absolute positioning within this container */
            }

            .top-left-box {
                position: absolute;
                top: 0;
                left: 0;
                padding: 10px;
                /*background-color: #f0f0f0;*/
                border: 2px solid #000;
                font-size: 12px;
                width: 60px; /* Adjust box width */
                text-align: left;
                font-weight: bold;
            }

            .top-right-box {
                position: absolute;
                top: 0;
                left: 56.7%;
                padding: 10px;
                /*background-color: #f0f0f0;*/
                border: 2px solid #000;
                font-size: 12px;
                width: 46.5px; /* Adjust box width */
                text-align: left;
                font-weight: bold;
            }

            /* Existing styles */
            .fourth-table td {
                padding: 5px;
                font-size: 13px;
                width: 100%;
                text-align: center;
            }

            /* Make the first column smaller */
            .fourth-table td:first-child {
                width: 133.5%;
                height: 200px;
            }

            /********************************************************************** */

            .payee-container {
            display: flex;
            flex-wrap: wrap; /* Allow wrapping if space is insufficient */
            justify-content: space-between; /* Spread tables evenly */
            gap: 20px; /* Adds space between tables */
            margin-bottom: 30px;
        }

        .payee-table {
            flex: 1; /* Allow equal distribution of space */
            min-width: 48%; /* Prevent tables from becoming too small */
            border: 1px solid #000;
            padding: 10px;
            box-sizing: border-box;
        }

            /**.payee-table {
                margin-bottom: 20px;
            }
            .payee-table h3 {
                font-size: 18px;
                margin-bottom: 10px;
            }

            .payee-table table {
                width: 100%;
                border-collapse: collapse;
            }

            .payee-table th, .payee-table td {
                border: 1px solid #000;
                padding: 10px;
                text-align: center;
            }

            .navigation-button {
                cursor: pointer;
                color: blue;
                text-decoration: underline;
            } */
        </style>
</head>
<body>
    <!-- First Table 
    <table>
        <tr>
            <!-- Left Column 
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

            <!-- Right Column 
            <td class="right-column">
                <div><label>No. :</label> <span>02-207512-2025-00002</span></div>
                <div><label>Date:</label> <span>January 30, 2025</span></div>
                <div>
                    <label>Fund:</label> 
                    <span>06</span>
                </div>
                <div class="highlight-container">
                    <div class="highlight">Business Related Funds</div>
                </div>
            </td>
        </tr>
    </table> -->

    <h2>User Funds Details</h2>
    <p>User ID: <?php echo htmlspecialchars($user_id); ?></p>

    <button onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
    <!-- Loop through each payee and create a separate set of tables for each -->
    <div class="payee-container">
    <?php foreach ($payees as $payee => $funds): ?>
        <div class="payee-table">
        <h3>Payee: <?php echo htmlspecialchars($payee); ?></h3>

            <!-- Table 1 -->
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
                            <div class="highlight">Business Related Funds</div>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Table 2 -->
            <?php foreach ($funds as $user): ?>
            <table class="second-table">
                <tr>
                    <td class="payee">Payee</td>
                    <td class="payee1"><?php echo htmlspecialchars($payee); ?></td>
                </tr>
                <tr>
                    <td class="payee">Office</td>
                    <td class="payee1">
                        
                        <div><?php echo htmlspecialchars($user['office']); ?></div>
                        
                    </td>
                </tr>
                <tr>
                    <td class="payee">Address</td>
                    <td class="payee1">
                        
                        <div><?php echo htmlspecialchars($user['address']); ?></div>
                        
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
                        <td><?php echo htmlspecialchars($user['responsibility_center']); ?></td>
                        <td><?php echo htmlspecialchars($user['particulars']); ?></td>
                        <td>MFO/PAP</td>
                        <td><?php echo htmlspecialchars($user['uacs_code']); ?></td>
                        <td><?php echo htmlspecialchars($user['amount']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                
            </table>

            <!-- Table 4 -->
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

        </div>
        <?php endforeach; ?>
    </div>

</body>
</html>
