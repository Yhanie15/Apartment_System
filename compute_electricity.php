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
    $electricity_rate = $_POST['electricity_rate'];
    $electricity_consumption = $_POST['electricity_consumption'];
    $meter_read_date = $_POST['meter_read_date'];
    $calculation_month = $_POST['calculation_month'];  // Retrieve the selected month

    // Convert the month from "YYYY-MM" to "Month Year" format (e.g., "August 2024")
    $dateObj = DateTime::createFromFormat('Y-m', $calculation_month);
    $formatted_month = $dateObj->format('F Y');  // "F" for full month name, "Y" for year

    // Calculate the electricity bill
    $electricity_bill = $electricity_rate * $electricity_consumption;

    // Calculate the due date (7 days after the meter_read_date)
    $meter_read_date_obj = new DateTime($meter_read_date);
    $meter_read_date_obj->modify('+7 days');
    $due_date = $meter_read_date_obj->format('Y-m-d');

    // Insert the data into electricity_calculations with the formatted month and due date
    $sql = "INSERT INTO electricity_calculations (unit_number, electricity_rate, electricity_consumption, meter_read_date, calculation_month, electricity_bill, due_date, current_status)
            VALUES ('$unit_number', '$electricity_rate', '$electricity_consumption', '$meter_read_date', '$formatted_month', '$electricity_bill', '$due_date', 'Unpaid')";

    if ($conn->query($sql) === TRUE) {
        echo "Electricity bill calculated successfully!";
        header("Location: electricity_payment_history.php?unit_number=" . urlencode($unit_number)); // Redirect after successful insertion
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close the connection
$conn->close();
?>