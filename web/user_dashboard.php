<?php
session_start();
include '../db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: web/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data from tenant_account table based on the logged-in user
$stmt = $pdo->prepare("SELECT username, unit_number, first_name, last_name, middle_name, ext_name, birth_date, gender, age, address, email, phone, emergency_contact_name, relationship, emergency_contact_number, profile_picture, move_in_date FROM tenant_account WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "<script>alert('User not found.'); window.location.href='login.php';</script>";
    exit();
}

$unit_number = $user['unit_number'];
$move_in_date = $user['move_in_date'];

// Fetch unpaid water bills for the tenant's unit
$water_bills_stmt = $pdo->prepare("
    SELECT calculation_month, water_bill, due_date
    FROM water_calculations 
    WHERE unit_number = ? AND current_status = 'Unpaid'
    ORDER BY STR_TO_DATE(calculation_month, '%M %Y') ASC
");
$water_bills_stmt->execute([$unit_number]);
$water_bills = $water_bills_stmt->fetchAll();

// Fetch unpaid electricity bills for the tenant's unit
$electricity_bills_stmt = $pdo->prepare("
    SELECT calculation_month, electricity_bill, due_date
    FROM electricity_calculations 
    WHERE unit_number = ? AND current_status = 'Unpaid'
    ORDER BY STR_TO_DATE(calculation_month, '%M %Y') ASC
");
$electricity_bills_stmt->execute([$unit_number]);
$electricity_bills = $electricity_bills_stmt->fetchAll();

// Rent Calculation Logic
$rent_stmt = $pdo->prepare("SELECT rent FROM rooms WHERE unit_number = ?");
$rent_stmt->execute([$unit_number]);
$rent_data = $rent_stmt->fetch();
$rent_per_month = $rent_data ? $rent_data['rent'] : 0;

$current_date = new DateTime();
if ($move_in_date) {
    $move_in_date = new DateTime($move_in_date);
    $months_stayed = $move_in_date->diff($current_date)->m + ($move_in_date->diff($current_date)->y * 12) + 1;
    $total_rent_due = $months_stayed * $rent_per_month;

    // Get total amount paid
    $payment_stmt = $pdo->prepare("
        SELECT SUM(amount_paid) AS total_paid 
        FROM rent_payments 
        WHERE unit_number = ?
    ");
    $payment_stmt->execute([$unit_number]);
    $payment_data = $payment_stmt->fetch();
    $total_paid = $payment_data ? $payment_data['total_paid'] : 0;

    // Calculate balance and determine payment status
    $balance = max(0, $total_rent_due - $total_paid);
    $status = $balance <= 0 ? 'Paid' : ($total_paid > 0 ? 'Partial Payment' : 'Unpaid');
} else {
    $total_rent_due = 0;
    $total_paid = 0;
    $balance = 0;
    $status = 'N/A';
}

// Get the last payment date
$last_payment_stmt = $pdo->prepare("
    SELECT MAX(payment_date) AS last_payment_date 
    FROM rent_payments 
    WHERE unit_number = ?
");
$last_payment_stmt->execute([$unit_number]);
$last_payment_data = $last_payment_stmt->fetch();
$last_payment_date = $last_payment_data && $last_payment_data['last_payment_date'] ? $last_payment_data['last_payment_date'] : 'N/A';

// Calculate total unpaid water bill
$total_water_due = 0;
foreach ($water_bills as $bill) {
    $total_water_due += $bill['water_bill'];
}

// Calculate total unpaid electricity bill
$total_electricity_due = 0;
foreach ($electricity_bills as $bill) {
    $total_electricity_due += $bill['electricity_bill'];
}

// Calculate total balance due
$total_balance_due = $total_water_due + $total_electricity_due + $balance;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="user_dashboard.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img src="img/jrsl_logo.png" alt="JRSL Logo">
            </div>
            <nav>
                <ul>
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="#"> Rent & Bills History</a></li>
                    <li><a href="#">Payment History</a></li>
                    <li><a href="logout.php">Log Out</a></li>
                </ul>
            </nav>
        </div>
        <div class="main-content">
            <div class="header">
                <h2>Welcome to Room #<?php echo htmlspecialchars($user['unit_number']); ?>, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                <div class="profile">
                    <div class="avatar">
                        <img src="img/pic1.jpg" alt="Profile Picture">
                    </div>
                    <a href="#" class="edit-profile" onclick="showForm()">Edit Profile</a>
                </div>
            </div>
            <div class="dashboard-content">
                <!-- Water Bills -->
                <div class="monthly-bills card">
                    <h3>Water Bills</h3>
                    <?php if (count($water_bills) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($water_bills as $bill): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($bill['calculation_month']); ?></td>
                                            <td>PHP <?php echo number_format($bill['water_bill'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($bill['due_date']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No unpaid water bills.</p>
                    <?php endif; ?>
                </div>

                <!-- Electricity Bills -->
                <div class="balances card">
                    <h3>Electricity Bills</h3>
                    <?php if (count($electricity_bills) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($electricity_bills as $bill): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($bill['calculation_month']); ?></td>
                                            <td>PHP <?php echo number_format($bill['electricity_bill'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($bill['due_date']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No unpaid electricity bills.</p>
                    <?php endif; ?>
                </div>

                <!-- Rent Card -->
                <div class="rent card">
    <h3>Rent</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Monthly Rent</th>
                    <th>Total Due</th>
                    <th>Status</th>
                    
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>PHP <?php echo number_format($rent_per_month, 2); ?></td>
                    <td>PHP <?php echo number_format($total_rent_due, 2); ?></td>
                    <td><?php echo htmlspecialchars($status); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


                <!-- Balances Card -->
                <div class="balances-section">
    <div class="balances card">
        <h3>Total Balances</h3>
        <p>PHP <?php echo number_format($total_balance_due, 2); ?></p>
        <button onclick="openReceiptModal()">Generate Receipt</button>
        
    </div>

   <!-- Modal for Receipt Section -->
<div id="receiptModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeReceiptModal()">&times;</span>
        <h3>Balance Summary</h3>

        <!-- Water Bills -->
        <h4>Water Bills</h4>
        <?php if (count($water_bills) > 0): ?>
            <ul>
                <?php foreach ($water_bills as $bill): ?>
                    <li>
                        Month: <?php echo htmlspecialchars($bill['calculation_month']); ?> - 
                        Amount: PHP <?php echo number_format($bill['water_bill'], 2); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No unpaid water bills.</p>
        <?php endif; ?>

        <!-- Electricity Bills -->
        <h4>Electricity Bills</h4>
        <?php if (count($electricity_bills) > 0): ?>
            <ul>
                <?php foreach ($electricity_bills as $bill): ?>
                    <li>
                        Month: <?php echo htmlspecialchars($bill['calculation_month']); ?> - 
                        Amount: PHP <?php echo number_format($bill['electricity_bill'], 2); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No unpaid electricity bills.</p>
        <?php endif; ?>

        <!-- Rent -->
        <h4>Rent</h4>
        <p>Monthly Rent: PHP <?php echo number_format($rent_per_month, 2); ?></p>
        <p>Total Rent Due: PHP <?php echo number_format($total_rent_due, 2); ?></p>

        <hr>
        <p><strong>Total Balance:</strong> PHP <?php echo number_format($total_balance_due, 2); ?></p>
        <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
        <button onclick="printReceipt()">Print Receipt</button>
    </div>
</div>

<style>
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: #fff;
        margin: 10% auto;
        padding: 20px;
        border-radius: 8px;
        width: 50%;
        position: relative;
        text-align: center;
    }

    .close {
        position: absolute;
        top: 10px;
        right: 20px;
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }
</style>

<script>
    // Open the modal
    function openReceiptModal() {
        document.getElementById("receiptModal").style.display = "block";
    }

    // Close the modal
    function closeReceiptModal() {
        document.getElementById("receiptModal").style.display = "none";
    }

    // Print the receipt
    function printReceipt() {
        const modalContent = document.querySelector(".modal-content").innerHTML;
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Receipt</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; text-align: center; }
                        p, li { margin: 5px 0; }
                        ul { list-style-type: none; padding: 0; }
                        hr { margin: 10px 0; }
                    </style>
                </head>
                <body>${modalContent}</body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
</script>

<!-- Trigger Button -->


</body>
</html>
