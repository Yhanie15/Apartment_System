<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'apartment_management');

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize filters
$month_filter = isset($_GET['month']) ? $_GET['month'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';


// Build the query with filters
$sql = "SELECT unit_number, water_rate, water_consumption, previous_reading, current_reading, 
        meter_read_date, water_bill, calculation_month, due_date, last_payment_date, current_status 
        FROM water_calculations WHERE 1=1";

if (!empty($month_filter)) {
    $sql .= " AND calculation_month = '" . $conn->real_escape_string($month_filter) . "'";
}

if (!empty($status_filter)) {
    $sql .= " AND current_status = '" . $conn->real_escape_string($status_filter) . "'";
}

$sql .= " ORDER BY due_date DESC";
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

    <style>
        .filter-form {
            display: flex;
            align-items: center;
        }
        .filter-form label {
            font-size: 18px;
            margin-right: 10px;
            color: #333;
        }
        .filter-form select {
            padding: 10px;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
            border: 2px solid #ccc;
            transition: border-color 0.3s ease;
        }
        .filter-form select:focus {
            border-color: #007bff;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <h1>Water Bill Payment Report</h1>

        <!-- Filters -->
        <form method="GET" class="filter-form">
            <label for="month">Filter by Month:</label>
            <select name="month" id="month">
                <option value="">--Select Month--</option>
                <option value="January" <?php echo $month_filter == 'January' ? 'selected' : ''; ?>>January</option>
                <option value="February" <?php echo $month_filter == 'February' ? 'selected' : ''; ?>>February</option>
                <option value="March" <?php echo $month_filter == 'March' ? 'selected' : ''; ?>>March</option>
                <option value="April" <?php echo $month_filter == 'April' ? 'selected' : ''; ?>>April</option>
                <option value="May" <?php echo $month_filter == 'May' ? 'selected' : ''; ?>>May</option>
                <option value="June" <?php echo $month_filter == 'June' ? 'selected' : ''; ?>>June</option>
                <option value="July" <?php echo $month_filter == 'July' ? 'selected' : ''; ?>>July</option>
                <option value="August" <?php echo $month_filter == 'August' ? 'selected' : ''; ?>>August</option>
                <option value="September" <?php echo $month_filter == 'September' ? 'selected' : ''; ?>>September</option>
                <option value="October" <?php echo $month_filter == 'October' ? 'selected' : ''; ?>>October</option>
                <option value="November" <?php echo $month_filter == 'November' ? 'selected' : ''; ?>>November</option>
                <option value="December" <?php echo $month_filter == 'December' ? 'selected' : ''; ?>>December</option>
                <!-- Add other months as needed -->
            </select>

            <label for="status">Filter by Status:</label>
            <select name="status" id="status">
                <option value="">--Select Status--</option>
                <option value="Paid" <?php echo $status_filter == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                <option value="Unpaid" <?php echo $status_filter == 'Unpaid' ? 'selected' : ''; ?>>Unpaid</option>
            </select>

            <a href="print.php?month=<?php echo urlencode($month_filter); ?>&status=<?php echo urlencode($status_filter); ?>" target="_blank" class="print-btn">Print</a>
        </form>


        <!-- Table to display water bill payment details -->
        <table>
            <thead>
                <tr>
                    <th>Unit Number</th>
                    <th>Calculation Month</th>
                    <th>Meter Read Date</th>
                    <th>Water Bill</th>
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
                        <td>PHP <?php echo htmlspecialchars(number_format($row['water_bill'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['current_status']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11">No water bill payment records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Automatically submit the form when filters are changed
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.filter-form');
            form.addEventListener('change', () => form.submit());
        });
    </script>

</body>
</html>

<?php
// Close the connection
$conn->close();
?>
