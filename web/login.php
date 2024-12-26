<?php
include '../db.php';

function loginUser($pdo, $role, $unit_number, $username, $password) {
    // Determine the table and query based on role
    $table = ($role === 'admin') ? 'users' : 'tenant_account';
    $query = ($role === 'admin') ? "SELECT * FROM $table WHERE username = ?" : "SELECT * FROM $table WHERE unit_number = ? AND username = ?";

    $stmt = $pdo->prepare($query);

    // Bind parameters based on the role
    if ($role === 'admin') {
        $stmt->execute([$username]);
    } else {
        $stmt->execute([$unit_number, $username]);
    }

    $user = $stmt->fetch();

    // Verify the password and start session if credentials are valid
    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $role;

        // Redirect based on the role
        if ($role === 'admin') {
            header("Location: ../dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }
        exit();
    } else {
        echo "<script>alert('Invalid credentials. Please try again.'); window.location.href='login.php';</script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = isset($_POST['role']) ? $_POST['role'] : null;
    $unit_number = $_POST['unitnumber'] ?? null; // Unit number is null for admin
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($role) {
        loginUser($pdo, $role, $unit_number, $username, $password);
    } else {
        echo "<script>alert('Please select a role.'); window.location.href='login.php';</script>";
    }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
/>

    <link rel="stylesheet" href="login.css"> <!-- New separate CSS file -->
    <script>

    document.addEventListener("DOMContentLoaded", () => {
    const togglePassword = document.getElementById("toggle-password");
    const passwordInput = document.getElementById("password");
    const eyeIcon = document.getElementById("eye-icon");

    if (togglePassword) {
        togglePassword.addEventListener("click", () => {
            const isPasswordVisible = passwordInput.type === "text";
            passwordInput.type = isPasswordVisible ? "password" : "text";
            eyeIcon.classList.toggle("fa-eye-slash", !isPasswordVisible);
            eyeIcon.classList.toggle("fa-eye", isPasswordVisible);
        });
    }
});

    function toggleFields(role) {
        const unitNumberField = document.getElementById('unitnumber-group');
        const unitNumberInput = document.getElementById('unitnumber');
        const usernameField = document.getElementById('username-group');

        if (role === 'tenant') {
            unitNumberField.style.display = 'block'; // Show Unit Number for tenant
            unitNumberInput.required = true; // Make Unit Number required
            usernameField.style.display = 'block'; // Show Username for tenant
        } else {
            unitNumberField.style.display = 'none'; // Hide Unit Number for admin
            unitNumberInput.required = false; // Remove required attribute for Unit Number
            usernameField.style.display = 'block'; // Username still required for admin
        }
    }
</script>

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
    <div class="toggle-container">
        <label class="toggle-btn">
            <input type="radio" name="role" value="tenant" checked onchange="toggleFields('tenant')">
            <span>Tenant</span>
        </label>
        <label class="toggle-btn">
            <input type="radio" name="role" value="admin" onchange="toggleFields('admin')">
            <span>Admin</span>
        </label>
    </div>
    <div class="input-group" id="unitnumber-group">
        <input id="unitnumber" name="unitnumber" placeholder="Unit Number">
    </div>
    <div class="input-group" id="username-group">
        <input type="text" id="username" name="username" placeholder="Username" required>
    </div>
    <div class="input-group">
    <input type="password" id="password" name="password" placeholder="Password" required>
    <span id="toggle-password" style="cursor: pointer; position: absolute; right: 10px; top: 50%; transform: translateY(-50%);">
        <i class="fas fa-eye" id="eye-icon"></i>
    </span>
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
