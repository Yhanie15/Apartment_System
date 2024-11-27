<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get the $pdo connection

// Check if unit number is provided in the URL
if (!isset($_GET['unit_number'])) {
    header("Location: view_tenants.php");
    exit();
}

$unit_number = $_GET['unit_number'];

// Fetch tenant data with associated room details
$stmt = $pdo->prepare("SELECT tenant_account.*, rooms.unit_number AS room_name 
                       FROM tenant_account 
                       LEFT JOIN rooms ON tenant_account.unit_number = rooms.unit_number 
                       WHERE tenant_account.unit_number = ?");
$stmt->execute([$unit_number]);
$tenant = $stmt->fetch(PDO::FETCH_ASSOC);

// If tenant is not found, redirect back to view tenants page
if (!$tenant) {
    header("Location: view_tenants.php");
    exit();
}

// Handle form submission for editing tenant details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $surname = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $move_in_date = $_POST['move_in_date'];
    $unit_number = $_POST['unit_number'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $birth_date = $_POST['birth_date'];
    $emergency_contact_name = $_POST['emergency_contact_name'];
    $emergency_contact_number = $_POST['emergency_contact_number'];

    // Update tenant information
    $stmt = $pdo->prepare("UPDATE tenant_account
                           SET last_name = ?, first_name = ?, email = ?, phone = ?, 
                               move_in_date = ?, unit_number = ?, gender = ?, address = ?, 
                               birth_date = ?, emergency_contact_name = ?, emergency_contact_number = ? 
                           WHERE unit_number = ?");
    $isUpdated = $stmt->execute([$surname, $first_name, $email, $phone, $move_in_date, $unit_number, $gender, $address, $birth_date, $emergency_contact_name, $emergency_contact_number, $unit_number]);

    if ($isUpdated) {
        echo "<script>alert('Tenant information updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating tenant information.');</script>";
    }
}

// Fetch room list for dropdown, including current tenant count and capacity
$stmt = $pdo->query("SELECT r.unit_number, r.capacity, COUNT(t.unit_number) AS current_tenants
                     FROM rooms r
                     LEFT JOIN tenant_account t ON r.unit_number = t.unit_number
                     GROUP BY r.unit_number, r.capacity");
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Tenant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="JRSLCSS/view_tenant.css"> <!-- Assuming view_tenant.css will style this page -->
    <style>
        /* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000; /* Make sure it's above other content */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
    transition: opacity 0.3s ease-in-out;
}

/* Modal content styles */
.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px 30px; /* 20px padding sa top at bottom, 30px sa left at right */
    border: 1px solid #888;
    width: 80%;
    max-width: 800px; /* This is the maximum width */
    border-radius: 10px;
    transition: transform 0.3s ease-in-out;
    transform: scale(0.9);
    display: flex;
    flex-direction: column;
    align-items: stretch;
    max-height: 90vh; /* Limit the height of the modal */
    overflow-y: auto; /* Allow vertical scrolling if content exceeds */
}

/* Modal visibility when active */
.modal.show {
    display: flex;
    opacity: 1;
    transform: scale(1);
}

/* Close button styles */
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
        

        .form-container {
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <!-- Sidebar navigation -->
    <?php include 'sidebar.php'; ?>

    <!-- Page content -->
    <div class="main-content">
        <h2>Tenant Details</h2>

        <!-- Tenant Info -->
        <div class="tenant-info">
            <p><strong>Full Name:</strong> <?php echo htmlspecialchars($tenant['first_name'] . ' ' . $tenant['last_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($tenant['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($tenant['phone']); ?></p>
            <p><strong>Unit Number:</strong> <?php echo htmlspecialchars($tenant['unit_number']); ?></p>
            <p><strong>Move-in Date:</strong> <?php echo htmlspecialchars($tenant['move_in_date']); ?></p>
            <p><strong>Gender:</strong> <?php echo htmlspecialchars($tenant['gender']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($tenant['address']); ?></p>
            <p><strong>Birthday:</strong> <?php echo htmlspecialchars($tenant['birth_date']); ?></p>
            <p><strong>Emergency Contact:</strong> <?php echo htmlspecialchars($tenant['emergency_contact_name']); ?> (<?php echo htmlspecialchars($tenant['emergency_contact_number']); ?>)</p>

            <!-- Edit Tenant Button -->
            <button id="openEditFormBtn" class="button">Edit Tenant</button>
        </div>
    </div>

    <!-- Modal Form for Editing Tenant -->
    <div id="editFormModal" class="modal">
        <div class="modal-content">
            <span id="closeModalBtn" class="close-btn">&times;</span>
            <h2>Edit Tenant</h2>
            <form action="view_tenant.php?id=<?php echo $tenant_id; ?>" method="POST">
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($tenant['last_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($tenant['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($tenant['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($tenant['phone']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="move_in_date">Move-in Date:</label>
                    <input type="date" id="move_in_date" name="move_in_date" value="<?php echo htmlspecialchars($tenant['move_in_date']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="unit_number">Unit Number:</label>
                    <select name="unit_number" id="unit_number" required>
                        <option value="<?php echo htmlspecialchars($tenant['unit_number']); ?>"><?php echo htmlspecialchars($tenant['unit_number']); ?></option>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo htmlspecialchars($room['unit_number']); ?>">
                                <?php echo htmlspecialchars($room['unit_number']); ?> (<?php echo htmlspecialchars($room['current_tenants']); ?>/<?php echo htmlspecialchars($room['capacity']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select name="gender" id="gender">
                        <option value="Male" <?php if ($tenant['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if ($tenant['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                        <option value="Other" <?php if ($tenant['gender'] == 'Other') echo 'selected'; ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                            <label for="address">Address:</label>
                            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($tenant['address']); ?>">
                        </div>
                <div class="form-group">
                            <label for="birthday">Birthday:</label>
                            <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($tenant['birth_date']); ?>">
                        </div>
                <div class="form-group">
                    <label for="emergency_contact_name">Emergency Contact Name:</label>
                    <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="<?php echo htmlspecialchars($tenant['emergency_contact_name']); ?>">
                </div>
                <div class="form-group">
                    <label for="emergency_contact_number">Emergency Contact Number:</label>
                    <input type="text" id="emergency_contact_number" name="emergency_contact_number" value="<?php echo htmlspecialchars($tenant['emergency_contact_number']); ?>">
                </div>
                <button type="submit" class="button">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        const openEditFormBtn = document.getElementById('openEditFormBtn');
        const editFormModal = document.getElementById('editFormModal');
        const closeModalBtn = document.getElementById('closeModalBtn');

        openEditFormBtn.addEventListener('click', () => {
            editFormModal.style.display = 'flex';  // Show modal
        });

        closeModalBtn.addEventListener('click', () => {
            editFormModal.style.display = 'none';  // Close modal
        });

        // Close modal if user clicks outside the modal
        window.addEventListener('click', (e) => {
            if (e.target === editFormModal) {
                editFormModal.style.display = 'none';
            }
        });
    </script>
</body>
</html>