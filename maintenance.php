<?php
// ADMIN MAINTENANCE PAGE
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php for $pdo connection

// Fetch reports data along with tenant information
$stmt = $pdo->query("
    SELECT r.id, r.report_details, r.image_path, r.created_at, 
           t.unit_number, t.first_name, t.last_name 
    FROM reports r
    JOIN tenant_account t ON r.tenant_id = t.id
    ORDER BY r.created_at DESC
");
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="JRSLCSS/maintenance.css"> <!-- Custom CSS for Maintenance -->
</head>
<body>

    <!-- Sidebar navigation -->
    <?php include 'sidebar.php'; ?>

    <!-- Page content -->
    <div class="main-content">
        <h2>Maintenance Reports</h2>

        <!-- Filter Dropdown -->
        <div class="filter-container">
            <label for="statusFilter">Filter by Date:</label>
            <select id="dateFilter" onchange="filterReports()">
                <option value="all">All Dates</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>
        </div>

        <!-- Reports List -->
        <div class="report-grid" id="reportGrid">
            <?php if (count($reports) > 0): ?>
                <?php foreach ($reports as $report): ?>
                    <div class="report-card" 
                         data-created-at="<?php echo htmlspecialchars($report['created_at']); ?>">
                        <h3>Room #<?php echo htmlspecialchars($report['unit_number']); ?></h3>
                        <p><strong>Tenant:</strong> <?php echo htmlspecialchars($report['first_name'] . ' ' . $report['last_name']); ?></p>
                        <p><strong>Details:</strong> <?php echo htmlspecialchars($report['report_details']); ?></p>
                        <p><strong>Submitted On:</strong> <?php echo htmlspecialchars($report['created_at']); ?></p>
                        <div class="image-container">
                            <img src="<?php echo htmlspecialchars($report['image_path']); ?>" alt="Report Image">
                        </div>
                        <div class="report-actions">
                            <button class="button resolve-button" onclick="markResolved(<?php echo $report['id']; ?>)">Mark as Resolved</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No reports found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterReports() {
            var dateFilter = document.getElementById('dateFilter').value;
            var reports = document.getElementsByClassName('report-card');
            var today = new Date();
            
            for (var i = 0; i < reports.length; i++) {
                var createdAt = new Date(reports[i].getAttribute('data-created-at'));
                var showReport = true;

                if (dateFilter === 'today') {
                    showReport = createdAt.toDateString() === today.toDateString();
                } else if (dateFilter === 'week') {
                    var weekStart = new Date(today.setDate(today.getDate() - today.getDay()));
                    var weekEnd = new Date(weekStart);
                    weekEnd.setDate(weekStart.getDate() + 6);
                    showReport = createdAt >= weekStart && createdAt <= weekEnd;
                } else if (dateFilter === 'month') {
                    showReport = createdAt.getMonth() === today.getMonth() && createdAt.getFullYear() === today.getFullYear();
                }

                reports[i].style.display = showReport ? 'block' : 'none';
            }
        }

        function markResolved(id) {
            if (confirm("Are you sure you want to mark this report as resolved?")) {
                window.location.href = `resolve_report.php?id=${id}`;
            }
        }
    </script>
</body>
</html>
