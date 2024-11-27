<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="JRSLCSS/reports.css"> <!-- Link to new CSS for reports -->
    <link rel="stylesheet" href="styles.css">

</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Reports Content -->
    <div class="main-content">
        
        <!-- Reports Section -->
        <div class="report-grid">
            <div class="report-card" onclick="window.location.href='rental_payment_report.php'">
                <span>Rental Payment Report</span>
            </div>
            <div class="report-card" onclick="window.location.href='water_bill_report.php'">
            <span>Water Bill Payment Report</span>
            </div>
            <div class="report-card" onclick="window.location.href='electricity_bill_report.php'">
                <span>Electricity Bill Payment Report</span>
            </div>
            <div class="report-card" onclick="window.location.href='balances_report.php'">
                <span>Balances</span>
            </div>
        </div>
    </div>
</body>
</html>
