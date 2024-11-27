<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'apartment_management');

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all water payments
$sql = "SELECT * FROM water_payment_history ORDER BY payment_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Bill Payment Report</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="JRSLCSS/rental_payment.css"> <!-- Applying same rental payment styles -->
</head>
<body>
    
    <div class="container">
        <h1>Water Bill Payment Report</h1>
        <div class="back-button">
            <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>

        <!-- Table to display water bill payment details -->
        <table>
            <thead>
                <tr>
                    <th>Unit Number</th>
                    <th>Computation Date</th>
                    <th>Monthly Water Bill</th>
                    <th>Due Date</th>
                    <th>Current Status</th>
                    <th>Last Payment Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['unit_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['payment_date']); ?></td>
                        <td>PHP <?php echo htmlspecialchars(number_format($row['monthly_water_bill'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['current_status']); ?></td>
                        <td><?php echo !empty($row['last_payment_date']) ? htmlspecialchars($row['last_payment_date']) : 'N/A'; ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No water bill payment records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
// Close the connection
$conn->close();
?>
