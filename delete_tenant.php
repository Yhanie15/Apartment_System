<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

if (isset($_GET['id'])) {
    $tenant_id = $_GET['id'];

    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Fetch the unit number associated with the tenant
        $stmt = $pdo->prepare("SELECT unit_number FROM tenants WHERE id = ?");
        $stmt->execute([$tenant_id]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($tenant) {
            $unit_number = $tenant['unit_number'];

            // Delete the tenant
            $stmt = $pdo->prepare("DELETE FROM tenants WHERE id = ?");
            $stmt->execute([$tenant_id]);

            // Reset bills associated with the tenant's unit number
            $stmt = $pdo->prepare("UPDATE bills SET electricity_bill = 0, water_bill = 0, due_date_rent = NULL, due_date_bills = NULL WHERE unit_number = ?");
            $stmt->execute([$unit_number]);

            // Delete rent payments associated with the tenant's unit number
            $stmt = $pdo->prepare("DELETE FROM rent_payments WHERE unit_number = ?");
            $stmt->execute([$unit_number]);
        }

        // Commit transaction
        $pdo->commit();

        // Redirect to the tenant list page with a success message
        $_SESSION['success'] = "Tenant deleted successfully and associated bills and payments reset.";
        header("Location: view_tenants.php");
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    // Redirect to tenant list if no tenant ID is provided
    header("Location: view_tenants.php");
}
?>