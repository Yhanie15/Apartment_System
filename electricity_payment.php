<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'apartment_management');

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all unit numbers from the 'rooms' table
$sql = "SELECT unit_number FROM rooms";
$units_result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electicity Payment</title>
    <link rel="stylesheet" href="JRSLCSS/water_payment.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="header">
        <h2>Electicity Payment</h2>
        <div class="search-container">
            <div class="search-bar">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for unit numbers..">
            </div>
            <div class="filter-container">
                <label for="statusFilter">Filter by Status:</label>
                <select id="statusFilter" onchange="filterByStatus()">
                    <option value="All">All</option>
                    <option value="Paid">Paid</option>
                    <option value="Unpaid">Unpaid</option>
                    <option value="Partial Payment">Partial Payment</option>
                </select>
            </div>
        </div>


    <table class="payment-table", id="payment-table">
        <thead>
            <tr>
                <th>Unit Number</th>
                <th>Balance</th>
                <th>Due Date</th>
                <th>Current Status</th>
                <th>Last Payment Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($units_result && $units_result->num_rows > 0) {
                while ($unit = $units_result->fetch_assoc()) {
                    $unit_number = $unit['unit_number'];

                    // Get the latest electricity calculation details
                    $payment_sql = "
                        SELECT * 
                        FROM electricity_calculations 
                        WHERE unit_number = '$unit_number' 
                        ORDER BY calculation_month DESC 
                        LIMIT 1";
                    $payment_result = $conn->query($payment_sql);

                    if ($payment_result && $payment_result->num_rows > 0) {
                        $payment = $payment_result->fetch_assoc();
                        $monthly_electricity_bill = number_format($payment['electricity_bill'], 2);
                        $due_date = $payment['due_date'];
                        $current_status = $payment['current_status'];
                        $last_payment_date = $payment['last_payment_date'] ? $payment['last_payment_date'] : 'N/A';
                    } else {
                        $monthly_electricity_bill = 'N/A';
                        $due_date = 'N/A';
                        $current_status = 'Unpaid';
                        $last_payment_date = 'N/A';
                    }

                    // Calculate balance for unpaid bills
                    $balance_sql = "
                        SELECT SUM(electricity_bill) AS balance 
                        FROM electricity_calculations 
                        WHERE unit_number = '$unit_number' 
                        AND current_status = 'Unpaid'";
                    $balance_result = $conn->query($balance_sql);
                    $balance_row = $balance_result->fetch_assoc();
                    $balance = $balance_row['balance'] ? number_format($balance_row['balance'], 2) : 'N/A';

                    // Override current status to "Unpaid" if there's a balance
                    if ($balance_row['balance'] > 0) {
                        $current_status = 'Unpaid';
                    }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($unit_number); ?></td>
                        <td>PHP <?php echo htmlspecialchars($balance); ?></td>
                        <td><?php echo htmlspecialchars($due_date); ?></td>
                        <td><?php echo htmlspecialchars($current_status); ?></td>
                        <td><?php echo htmlspecialchars($last_payment_date); ?></td>
                        <td>
                            <a href="electricity_payment_history.php?unit_number=<?php echo urlencode($unit_number); ?>" class="button"><p>View Payment History</p></a>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                echo "<tr><td colspan='6'>No units available.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<script>
    // Function to search the table by unit number
    function searchTable() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("payment-table"); // Corrected table ID
        tr = table.getElementsByTagName("tr");

        for (i = 1; i < tr.length; i++) { // Start from 1 to skip header row
            td = tr[i].getElementsByTagName("td")[0]; // Search by unit number (first column)
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }

    // Function to filter the table by status
    function filterByStatus() {
        var filter, table, tr, td, i, txtValue;
        filter = document.getElementById("statusFilter").value;
        table = document.getElementById("payment-table");
        tr = table.getElementsByTagName("tr");

        for (i = 1; i < tr.length; i++) { // Start from 1 to skip header row
            td = tr[i].getElementsByTagName("td")[3]; // Get the status column (4th column, index 3)
            if (td) {
                txtValue = td.textContent || td.innerText;
                // Check if the status matches the filter
                if (filter === "All" || txtValue.trim() === filter) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
</script>


</body>
</html>

<?php
// Close the connection
$conn->close();
?>
