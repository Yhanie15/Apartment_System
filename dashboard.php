<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

try {
    // Query to get total number of tenants
    $stmt = $pdo->query("SELECT COUNT(*) AS total_tenants FROM tenant_account");
    $total_tenants = $stmt->fetch(PDO::FETCH_ASSOC)['total_tenants'];

    // Query to get total number of rooms
    $stmt = $pdo->query("SELECT COUNT(*) AS total_rooms FROM rooms");
    $total_rooms = $stmt->fetch(PDO::FETCH_ASSOC)['total_rooms']; // Should be 13 as per your data

    // Query to get room occupancy status
    $stmt = $pdo->query("SELECT r.room_type, COUNT(t.id) AS tenant_count 
                          FROM rooms r
                          LEFT JOIN tenant_account t ON r.unit_number = t.unit_number
                          GROUP BY r.room_type");
    $room_occupancy = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate the number of occupied rooms
    $occupied = 0;
    foreach ($room_occupancy as $room) {
        if ($room['tenant_count'] > 0) {
            $occupied++;
        }
    }

    // Calculate the number of vacant rooms
    $vacant = $total_rooms - $occupied; // Total rooms minus the occupied rooms

    // Query to calculate the total revenue from rent payments, water payments, and electricity payments
    $stmt = $pdo->query("
        SELECT SUM(amount_paid) AS total_revenue
        FROM (
            SELECT amount_paid FROM rent_payments
            UNION ALL
            SELECT amount_paid FROM water_payment_history
            UNION ALL
            SELECT amount_paid FROM electricity_payment_history
        ) AS combined_payments
    ");
    $total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'];

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="JRSLCSS/dashboard.css">
    <link rel="stylesheet" href="styles.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1>Welcome to Admin Dashboard</h1>
            <div class="header-icons">
                <i class="fas fa-bell"></i>
                <div class="dropdown">
                    <i class="fas fa-user-circle" onclick="toggleDropdown()"></i>
                    <div id="profileDropdown" class="dropdown-content">
                        <a href="change_password.php">View Profile</a>
                        <a href="view_profile.php">Settings</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <i class="fas fa-users"></i>
                <h3>Tenants</h3>
                <span><?php echo $total_tenants; ?></span>
            </div>
            <div class="card">
                <i class="fas fa-home"></i>
                <h3>Rooms</h3>
                <span><?php echo $total_rooms; ?></span>
            </div>
            <div class="card">
                <i class="fas fa-dollar-sign"></i>
                <h3>Income</h3>
                <p><?php echo number_format($total_revenue, 2); ?> PHP</p>
            </div>
        </div>

        <div class="content-row">
            <div class="payment-table">
                <h3>Payment to be collected:</h3>
                <table>
                    <tr>
                        <th>Room</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <td>Room 2022</td>
                        <td>5,000</td>
                        <td><button class="notify-btn">Notify</button></td>
                    </tr>
                    <tr>
                        <td>Room 2028</td>
                        <td>5,000</td>
                        <td><button class="notify-btn">Notify</button></td>
                    </tr>
                    <tr>
                        <td>Room 2042</td>
                        <td>5,000</td>
                        <td><button class="notify-btn">Notify</button></td>
                    </tr>
                    <tr>
                        <td>Room 2022</td>
                        <td>5,000</td>
                        <td><button class="notify-btn">Notify</button></td>
                    </tr>
                </table>
            </div>

            <div class="chart-container">
                <h3>Room Occupancy Status</h3>
                <canvas id="roomOccupancyChart"></canvas>
            </div>
        </div>

        <div class="content-row">
            <div class="chart-container">
                <h3>Income Revenue</h3>
                <canvas id="incomeRevenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script>
        // Room Occupancy Pie Chart
        const roomOccupancyCtx = document.getElementById('roomOccupancyChart').getContext('2d');
        const roomOccupancyChart = new Chart(roomOccupancyCtx, {
            type: 'pie',
            data: {
                labels: ['Occupied', 'Vacant'],
                datasets: [{
                    data: [<?php echo $occupied; ?>, <?php echo $vacant; ?>], // Use PHP variables for dynamic data
                    backgroundColor: ['#36A2EB', '#FF6384'],
                }]
            },
            options: {
                responsive: true
            }
        });

        // Income Revenue Bar Chart
        const incomeRevenueCtx = document.getElementById('incomeRevenueChart').getContext('2d');
        const incomeRevenueChart = new Chart(incomeRevenueCtx, {
            type: 'bar',
            data: {
                labels: ['June', 'July', 'August', 'September', 'October'],
                datasets: [{
                    label: '2023',
                    data: [5, 10, 15, 20, 25], // Example data for 2023
                    backgroundColor: '#36A2EB'
                }, {
                    label: '2024',
                    data: [10, 15, 20, 25, 30], // Example data for 2024
                    backgroundColor: '#FF6384'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        function toggleDropdown() {
            var dropdown = document.getElementById("profileDropdown");
            if (dropdown.style.display === "block") {
                dropdown.style.display = "none";
            } else {
                dropdown.style.display = "block";
            }
        }

        // Hide the dropdown if clicked outside
        window.onclick = function(event) {
            if (!event.target.matches('.fas.fa-user-circle')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.style.display === "block") {
                        openDropdown.style.display = "none";
                    }
                }
            }
        }
    </script>
</body>
</html>
