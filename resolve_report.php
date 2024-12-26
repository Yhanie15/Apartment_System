<?php
// Start the session
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

// Include the database connection
include 'db.php';

// Check if the report ID is provided in the URL
if (isset($_GET['id'])) {
    $reportId = $_GET['id'];

    // Prepare and execute the SQL query to mark the report as resolved and archived
    $stmt = $pdo->prepare("UPDATE reports SET status = 'resolved', archived = TRUE WHERE id = ?");
    $stmt->execute([$reportId]);

    // Check if the query was successful
    if ($stmt->rowCount() > 0) {
        // Redirect back to the maintenance page with a success message
        echo "<script>
                alert('The report has been successfully resolved and archived.');
                window.location.href = 'maintenance.php';
              </script>";
        exit();
    } else {
        // Redirect back to the maintenance page with an error message
        echo "<script>
                alert('Failed to resolve the report. Please try again.');
                window.location.href = 'maintenance.php';
              </script>";
        exit();
    }
} else {
    // If no ID is provided, show an error message
    echo "<script>
            alert('Invalid request. No report ID provided.');
            window.location.href = 'maintenance.php';
          </script>";
    exit();
}
?>
