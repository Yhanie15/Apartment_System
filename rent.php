<?php
session_start();

// Include the database connection
include 'db.php'; 

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

// Function to get the total amount paid and the status of rent payment
function getRentStatus($pdo, $unit_number, $total_rent_due) {
    try {
        $stmt = $pdo->prepare("
            SELECT SUM(amount_paid) AS total_paid 
            FROM rent_payments 
            WHERE unit_number = ?
        ");
        $stmt->execute([$unit_number]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        $total_paid = $payment ? $payment['total_paid'] : 0;
        $balance = max(0, $total_rent_due - $total_paid);

        return [
            'status' => $balance <= 0 ? 'Paid' : ($total_paid > 0 ? 'Partial Payment' : 'Unpaid'),
            'total_paid' => $total_paid,
            'balance' => $balance,
        ];
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Function to get the last payment date
function getLastPaymentDate($pdo, $unit_number) {
    try {
        $stmt = $pdo->prepare("
            SELECT MAX(payment_date) AS last_payment_date 
            FROM rent_payments 
            WHERE unit_number = ?
        ");
        $stmt->execute([$unit_number]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        return $payment && $payment['last_payment_date'] ? $payment['last_payment_date'] : 'N/A';
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

try {
    // Fetch rooms and tenant information
    $stmt = $pdo->query("
        SELECT 
            rooms.id, 
            rooms.unit_number, 
            rooms.rent AS rent_per_month,
            MIN(tenant_account.move_in_date) AS move_in_date
        FROM rooms
        LEFT JOIN tenant_account ON rooms.unit_number = tenant_account.unit_number
        GROUP BY rooms.id, rooms.unit_number, rooms.rent
    ");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $currentDate = date('Y-m-d');
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <style>
        /* Your CSS */
        .search-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .search-bar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
        .search-bar input {
            padding: 10px;
            width: 300px;
            border: 2px solid #ccc;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s ease;
        }
        .search-bar input:focus {
            border-color: #007bff;
        }
        .filter-container {
            display: flex;
            align-items: center;
        }
        .filter-container label {
            font-size: 18px;
            margin-right: 10px;
            color: #333;
        }
        .filter-container select {
            padding: 10px;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
            border: 2px solid #ccc;
            transition: border-color 0.3s ease;
        }
        .filter-container select:focus {
            border-color: #007bff;
        }
        .button.add-button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .button.add-button:hover {
            background-color: #218838;
        }
        .button.view-button {
            background-color: #007bff; /* Blue background */
            color: white; /* White text */
            padding: 10px 20px; /* Padding for the button */
            border: none; /* Remove default borders */
            border-radius: 25px; /* Rounded corners */
            text-align: center; /* Center the text */
            text-decoration: none; /* Remove underline from the link */
            cursor: pointer; /* Pointer cursor on hover */
            transition: background-color 0.3s ease; /* Smooth hover effect */
        }
        .button.view-button:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
    </style>
</head>
<body>

    <!-- Sidebar navigation -->
    <?php include 'sidebar.php'; ?>

    <!-- Page content -->
    <div class="main-content">
        <h2>Rent Page</h2>

        <!-- Search and Filter Bar -->
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

        <!-- Room list table -->
<table id="roomsTable">
    <thead>
        <tr>
            <th>Unit Number</th>
            <th>Monthly Rent</th>
          
            <th>Last Payment</th>
            <th>Status</th>
            <th>Balance</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rooms as $room): 
            $move_in_date = $room['move_in_date'];
            $rent_per_month = $room['rent_per_month'];
            
            if (empty($move_in_date)) {
                $status = 'N/A';
             
                $last_payment_date = 'N/A';
                $balance = 0;
            } else {
                $move_in_date = new DateTime($move_in_date);
                $due_date = date('Y-m-d', strtotime($move_in_date->format('Y-m-d') . ' +1 month'));
                
                if ($currentDate < $due_date) {
                    // New tenant, balance is the first month's rent
                    $total_rent_due = $rent_per_month;
                } else {
                    // Calculate months stayed
                    $current_date = new DateTime($currentDate);
                    $months_stayed = $move_in_date->diff($current_date)->m + ($move_in_date->diff($current_date)->y * 12);
                    $total_rent_due = $months_stayed * $rent_per_month;
                }

                $rent_status = getRentStatus($pdo, $room['unit_number'], $total_rent_due);
                $status = $rent_status['status'];
                $balance = $rent_status['balance'];
                $last_payment_date = getLastPaymentDate($pdo, $room['unit_number']);
            }
        ?>
        <tr>
            <td><?php echo htmlspecialchars($room['unit_number']); ?></td>
            <td>PHP <?php echo htmlspecialchars($rent_per_month); ?></td>

            <td><?php echo htmlspecialchars($last_payment_date); ?></td>
            <td><?php echo htmlspecialchars($status); ?></td>
            <td>PHP <?php echo number_format($balance, 2); ?></td>
            <td><a href="rent_details.php?unit_number=<?php echo $room['unit_number']; ?>" class="button view-button">View</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    // Function to search the table by unit number
    function searchTable() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("roomsTable");
        tr = table.getElementsByTagName("tr");

        for (i = 0; i < tr.length; i++) {
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
    // Function to filter the table by rent status
function filterByStatus() {
    var filter, table, tr, td, i, txtValue;
    filter = document.getElementById("statusFilter").value;
    table = document.getElementById("roomsTable");
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
