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
$stmt = $pdo->query("SELECT r.id, r.report_details, r.image_path, r.created_at, t.unit_number, t.first_name, t.last_name 
                     FROM reports r 
                     JOIN tenant_account t ON r.tenant_id = t.id 
                     WHERE r.archived = FALSE 
                     ORDER BY r.created_at DESC");
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
    <style>
        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .report-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }

        .report-card:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .report-card h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #333;
        }

        .report-card p {
            font-size: 16px;
            margin-bottom: 8px;
            color: #555;
        }

        .image-container {
            margin-top: 10px;
            text-align: center;
        }

        .image-container img {
            width: 2in; 
            height: 2in; 
            object-fit: cover; 
            border-radius: 4px; 
            border: 1px solid #ddd;
            cursor: pointer;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .overlay img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 8px;
        }

        .overlay .close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #fff;
            color: #000;
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            font-weight: bold;
        }
    </style>
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
                            <img src="<?php echo htmlspecialchars($report['image_path']); ?>" alt="Report Image" onclick="showOverlay('<?php echo htmlspecialchars($report['image_path']); ?>')">
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

    <div class="overlay" id="imageOverlay">
        <span class="close-btn" onclick="closeOverlay()">&times;</span>
        <img id="overlayImage" src="" alt="Full View">
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

        function showOverlay(imagePath) {
            var overlay = document.getElementById('imageOverlay');
            var overlayImage = document.getElementById('overlayImage');
            overlayImage.src = imagePath;
            overlay.style.display = 'flex';
        }

        function closeOverlay() {
            var overlay = document.getElementById('imageOverlay');
            overlay.style.display = 'none';
        }
    </script>
</body>
</html>
