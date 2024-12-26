<?php
// Database connection
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Your database username
define('DB_PASS', ''); // Your database password
define('DB_NAME', 'apartment_management');

// PDO connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    // Set PDO attributes (optional)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // Display error message and terminate script execution on connection failure
    die("Database connection failed: " . $e->getMessage());
}

// Fetch rental payments
try {
    $sql = "SELECT * FROM rent_payments ORDER BY payment_date DESC";
    $stmt = $pdo->query($sql);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching rental payments: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="JRSLCSS/rental_payment.css">
    <title>Rental Payment Report</title>
</head>
<body>
<?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h1>Rental Payment Report</h1>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Unit Number</th>
                    <th>Payment Date</th>
                    <th>Amount Paid</th>
                
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($payment['id']); ?></td>
                        <td><?php echo htmlspecialchars($payment['unit_number']); ?></td>
                        <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                        <td><?php echo htmlspecialchars($payment['amount_paid']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No rental payments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>