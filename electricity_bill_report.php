<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'apartment_management');

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all electricity payments
$sql = "SELECT unit_number, electricity_rate, electricity_consumption, previous_reading, current_reading, 
        meter_read_date, electricity_bill, calculation_month, due_date, last_payment_date, current_status 
        FROM electricity_calculations ORDER BY due_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electricity Bill Payment Report</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="JRSLCSS/rental_payment.css"> <!-- Applying same rental payment styles -->
</head>
<body>
<?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <h1><Em></Em>Electricity Bill Payment Report</h1>
        

        <!-- Table to display electricity bill payment details -->
        <table>
            <thead>
                <tr>
                    <th>Unit Number</th>
                    <th>Calculation Month</th>
                    <th>Meter Read Date</th>
                    <th>Electricity Bill</th>
                    <th>Due Date</th>
                    <th>Current Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['unit_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['calculation_month']); ?></td>
                        <td><?php echo htmlspecialchars($row['meter_read_date']); ?></td>
                        <td>PHP <?php echo htmlspecialchars(number_format($row['electricity_bill'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['current_status']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11">No electricity bill payment records found.</td>
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
