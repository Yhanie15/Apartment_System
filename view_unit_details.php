<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

include 'db.php'; // Include db.php to get $pdo connection

// Function to calculate water and electricity bills
function calculateBills($unit_number, $electricity_rate, $water_rate, $electricity_consumption, $water_consumption) {
    $total_electricity_bill = $electricity_rate * $electricity_consumption;
    $total_water_bill = $water_rate * $water_consumption;
    
    return array(
        'unit_number' => $unit_number,
        'total_electricity_bill' => $total_electricity_bill,
        'total_water_bill' => $total_water_bill
    );
}

// If form is submitted, calculate the bills
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unit_number = $_POST['unit_number']; // Adjust based on your form field
    $electricity_rate = $_POST['electricity_rate']; // Adjust based on your form field
    $water_rate = $_POST['water_rate']; // Adjust based on your form field
    $electricity_consumption = $_POST['electricity_consumption']; // Adjust based on your form field
    $water_consumption = $_POST['water_consumption']; // Adjust based on your form field

    // Calculate bills
    $bills = calculateBills($unit_number, $electricity_rate, $water_rate, $electricity_consumption, $water_consumption);

    // Store bills in session
    $_SESSION['bills'] = $bills;

    try {
        // Delete existing bills for the unit
        $stmt = $pdo->prepare("DELETE FROM bills WHERE unit_number = ?");
        $stmt->execute([$unit_number]);

        // Insert new bills for the unit
        $stmt = $pdo->prepare("
            INSERT INTO bills (unit_number, electricity_bill, water_bill)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$unit_number, $bills['total_electricity_bill'], $bills['total_water_bill']]);

        // Redirect to bills_payment.php to display the calculated bills
        header("Location: bills_payment.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Fetch room data
$id = $_GET['id']; // Ensure this is sanitized properly
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$id]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch tenants for the room
$stmt = $pdo->prepare("SELECT * FROM tenants WHERE unit_number = ?");
$stmt->execute([$room['unit_number']]);
$tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total bills from the database
$stmt = $pdo->prepare("SELECT SUM(electricity_bill) AS total_electricity_bill, SUM(water_bill) AS total_water_bill FROM bills WHERE unit_number = ?");
$stmt->execute([$room['unit_number']]);
$total_bills = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Unit Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Ensure this stylesheet exists -->
    <link rel="stylesheet" href="JRSLCSS/view_unit_details.css">
    <link rel="stylesheet" href="JRSLCSS/dashboard.css">
</head>
<body>

    <!-- Sidebar navigation - This is a sample, adjust based on your actual design -->
    <?php include 'sidebar.php'; ?>

    <!-- Page content -->
    <div class="main-content">
        <h2>View Unit Details</h2>

        <!-- Unit details -->
        <div class="form-container">
        <div class="unit-details">
            <h2>Unit Details</h2>
            <p><strong>Unit Number:</strong> <?php echo htmlspecialchars($room['unit_number']); ?></p>

            <!-- Display total bills from the database -->
            
            <?php if ($total_bills): ?>
                <p><strong>Total Electricity Bill:</strong> PHP<?php echo number_format($total_bills['total_electricity_bill'], 2); ?></p>
                <p><strong>Total Water Bill:</strong> PHP<?php echo number_format($total_bills['total_water_bill'], 2); ?></p>
            <?php endif; ?>
        </div>
         
        <!-- Form to calculate bills -->
        <div class="form-container">
            <h2>Calculate Bills</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id; ?>" method="post">
                <label for="electricity_rate">Electricity Rate (PHP per kWh):</label>
                <input type="number" id="electricity_rate" name="electricity_rate" step="0.01" required
                    value="<?php echo isset($_POST['electricity_rate']) ? htmlspecialchars($_POST['electricity_rate']) : ''; ?>">

                <label for="electricity_consumption">Electricity Consumption (kWh):</label>
                <input type="number" id="electricity_consumption" name="electricity_consumption" required
                    value="<?php echo isset($_POST['electricity_consumption']) ? htmlspecialchars($_POST['electricity_consumption']) : ''; ?>">

                <label for="water_rate">Water Rate (PHP per gallon):</label>
                <input type="number" id="water_rate" name="water_rate" step="0.01" required
                    value="<?php echo isset($_POST['water_rate']) ? htmlspecialchars($_POST['water_rate']) : ''; ?>">

                <label for="water_consumption">Water Consumption (gallons):</label>
                <input type="number" id="water_consumption" name="water_consumption" required
                    value="<?php echo isset($_POST['water_consumption']) ? htmlspecialchars($_POST['water_consumption']) : ''; ?>">

                <input type="hidden" name="unit_number" value="<?php echo htmlspecialchars($room['unit_number']); ?>">
                <input type="submit" name="calculate" value="Calculate Bills">
            </form>
        </div>

        <!-- Display the calculated bills -->
        <?php if (isset($_SESSION['bills'])) : ?>
            <div class="form-container">
                <h2>Calculated Bills</h2>
                <p><strong>Electricity Bill:</strong> PHP<?php echo number_format($bills['total_electricity_bill'], 2); ?></p>
                <p><strong>Water Bill:</strong> PHP<?php echo number_format($bills['total_water_bill'], 2); ?></p>
            </div>
        <?php endif; ?>

        <!-- Back button -->
        <a href="bills_payment.php" class="back-button">Back to Bills & Payment</a>
    </div>

</body>
</html>

