<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'apartment_management');

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unit_number = $_GET['unit_number']; // Get unit number from URL
    $month = $_POST['month']; // Month for which payment is made in YYYY-MM format
    $amount_paid = $_POST['amount_paid']; // Payment amount
    $payment_date = $_POST['payment_date']; // Payment date

    // Convert the month (YYYY-MM) to word format (e.g., "January 2024")
    $month_in_word = date("F Y", strtotime($month . "-01"));

    // Insert payment into the electricity_payment_history table with the month in word format
    $insert_payment_sql = "
        INSERT INTO electricity_payment_history (unit_number, month_of, amount_paid, payment_date)
        VALUES ('$unit_number', '$month_in_word', '$amount_paid', '$payment_date')";

    if ($conn->query($insert_payment_sql) === TRUE) {
        
        // Fetch all unpaid months and their total payments
        $unpaid_months_sql = "
            SELECT ec.calculation_month, ec.electricity_bill, IFNULL(SUM(eph.amount_paid), 0) AS total_paid
            FROM electricity_calculations ec
            LEFT JOIN electricity_payment_history eph 
                ON ec.unit_number = eph.unit_number 
                AND ec.calculation_month = eph.month_of
            WHERE ec.unit_number = '$unit_number'
            GROUP BY ec.calculation_month";

        $unpaid_months_result = $conn->query($unpaid_months_sql);

        // Loop through all months and update statuses accordingly
        while ($unpaid_month_row = $unpaid_months_result->fetch_assoc()) {
            $calculation_month = $unpaid_month_row['calculation_month'];
            $electricity_bill = $unpaid_month_row['electricity_bill'];
            $total_paid = $unpaid_month_row['total_paid'];

            // Calculate remaining balance for this month
            $remaining_balance = $electricity_bill - $total_paid;

            // Determine the current status for this month
            if ($remaining_balance <= 0) {
                // Update to 'Paid' if no remaining balance
                $update_status_sql = "
                    UPDATE electricity_calculations 
                    SET current_status = 'Paid', last_payment_date = '$payment_date' 
                    WHERE unit_number = '$unit_number' 
                    AND calculation_month = '$calculation_month'";
            } else {
                // Update to 'Partial Pay' if there is remaining balance
                $update_status_sql = "
                    UPDATE electricity_calculations 
                    SET current_status = 'Partial Pay', last_payment_date = '$payment_date' 
                    WHERE unit_number = '$unit_number' 
                    AND calculation_month = '$calculation_month'";
            }

            // Execute the update query for each unpaid month
            $conn->query($update_status_sql);
        }

        // Redirect back to the payment history page after successful payment and update
        header("Location: electricity_payment_history.php?unit_number=" . urlencode($unit_number));
        exit();
    } else {
        echo "Error adding payment: " . $conn->error;
    }
}

// Close the connection
$conn->close();
?>
