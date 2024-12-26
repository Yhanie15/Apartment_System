<?php
// LIST OF ROOMS
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

// Fetch rooms data along with tenant information (to detect if room is occupied)
$stmt = $pdo->query("SELECT r.id, r.unit_number, r.rent, r.capacity, r.room_type, COUNT(t.id) AS tenant_count FROM rooms r LEFT JOIN tenant_account t ON r.unit_number = t.unit_number GROUP BY r.id ORDER BY r.unit_number ASC");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Rooms</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="JRSLCSS/view_rooms.css">
</head>
<body>

    <!-- Sidebar navigation -->
    <?php include 'sidebar.php'; ?>

    <!-- Page content -->
    <div class="main-content">
        <h2>View Rooms</h2>

        <!-- Add Room button -->
        <a href="add_room.php" class="button add-button">+ Add Room</a>

        <!-- Filter Dropdowns -->
        <div class="filter-container">
            <label for="roomFilter">Filter by Room Type:</label>
            <select id="roomFilter" onchange="filterRooms()">
                <option value="all">All Rooms</option>
                <option value="solo">Solo Room</option>
                <option value="small">Small Room</option>
                <option value="medium">Medium Room</option>
                <option value="large">Large Room</option>
            </select>

            <label for="statusFilter">Filter by Status:</label>
            <select id="statusFilter" onchange="filterRooms()">
                <option value="all">All Status</option>
                <option value="available">Available</option>
                <option value="occupied">Occupied</option>
            </select>
        </div>

        <!-- Room list in grid display -->
        <div class="room-grid" id="roomGrid">
            <?php foreach ($rooms as $room): ?>
                <?php 
                    // Determine room status and availability
                    $availableSlots = $room['capacity'] - $room['tenant_count'];
                    if ($availableSlots > 0) {
                        $status = 'available';
                        $availabilityText = "Available ($availableSlots slot" . ($availableSlots > 1 ? 's' : '') . ")";
                    } else {
                        $status = 'occupied';
                        $availabilityText = "Occupied";
                    }
                ?>
                <div class="room-card" 
                     data-room-type="<?php echo htmlspecialchars($room['room_type']); ?>"
                     data-status="<?php echo $status; ?>">
                    <h3><?php echo htmlspecialchars($room['unit_number']); ?></h3>
                    <p><i class="fas fa-dollar-sign"></i> <?php echo htmlspecialchars($room['rent']); ?></p>
                    <p><i class="fas fa-users"></i> Capacity: <?php echo htmlspecialchars($room['capacity']); ?></p>
                    <p><i class="fas fa-door-open"></i> Type: <?php echo htmlspecialchars($room['room_type']); ?></p>
                    <p><i class="fas fa-info-circle"></i> Status: <?php echo htmlspecialchars($availabilityText); ?></p> <!-- Display room status and availability with icon -->
                    <div class="room-actions">
                        <a href="view_room.php?id=<?php echo $room['id']; ?>" class="button view-button">View</a>
                        <button class="button delete-button" onclick="confirmDelete(<?php echo $room['id']; ?>)">Delete</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <script>
        function filterRooms() {
            var roomFilter = document.getElementById('roomFilter').value;
            var statusFilter = document.getElementById('statusFilter').value;
            var rooms = document.getElementsByClassName('room-card');

            for (var i = 0; i < rooms.length; i++) {
                var roomType = rooms[i].getAttribute('data-room-type').toLowerCase();
                var status = rooms[i].getAttribute('data-status').toLowerCase();
                
                var showRoom = true;

                // Filter by Room Type
                if (roomFilter !== 'all' && !roomType.includes(roomFilter)) {
                    showRoom = false;
                }

                // Filter by Room Status (Occupied / Available)
                if (statusFilter !== 'all' && status !== statusFilter) {
                    showRoom = false;
                }

                // Show or hide the room based on the filters
                if (showRoom) {
                    rooms[i].style.display = 'block';
                } else {
                    rooms[i].style.display = 'none';
                }
            }
        }

        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this room?")) {
                window.location.href = `delete_room.php?id=${id}`;
            }
        }
    </script>
</body>
</html>
