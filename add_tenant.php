<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

// Handle form submission to add a new tenant
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $move_in_date = $_POST['move_in_date'];
    $unit_number = $_POST['unit_number'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $birthday = $_POST['birthday'];
    $emergency_name = $_POST['emergency_name'];
    $emergency_contact_number = $_POST['emergency_contact_number'];

    try {
        // Insert new tenant into the database
        $stmt = $pdo->prepare("INSERT INTO tenants (last_name, first_name, middle_name, email, phone, move_in_date, unit_number, gender, address, birthday, emergency_name, emergency_contact_number) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$last_name, $first_name, $middle_name, $email, $phone, $move_in_date, $unit_number, $gender, $address, $birthday, $emergency_name, $emergency_contact_number]);

        // Resetting bills and payments associated with the unit number
        $stmt = $pdo->prepare("UPDATE bills SET electricity_bill = 0, water_bill = 0, due_date_rent = NULL, due_date_bills = NULL WHERE unit_number = ?");
        $stmt->execute([$unit_number]);

        header("Location: view_tenants.php");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Tenant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Make sure styles.css is updated to include nav styles -->
    <link rel="stylesheet" href="JRSLCSS/add_tenant.css">
    <link rel="stylesheet" href="JRSLCSS/dashboard.css">
</head>
<body>

    <!-- Sidebar navigation -->
    <?php include 'sidebar.php'; ?>

    <!-- Page content -->
    <div class="main-content">
        <div class="form-container">
        <a href="view_tenants.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to List of Tenant</a>
            <h2>Add New Tenant</h2>
            <form action="add_tenant.php" method="POST">
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="middle_name">Middle Name:</label>
                    <input type="text" id="middle_name" name="middle_name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="move_in_date">Move-in Date:</label>
                    <input type="date" id="move_in_date" name="move_in_date" required>
                </div>
                <div class="form-group">
                    <label for="unit_number">Unit Number:</label>
                    <select id="unit_number" name="unit_number" required>
                        <?php
                        // Fetch units from the database
                        $stmt = $pdo->query("SELECT id, unit_number, capacity FROM rooms");
                        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($rooms as $room) {
                            // Check the current occupancy of each unit
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tenants WHERE unit_number = ?");
                            $stmt->execute([$room['unit_number']]);
                            $current_occupancy = $stmt->fetchColumn();

                            // Only display the unit if it hasn't reached maximum capacity
                            if ($current_occupancy < $room['capacity']) {
                                echo "<option value='" . htmlspecialchars($room['unit_number']) . "'>" . htmlspecialchars($room['unit_number']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="address">Address:</label>
                    <textarea id="address" name="address"></textarea>
                </div>
                <div class="form-group">
                    <label for="birthday">Birthday:</label>
                    <input type="date" id="birthday" name="birthday">
                </div>
                <div class="form-group">
                    <label for="emergency_contact_name">Emergency Contact Name:</label>
                    <input type="text" id="emergency_name" name="emergency_name">
                </div>
                <div class="form-group">
                    <label for="emergency_contact_number">Emergency Contact Number:</label>
                    <input type="text" id="emergency_contact_number" name="emergency_contact_number">
                </div>
                <div class="form-group">
                    <input type="submit" value="Add Tenant">
                </div>
            </form>
            
        </div>
    </div>

</body>
</html>
