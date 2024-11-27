<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

// Check if room ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_rooms.php");
    exit();
}

$room_id = $_GET['id'];

// Delete room from the database
$stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
$stmt->execute([$room_id]);

// Redirect to rooms list after deletion
header("Location: view_rooms.php");
exit();
?>
