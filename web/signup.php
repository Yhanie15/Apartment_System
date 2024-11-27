<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $unit_number = $_POST['unit_number'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $age = $_POST['age'];
    $birth_date = $_POST['birth_date'];
    $address = $_POST['address'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Step 1: Get the capacity of the selected unit from the rooms table
    $stmt = $pdo->prepare("SELECT capacity FROM rooms WHERE unit_number = ?");
    $stmt->execute([$unit_number]);
    $room = $stmt->fetch();

    if (!$room) {
        echo "<script>alert('Invalid unit number.');</script>";
    } else {
        // Step 2: Check if the number of tenants already in the unit has reached its capacity
        $capacity = $room['capacity'];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tenant_account WHERE unit_number = ?");
        $stmt->execute([$unit_number]);
        $tenantCount = $stmt->fetchColumn();

        if ($tenantCount >= $capacity) {
            echo "<script>alert('Unit number is already at full capacity.');</script>";
        } else {
            // Step 3: Proceed with the signup if capacity is not yet reached
            $stmt = $pdo->prepare("INSERT INTO tenant_account (unit_number, first_name, last_name, age, birth_date, address, username, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$unit_number, $first_name, $last_name, $age, $birth_date, $address, $username, $password])) {
                echo "<script>alert('Sign up successful!'); window.location.href='login.php';</script>";
            } else {
                echo "<script>alert('Error: Could not sign up.');</script>";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - JRSL Apartment</title>

    <!-- External CSS libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="signup.css">
</head>
<body>

    <!-- Navigation Bar -->
    <nav>
        <div class="logo">
            <img src="img/jrsl_logo.png" alt="JRSL Logo">
        </div>
        <input type="checkbox" id="menu-toggle">
        <label for="menu-toggle" class="menu-icon"><i class="fas fa-bars"></i></label>
        <ul class="nav-links">
            <li><a href="home.php">Home</a></li>
            <li><a href="home.php#room">Rooms</a></li>
            <li><a href="home.php#amenities">Amenities</a></li>
            <li><a href="home.php#contact">Contact</a></li>
            <li><a class="login-btn" href="login.php">Log In</a></li>
        </ul>
    </nav>

    <!-- Main Signup Section -->
    <section class="signup-section">
        <div class="signup-container">
            <!-- Left side with background image -->
            <div class="signup-left">
                <img src="img/bg.jpg" alt="Background Image">
            </div>

            <!-- Right side with signup form -->
            <div class="signup-right">
                <div class="signup-form">
                    <h2>Sign Up</h2>
                    <form method="post">
                        <div class="input-group">
                            <input type="text" id="first-name" name="first_name" placeholder="First Name" required>
                        </div>
                        <div class="input-group">
                            <input type="text" id="last-name" name="last_name" placeholder="Last Name" required>
                        </div>
                        <div class="input-group dual-input">
                            <input type="number" id="age" name="age" placeholder="Age" required>
                            <input type="date" id="birth-date" name="birth_date" placeholder="Birth Date" required>
                        </div>
                        <div class="input-group">
                            <input type="text" id="address" name="address" placeholder="Address" required>
                        </div>
                        <div class="input-group">
                            <input type="email" id="username" name="username" placeholder="Username/Email" required>
                        </div>
                        <div class="input-group">
                            <input type="number" id="unit-number" name="unit_number" placeholder="Enter Unit Number" required>
                        </div>
                        <div class="input-group">
                            <input type="password" id="password" name="password" placeholder="Password" required>
                        </div>
                        <div class="input-group">
                            <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm Password" required>
                        </div>
                        <button type="submit" class="btn">Sign Up</button>
                    </form>
                    <p>Have an account? <a href="login.php">Click here to Log in.</a></p>
                </div>
            </div>
        </div>
    </section>

</body>
</html>
