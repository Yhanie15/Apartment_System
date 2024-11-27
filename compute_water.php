<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'apartment_management');

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $unit_number = $_POST['unit_number'];
    $water_rate = $_POST['water_rate'];
    $water_consumption = $_POST['water_consumption'];
    $meter_read_date = $_POST['meter_read_date'];
    $calculation_month = $_POST['calculation_month'];  // Retrieve the selected month

    // Convert the month from "YYYY-MM" to "Month Year" format (e.g., "August 2024")
    $dateObj = DateTime::createFromFormat('Y-m', $calculation_month);
    $formatted_month = $dateObj->format('F Y');  // "F" for full month name, "Y" for year

    // Calculate the water bill
    $water_bill = $water_rate * $water_consumption;

    // Calculate the due date (7 days after the meter_read_date)
    $meter_read_date_obj = new DateTime($meter_read_date);
    $meter_read_date_obj->modify('+7 days');
    $due_date = $meter_read_date_obj->format('Y-m-d');

    // Insert the data into water_calculations with the formatted month and due date
    $sql = "INSERT INTO water_calculations (unit_number, water_rate, water_consumption, meter_read_date, calculation_month, water_bill, due_date, current_status)
            VALUES ('$unit_number', '$water_rate', '$water_consumption', '$meter_read_date', '$formatted_month', '$water_bill', '$due_date', 'Unpaid')";

    if ($conn->query($sql) === TRUE) {
        echo "Water bill calculated successfully!";
        header("Location: water_payment_history.php?unit_number=" . urlencode($unit_number)); // Redirect after successful insertion
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close the connection
$conn->close();
?>