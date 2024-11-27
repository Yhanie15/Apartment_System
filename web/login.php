<?php
include '../db.php';

function loginUser($pdo, $unit_number, $username, $password) {
    $stmt = $pdo->prepare("SELECT * FROM tenant_account WHERE unit_number = ? AND username = ?");
    $stmt->execute([$unit_number, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['unit_number'] = $user['unit_number'];
        $_SESSION['username'] = $user['username'];
        header("Location: user_dashboard.php");
        exit();
    } else {
        echo "<script>alert('Invalid unit number, username, or password. Please try again.'); window.location.href='login.php';</script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $unit_number = $_POST['unitnumber'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    loginUser($pdo, $unit_number, $username, $password);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - JRSL Apartment</title>

    <!-- External CSS libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="login.css"> <!-- New separate CSS file -->
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
            <li><a class="login-btn" href="signup.php">Sign Up</a></li>
        </ul>
    </nav>

    <!-- Main Login Section -->
    <section class="login-section">
        <div class="login-container">
            <!-- Left side with background image -->
            <div class="login-left">
                <img src="img/bg.jpg" alt="Background Image">
            </div>
        
            <!-- Right side with login form -->
            <div class="login-right">
                <div class="login-form">
                    <h2>Log In</h2>
                    <form action="" method="post">
                        <div class="input-group">
                            <input id="unitnumber" name="unitnumber" placeholder="Unit Number" required>
                        </div>
                        <div class="input-group">
                            <input type="email" id="username" name="username" placeholder="Username/Email" required>
                        </div>
                        <div class="input-group">
                            <input type="password" id="password" name="password" placeholder="Password" required>
                        </div>
                        <button type="submit" class="btn">Log In</button>
                    </form>
                    <p>Don't have an account? <a href="signup.php">Click here to Sign up.</a></p>
                </div>
            </div>
        </div>
    </section>

</body>
</html>