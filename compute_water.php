<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'apartment_management');

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$previous_reading = 0; // Default value for previous reading
$is_first_time = true; // Assume it's the first time calculating the bill

// Check if a unit number is provided in the GET request
if (isset($_GET['unit_number'])) {
    $unit_number = $_GET['unit_number'];

    // Fetch the most recent `current_reading` from the previous month's calculation
    $latest_reading_sql = "
        SELECT current_reading 
        FROM water_calculations 
        WHERE unit_number = ? 
        ORDER BY meter_read_date DESC 
        LIMIT 1
    ";
    $stmt = $conn->prepare($latest_reading_sql);
    $stmt->bind_param('s', $unit_number);
    $stmt->execute();
    $stmt->bind_result($previous_reading);
    if ($stmt->fetch()) {
        $is_first_time = false; // A record exists; it's not the first time
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $unit_number = $_POST['unit_number'];
    $previous_reading = $_POST['previous_reading']; // Pre-filled or fetched value
    $current_reading = $_POST['current_reading'];
    $water_rate = $_POST['water_rate'];
    $meter_read_date = $_POST['meter_read_date'];
    $calculation_month = $_POST['calculation_month']; // Retrieve the selected month

    // Validate input: Current reading must be greater than or equal to previous reading
    if ($current_reading < $previous_reading) {
        echo "Error: Current reading must be greater than or equal to the previous reading.";
        exit;
    }

    // Calculate total consumption and total bill
    $total_consumption = $current_reading - $previous_reading;
    $total_bill = $total_consumption * $water_rate;

    // Convert the month from "YYYY-MM" to "Month Year" format (e.g., "August 2024")
    $dateObj = DateTime::createFromFormat('Y-m', $calculation_month);
    $formatted_month = $dateObj->format('F Y'); // "F" for full month name, "Y" for year

    // Calculate the due date (7 days after the meter_read_date)
    $meter_read_date_obj = new DateTime($meter_read_date);
    $meter_read_date_obj->modify('+7 days');
    $due_date = $meter_read_date_obj->format('Y-m-d');

    // Insert the data into water_calculations
    $sql = "INSERT INTO water_calculations (
                unit_number, 
                previous_reading, 
                current_reading, 
                water_consumption, 
                water_rate, 
                water_bill, 
                meter_read_date, 
                calculation_month, 
                due_date, 
                current_status
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Unpaid'
            )";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        'sddddssss',
        $unit_number,
        $previous_reading,
        $current_reading,
        $total_consumption,
        $water_rate,
        $total_bill,
        $meter_read_date,
        $formatted_month,
        $due_date
    );

    if ($stmt->execute()) {
        echo "Water bill calculated successfully!";
        header("Location: water_payment_history.php?unit_number=" . urlencode($unit_number)); // Redirect after successful insertion
        exit;
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
}

// Close the connection
$conn->close();
?>
