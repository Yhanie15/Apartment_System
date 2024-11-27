<?php
session_start();
require '../config.php'; // Adjusted path to config.php

$error = '';

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Create a connection
    $conn = new mysqli("localhost", "root", "" , "apartment_management");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind
    if ($stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?")) {
        $stmt->bind_param("s", $username);

        // Execute the statement
        if ($stmt->execute()) {
            // Bind result variables
            $stmt->bind_result($id, $hashed_password);

            // Fetch value
            if ($stmt->fetch()) {
                if (password_verify($password, $hashed_password)) {
                    // Password is correct
                    $_SESSION['user_id'] = $id;
                    $_SESSION['username'] = $username;
                    header("Location: ../dashboard.php"); // Adjusted path to redirect to dashboard
                    exit();
                } else {
                    // Incorrect password
                    $error = "Invalid username or password.";
                }
            } else {
                // Incorrect username
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }

        $stmt->close();
    } else {
        $error = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JRSL Apartment Management System</title>
    <link rel="stylesheet" href="../styles.css"> <!-- Adjusted path to styles.css -->
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <h2>Login</h2>
            
            <?php if (!empty($error)) { echo "<p class='error'>$error</p>"; } ?>
            <form method="post" action="">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <input type="submit" value="Login">
            </form>
        </div>
    </div>
</body>
</html>
