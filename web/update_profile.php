<?php
session_start();
include '../db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_POST['user_id'];

// Prepare an array to store fields to be updated
$fields_to_update = [];
$params = [];

// Define a helper function to check and prepare fields
function addFieldIfNotEmpty(&$fields_to_update, &$params, $field, $value) {
    if (!empty($value)) {
        $fields_to_update[] = "$field = ?";
        $params[] = $value;
    }
}

// Check each field and add to update array if not empty
addFieldIfNotEmpty($fields_to_update, $params, 'first_name', $_POST['first_name']);
addFieldIfNotEmpty($fields_to_update, $params, 'last_name', $_POST['last_name']);
addFieldIfNotEmpty($fields_to_update, $params, 'middle_name', $_POST['middle_name']);
addFieldIfNotEmpty($fields_to_update, $params, 'ext_name', $_POST['ext_name']);
addFieldIfNotEmpty($fields_to_update, $params, 'birth_date', $_POST['birth_date']);
addFieldIfNotEmpty($fields_to_update, $params, 'gender', $_POST['gender']);
addFieldIfNotEmpty($fields_to_update, $params, 'age', $_POST['age']);
addFieldIfNotEmpty($fields_to_update, $params, 'address', $_POST['address']);
addFieldIfNotEmpty($fields_to_update, $params, 'email', $_POST['email']);
addFieldIfNotEmpty($fields_to_update, $params, 'phone', $_POST['phone_number']);
addFieldIfNotEmpty($fields_to_update, $params, 'emergency_contact_name', $_POST['emergency_contact_name']);
addFieldIfNotEmpty($fields_to_update, $params, 'relationship', $_POST['relationship']);
addFieldIfNotEmpty($fields_to_update, $params, 'emergency_contact_number', $_POST['emergency_contact_number']);

// Handle profile picture upload
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $profile_picture_path = 'img/' . basename($_FILES['profile_picture']['name']);
    move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture_path);
    $fields_to_update[] = 'profile_picture = ?';
    $params[] = $profile_picture_path;
}

// Ensure there's something to update
if (!empty($fields_to_update)) {
    $params[] = $user_id;
    $sql = "UPDATE tenant_account SET " . implode(', ', $fields_to_update) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($params)) {
        echo "<script>alert('Profile updated successfully.'); window.location.href='user_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error updating profile. Please try again.'); window.location.href='user_dashboard.php';</script>";
    }
} else {
    echo "<script>alert('No changes were made.'); window.location.href='user_dashboard.php';</script>";
}
?>
