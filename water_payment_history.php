<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'apartment_management');

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if a specific unit number is passed
if (isset($_GET['unit_number'])) {
    $unit_number = $_GET['unit_number'];

    // Fetch payment history for the unit
    $sql = "SELECT * FROM water_payment_history WHERE unit_number = '$unit_number' ORDER BY payment_date DESC";
    $result = $conn->query($sql);

    // Fetch water calculations for the unit (remaining unpaid months)
    // Exclude already paid months
    $calculation_sql = "
        SELECT * FROM water_calculations 
        WHERE unit_number = '$unit_number' 
        AND calculation_month NOT IN (
            SELECT month_of FROM water_payment_history WHERE unit_number = '$unit_number'
        )
        ORDER BY calculation_month DESC
    ";
    $calculation_result = $conn->query($calculation_sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Payment History</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="JRSLCSS/water_result.css"> 
    <link rel="stylesheet" href="JRSLCSS/bills_payment.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
    <div class="main-content">
        <a href="water_payment.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Water Payment Page</a>
        <h2>Payment History for Unit <?php echo htmlspecialchars($unit_number); ?></h2>

        <div class="search-section">
            <input type="text" placeholder="Quick Search" id="searchRentDetails">
            <button onclick="openModal('computeModal')" class="back-button">+ Compute Bills</button>
            <button onclick="openModal('updateModal')" class="back-button">+ Add Payment</button>
        </div>

        <h3>Remaining Water Bill Calculations</h3>
        <?php if ($calculation_result && $calculation_result->num_rows > 0): ?>
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>Calculation Month</th>
                        <th>Water Rate (PHP per gallon)</th>
                        <th>Water Consumption (gallons)</th>
                        <th>Water Bill (PHP)</th>
                        <th>Meter Read Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($calculation_row = $calculation_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($calculation_row['calculation_month']); ?></td>
                            <td>PHP <?php echo number_format($calculation_row['water_rate'], 2); ?></td>
                            <td><?php echo htmlspecialchars($calculation_row['water_consumption']); ?></td>
                            <td>PHP <?php echo number_format($calculation_row['water_bill'], 2); ?></td>
                            <td><?php echo htmlspecialchars($calculation_row['meter_read_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No water bill calculations available for Unit <?php echo htmlspecialchars($unit_number); ?>.</p>
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

    <!-- Modal for Calculating Water Bill -->
<div class="compute_modal" id="computeModal">
    <div class="modal-content">
        <h3>Calculate Water for Room <?php echo htmlspecialchars($unit_number); ?></h3>
        <form method="POST" action="compute_water.php">
            <label>Unit Number:</label>
            <input type="text" name="unit_number" value="<?php echo htmlspecialchars($unit_number); ?>" required><br>

            <label>Water Rate (PHP per gallon):</label>
            <input type="text" name="water_rate" required><br>

            <label>Water Consumption (gallons):</label>
            <input type="text" name="water_consumption" required><br>

            <label>Meter Read Date:</label>
            <input type="date" name="meter_read_date" required><br>

            <!-- Add month picker for Calculation Month -->
            <label>Calculation Month:</label>
            <input type="month" name="calculation_month" required><br>

            <button type="submit" class="green-button">Calculate</button>
        </form>
        <button class="back-button" onclick="closeModal('computeModal')">Back to Water</button>
    </div>
</div>

    <!-- Modal for Updating Payment -->
    <div class="payment_modal" id="updateModal">
        <div class="modal-content">
            <h3>Update Water Payment for <?php echo htmlspecialchars($unit_number); ?></h3>
            <form method="POST" action="update_payment.php?unit_number=<?php echo urlencode($unit_number); ?>">
                <label for="month">Month of:</label>
                <input type="month" name="month" required><br>

                <label>Amount:</label>
                <input type="text" name="amount_paid" required><br>

                <label>Payment Date:</label>
                <input type="date" name="payment_date" required><br>

                <button type="submit" class="green-button">Submit Payment</button>
            </form>
            <button class="back-button" onclick="closeModal('updateModal')">Back to Water</button>
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
