<?php
session_start();
include '../db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: web/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data from tenant_account table based on the logged-in user
$stmt = $pdo->prepare("SELECT username, unit_number, first_name, last_name, middle_name, ext_name, birth_date, gender, age, address, email, phone, emergency_contact_name, relationship, emergency_contact_number, profile_picture FROM tenant_account WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "<script>alert('User not found.'); window.location.href='login.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="user_dashboard.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img src="img/jrsl_logo.png" alt="JRSL Logo">
            </div>
            <nav>
                <ul>
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="#"> Rent & Bills History</a></li>
                    <li><a href="#">Payment History</a></li>
                    <li><a href="logout.php">Log Out</a></li>
                </ul>
            </nav>
        </div>
        <div class="main-content">
            <div class="header">
                <h2>Welcome to Room #<?php echo htmlspecialchars($user['unit_number']); ?>, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                <div class="profile">
                    <div class="avatar">
                        <img src="img/pic1.jpg" alt="Profile Picture">
                    </div>
                    <a href="#" class="edit-profile" onclick="showForm()">Edit Profile</a>
                </div>
            </div>
            <div class="dashboard-content">
                <div class="monthly-bills card">
                    <h3>Water Bills</h3>
                </div>
                <div class="balances card">
                    <h3>Electricity Bills</h3>
                </div>
                <div class="payment-history card">
                    <h3>Balances</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay Form -->
    <div id="overlayForm" class="overlay-form">
        <h2>Edit Profile</h2>
        
        <!-- Upload Profile Picture Section -->
        <div class="upload-section">
            <label for="profile_picture">Upload Profile Picture</label>
            <input type="file" id="profile_picture" name="profile_picture">
        </div>

        <form method="POST" action="update_profile.php" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">

            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" placeholder="First Name *" required>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" placeholder="Last Name *" required>
            <input type="text" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name']); ?>" placeholder="Middle Name">
            <input type="text" name="ext_name" value="<?php echo htmlspecialchars($user['ext_name']); ?>" placeholder="Extension Name">
            <input type="date" name="birth_date" value="<?php echo htmlspecialchars($user['birth_date']); ?>" placeholder="Date of Birth *" required>
            <select name="gender" required>
                <option value="">Gender *</option>
                <option value="Male" <?php echo ($user['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo ($user['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
            </select>
            <input type="number" name="age" value="<?php echo htmlspecialchars($user['age']); ?>" placeholder="Age *" required>
            <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" placeholder="Address *" required>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Email *" required>
            <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="Phone Number *" required>
            <input type="text" name="emergency_contact_name" value="<?php echo htmlspecialchars($user['emergency_contact_name']); ?>" placeholder="Emergency Contact Name *" required>
            <input type="text" name="relationship" value="<?php echo htmlspecialchars($user['relationship']); ?>" placeholder="Relationship *" required>
            <input type="text" name="emergency_contact_number" value="<?php echo htmlspecialchars($user['emergency_contact_number']); ?>" placeholder="Emergency Contact Number *" required>
            <button type="submit">Save</button>
        </form>
        <span class="close-btn" onclick="hideForm()">&times;</span>
    </div>

    <script>
        function showForm() {
            document.getElementById("overlayForm").style.display = "block";
        }

        function hideForm() {
            document.getElementById("overlayForm").style.display = "none";
        }
    </script>
</body>
</html>
