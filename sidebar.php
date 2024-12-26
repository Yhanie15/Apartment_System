<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Make sure styles.css is updated to include nav styles -->
    <link rel="stylesheet" href="JRSLCSS/sidebar.css">
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button id="sidebar-toggle"><i class="fas fa-bars"></i></button>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <!-- Logo or Header Image -->
            <img src="images/logo.png" alt="Description of the image" style="width:90%; height:auto;">
        </div>
        <ul>
            <!-- Sidebar Menu Items with Icons -->
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="view_tenants.php"><i class="fas fa-users"></i> View Tenants</a></li>
            <li><a href="view_rooms.php"><i class="fas fa-door-open"></i> View Rooms</a></li>

            <!-- Dropdown Menu for Bills & Payments -->
            <li class="dropdown">
                <a href="#"><i class="fas fa-file-invoice-dollar"></i> Bills & Payment</a>
                <ul class="dropdown-content">
                    <li><a href="rent.php"><i class="fas fa-money-bill"></i> Rent</a></li>
                    <li><a href="water_payment.php"><i class="fas fa-tint"></i> Water</a></li>
                    <li><a href="electricity_payment.php"><i class="fas fa-bolt"></i> Electricity</a></li>
                </ul>
            </li>
            
            <li><a href="maintenance.php"><i class="fas fa-screwdriver"></i> Maintenance</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
            <li><a href="web/home.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <script>
    // Toggle sidebar visibility
    document.getElementById('sidebar-toggle').addEventListener('click', function() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('open');
    });

    // Handle dropdown toggle
    document.querySelectorAll('.sidebar .dropdown > a').forEach(function(item) {
        item.addEventListener('click', function(e) {
            let nextEl = item.nextElementSibling;
            if (nextEl && nextEl.classList.contains('dropdown-content')) {
                e.preventDefault();
                nextEl.style.display = nextEl.style.display === 'block' ? 'none' : 'block';
            }
        });
    });
    </script>
</body>
</html>