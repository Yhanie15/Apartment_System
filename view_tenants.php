<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
$stmt = $pdo->query("SELECT unit_number, last_name, first_name, middle_name FROM tenant_account");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Tenants</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="JRSLCSS/view_tenants.css"> <!-- Make sure styles.css is updated to include nav styles -->

    <!-- Cool Search Bar Styling -->
    <style>
        .button-container {
            display: flex;
            justify-content: space-between; /* Aligns buttons to the ends */
            align-items: center; /* Aligns vertically */
            margin-bottom: 20px; /* Space between buttons and table */
        }

        .search-bar {
            display: flex;
            align-items: center; /* Ensures the label and input are aligned vertically */
            margin-left: 20px; /* Add some space to the left of the search bar */
            margin-top: 20px; /* Add space above the search bar to lower it */
        }

        .search-bar label {
            font-size: 18px;
            color: #333;
            margin-right: 10px; /* Reduced space between label and input */
        }

        .search-bar input {
            padding: 10px;
            width: 250px; /* Adjust width if needed */
            border: 2px solid #ccc;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s ease;
            margin-bottom: 0; /* Ensure there's no margin at the bottom */
        }

        .search-bar input:focus {
            border-color: #007bff;
        }

        /* Cool Button Style */
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

        /* Table Styles */
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
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <h2>View Tenants</h2>

        <div class="search-container">
    <div class="search-bar">
        <label for="tenantSearch">Search:</label>
        <input type="text" id="tenantSearch" onkeyup="searchTenants()" placeholder="Search by last name, first name, or unit number">
    </div>
</div>


        <!-- Tenant list table -->
        <table id="tenantTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Room Number</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $index => $user): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($user['unit_number']); ?></td>
                        <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['middle_name']); ?></td>
                        <td>
                            <a href="view_tenant.php?unit_number=<?php echo $user['unit_number']; ?>" class="button">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function searchTenants() {
    var input = document.getElementById('tenantSearch').value.toLowerCase();
    var table = document.getElementById('tenantTable');
    var rows = table.getElementsByTagName('tr');

    for (var i = 1; i < rows.length; i++) { // Skip the header row
        var unitNumber = rows[i].getElementsByTagName('td')[1]?.textContent.toLowerCase() || '';
        var lastName = rows[i].getElementsByTagName('td')[2]?.textContent.toLowerCase() || '';
        var firstName = rows[i].getElementsByTagName('td')[3]?.textContent.toLowerCase() || '';
        var middleName = rows[i].getElementsByTagName('td')[4]?.textContent.toLowerCase() || '';

        // Show row if any column contains the input value
        if (
            unitNumber.includes(input) || 
            lastName.includes(input) || 
            firstName.includes(input) || 
            middleName.includes(input)
        ) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}

    </script>

</body>
</html>