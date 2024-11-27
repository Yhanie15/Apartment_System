<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'apartment_management');

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$previous_reading = 0; // Default value for previous reading
$is_first_time = true; // Assume it's the first time calculating the bill

// Check if a unit number is provided in the GET request
if (isset($_GET['unit_number'])) {
    $unit_number = $_GET['unit_number'];

    // Fetch the most recent current reading from the previous calculations
    $latest_reading_sql = "
        SELECT current_reading 
        FROM electricity_calculations 
        WHERE unit_number = ? 
        ORDER BY STR_TO_DATE(calculation_month, '%M %Y') DESC, meter_read_date DESC 
        LIMIT 1
    ";
    $stmt = $conn->prepare($latest_reading_sql);
    $stmt->bind_param('s', $unit_number);
    $stmt->execute();
    $stmt->bind_result($previous_reading);
    if ($stmt->fetch()) {
        $is_first_time = false; // A record exists; it's not the first time
    }
    $stmt->close();

    // Fetch payment history for the unit
    $payment_sql = "
        SELECT * FROM electricity_payment_history 
        WHERE unit_number = ? 
        ORDER BY payment_date DESC
    ";
    $stmt = $conn->prepare($payment_sql);
    $stmt->bind_param('s', $unit_number);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch electricity calculations for the unit (remaining unpaid months)
    $calculation_sql = "
        SELECT * FROM electricity_calculations 
        WHERE unit_number = ? 
        AND calculation_month NOT IN (
            SELECT month_of FROM electricity_payment_history WHERE unit_number = ?
        )
        ORDER BY STR_TO_DATE(calculation_month, '%M %Y') DESC, meter_read_date DESC
    ";
    $stmt = $conn->prepare($calculation_sql);
    $stmt->bind_param('ss', $unit_number, $unit_number);
    $stmt->execute();
    $calculation_result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electicity Payment History</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="JRSLCSS/water_result.css"> 
    <link rel="stylesheet" href="JRSLCSS/bills_payment.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
    <div class="main-content">
        <a href="electricity_payment.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Electicity Payment Page</a>
        <h2>Payment History for Unit <?php echo htmlspecialchars($unit_number); ?></h2>

        <div class="search-section">
            <input type="text" placeholder="Quick Search" id="searchRentDetails">
            <button onclick="openModal('computeModal')" class="back-button">+ Compute Bills</button>
            <button onclick="openModal('updateModal')" class="back-button">+ Add Payment</button>
        </div>

        <h3>Remaining Electicity Bill Calculations</h3>
        <?php if ($calculation_result && $calculation_result->num_rows > 0): ?>
            <table class="payment-table">
                <thead>
                    <tr>
                    <th>Calculation Month</th>
                        <th>Previous Reading</th>
                        <th>Current Reading</th>
                        <th>Total Consumption (gallons)</th>
                        <th>Electicity Rate</th>
                        <th>Total Bill</th>
                        <th>Meter Read Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($calculation_row = $calculation_result->fetch_assoc()): ?>
                        <tr>
                        <td><?php echo htmlspecialchars($calculation_row['calculation_month']); ?></td>
                            <td><?php echo htmlspecialchars($calculation_row['previous_reading']); ?></td>
                            <td><?php echo htmlspecialchars($calculation_row['current_reading']); ?></td>
                            <td><?php echo htmlspecialchars($calculation_row['current_reading'] - $calculation_row['previous_reading']); ?></td>
                            <td>PHP <?php echo number_format($calculation_row['electricity_rate'], 2); ?></td>
                            <td>PHP <?php echo number_format(($calculation_row['current_reading'] - $calculation_row['previous_reading']) * $calculation_row['electricity_rate'], 2); ?></td>
                            <td><?php echo htmlspecialchars($calculation_row['meter_read_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No electricity bill calculations available for Unit <?php echo htmlspecialchars($unit_number); ?>.</p>
        <?php endif; ?> 

        <h3>Payment History</h3>
        <?php if ($result && $result->num_rows > 0): ?>
            <table class="payment-history-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date Time Added</th>
                        <th>Month Of</th>
                        <th>Amount Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($row['payment_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['month_of']); ?></td>
                            <td>PHP <?php echo number_format($row['amount_paid'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No payment history available for Unit <?php echo htmlspecialchars($unit_number); ?>.</p>
        <?php endif; ?> 
    </div>

    <!-- Modal for Calculating Electricity Bill -->
    <div class="compute_modal" id="computeModal">
    <div class="modal-content">
        <h3>Calculate Electicity for Room <?php echo htmlspecialchars($unit_number); ?></h3>
        <form method="POST" action="compute_electricity.php">
    <label>Unit Number:</label>
    <input type="text" name="unit_number" value="<?php echo htmlspecialchars($unit_number); ?>" readonly required><br>

    <label>Previous Reading:</label>
    <input 
        type="number" 
        name="previous_reading" 
        value="<?php echo htmlspecialchars($previous_reading); ?>" 
        <?php echo $is_first_time ? '' : 'readonly'; ?> 
        step="0.01" 
        required
    ><br>

    <label>Current Reading:</label>
    <input type="number" name="current_reading" step="0.01" required><br>

    <label>Electicity Rate (PHP per gallon):</label>
    <input type="number" name="electricity_rate" step="0.01" required><br>

    <label>Meter Read Date:</label>
    <input type="date" name="meter_read_date" required><br>

    <label>Calculation Month:</label>
    <input type="month" name="calculation_month" required><br>

    <button type="submit" class="green-button">Calculate</button>
</form>

        <button class="back-button" onclick="closeModal('computeModal')">Back to Electicity</button>
    </div>
</div>
    <!-- Modal for Updating Payment -->
    <div class="payment_modal" id="updateModal">
        <div class="modal-content">
            <h3>Update Electicity Payment for <?php echo htmlspecialchars($unit_number); ?></h3>
            <form method="POST" action="update_payment_electricity.php?unit_number=<?php echo urlencode($unit_number); ?>">
                <label for="month">Month of:</label>
                <input type="month" name="month" required><br>

                <label>Amount:</label>
                <input type="text" name="amount_paid" required><br>

                <label>Payment Date:</label>
                <input type="date" name="payment_date" required><br>

                <button type="submit" class="green-button">Submit Payment</button>
            </form>
            <button class="back-button" onclick="closeModal('updateModal')">Back to Electicity</button>
        </div>
    </div>

    <script>
        // Open modal function
        function openModal(modalId) {
            document.getElementById(modalId).style.display = "flex";
        }

        // Close modal function
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }
    </script>

</body>
</html>

<?php
// Close the connection
$conn->close();
?>
