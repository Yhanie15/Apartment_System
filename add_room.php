<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

// Initialize an error message variable
$error_message = "";

// Handle form submission to add a new room
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rent = $_POST['rent'];
    $unit_number = $_POST['unit_number'];
    $capacity = $_POST['capacity'];
    $room_type = $_POST['room_type']; // New field for room type

    // Check if the unit number already exists
    $checkStmt = $pdo->prepare("SELECT * FROM rooms WHERE unit_number = ?");
    $checkStmt->execute([$unit_number]);

    if ($checkStmt->rowCount() > 0) {
        // Unit number already exists
        $error_message = "The unit number '$unit_number' is already existing.";
    } else {
        // Insert new room into the database
        $stmt = $pdo->prepare("INSERT INTO rooms (rent, unit_number, capacity, room_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$rent, $unit_number, $capacity, $room_type]);

        // Redirect to the view rooms page after successful insertion
        header("Location: view_rooms.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Room</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="JRSLCSS/add_room.css">
    <link rel="stylesheet" href="JRSLCSS/dashboard.css">
    <link rel="stylesheet" href="styles.css">

    <!-- JavaScript for Popup Error Message -->
    <script>
        window.onload = function() {
            // Check if there's an error message to display
            <?php if (!empty($error_message)): ?>
                // Show the error popup
                document.getElementById("error-popup").style.display = "block";

                // Hide the popup after 2 seconds
                setTimeout(function() {
                    document.getElementById("error-popup").style.display = "none";
                }, 2000);
            <?php endif; ?>
        };
    </script>

    <style>
        /* Popup Style */
        #error-popup {
            display: none;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #f44336;
            color: white;
            padding: 15px;
            border-radius: 5px;
            font-weight: bold;
            z-index: 1000;
        }
    </style>
</head>
<body>

    <!-- Sidebar navigation -->
    <?php include 'sidebar.php'; ?>

    <!-- Error Popup -->
    <div id="error-popup">
        <?php echo $error_message; ?>
    </div>

    <!-- Page content -->
    <div class="main-content">
        <div class="form-container">
            <h2>Add New Room</h2>

            <form action="add_room.php" method="POST">
                <div class="form-group">
                    <label for="rent">Rent:</label>
                    <input type="number" id="rent" name="rent" value="<?php echo isset($rent) ? htmlspecialchars($rent) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="unit_number">Unit Number:</label>
                    <input type="text" id="unit_number" name="unit_number" value="<?php echo isset($unit_number) ? htmlspecialchars($unit_number) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="capacity">Capacity:</label>
                    <input type="number" id="capacity" name="capacity" value="<?php echo isset($capacity) ? htmlspecialchars($capacity) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="room_type">Room Type:</label>
                    <select id="room_type" name="room_type" required>
                        <option value="Solo Room" <?php echo isset($room_type) && $room_type === 'Solo Room' ? 'selected' : ''; ?>>Solo Room</option>
                        <option value="Small Room" <?php echo isset($room_type) && $room_type === 'Small Room' ? 'selected' : ''; ?>>Small Room</option>
                        <option value="Medium Room" <?php echo isset($room_type) && $room_type === 'Medium Room' ? 'selected' : ''; ?>>Medium Room</option>
                        <option value="Large Room" <?php echo isset($room_type) && $room_type === 'Large Room' ? 'selected' : ''; ?>>Large Room</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="submit" value="Add Room">
                </div>
            </form>
            
        </div>
    </div>
</body>
</html>
