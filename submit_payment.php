<?php
// Include database connection
include 'db.php'; 

// Get POST data from the form
$unit_number = $_POST['unit_number'];
$month = $_POST['month'];
$payment_date = $_POST['payment_date'];
$amount_paid = $_POST['amount_paid'];

// Validate inputs
if (!empty($unit_number) && !empty($month) && !empty($payment_date) && !empty($amount_paid)) {
    try {
        // Insert the payment into the rent_payments table
        $stmt = $pdo->prepare("INSERT INTO rent_payments (unit_number, payment_date, month, amount_paid, status) VALUES (?, ?, ?, ?, 'Paid')");
        $stmt->execute([$unit_number, $payment_date, $month, $amount_paid]);

        // Fetch the last inserted payment's ID
        $payment_id = $pdo->lastInsertId();

        // Return the payment details as a JSON response
        echo json_encode([
            'success' => true,
            'payment' => [
                'id' => $payment_id,
                'payment_date' => $payment_date,
                'month' => $month,
                'amount_paid' => $amount_paid
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'All fields are required.'
    ]);
}
?>
