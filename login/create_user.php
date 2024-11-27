<?php
require '../config.php'; // Adjusted path to config.php

// Define a function to create a new user
function createUser($username, $password) {
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Create a connection
    $conn = new mysqli("localhost", "root", "", "apartment_management");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);

    // Execute and check for success
    if ($stmt->execute()) {
        echo "New user '$username' created successfully.<br>";
    } else {
        echo "Error: " . $stmt->error . "<br>";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}

// Example usage
createUser('newuser4', 'password4'); // Create first user // Create second user
// Call createUser as many times as needed for different usernames and passwords
?>
