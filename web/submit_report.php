<?php
session_start();
require '../db.php';
require '../vendor/autoload.php'; // Cloudinary PHP SDK

use Cloudinary\Cloudinary;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Cloudinary configuration
$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'db6foxkv8',
        'api_key'    => '535575711775959',
        'api_secret' => 'jEihjLpkHkVbF-ySiJjW9sajs3Q',
    ],
]);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenant_id = $_SESSION['user_id'];
    $report_details = $_POST['details'];

    // Handle the file upload to Cloudinary
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        try {
            $uploaded_image = $cloudinary->uploadApi()->upload(
                $_FILES['image']['tmp_name'],
                [
                    'folder' => 'apartment_reports', // Folder name in Cloudinary
                    'public_id' => time() . "_" . basename($_FILES['image']['name']), // Unique name
                ]
            );

            // Get the image URL
            $image_url = $uploaded_image['secure_url'];

            // Insert the report into the database
            $stmt = $pdo->prepare("INSERT INTO reports (tenant_id, report_details, image_path) VALUES (?, ?, ?)");
            $stmt->execute([$tenant_id, $report_details, $image_url]);

            echo "<script>alert('Report submitted successfully!'); window.location.href='user_dashboard.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Failed to upload the image to Cloudinary: " . $e->getMessage() . "'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('No image uploaded or an error occurred.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='user_dashboard.php';</script>";
}
?>
