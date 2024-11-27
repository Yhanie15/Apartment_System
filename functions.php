<?php
function getRentStatus($pdo, $unit_number, $due_date, $move_in_date) {
    try {
        // Check if rent_payments table exists
        $table_check = $pdo->query("SELECT 1 FROM rent_payments LIMIT 1");
        if ($table_check === false) {
            // Table does not exist, return 'Unpaid' by default
            return 'Unpaid';
        }

        // Prepare the SQL query to get payment status
        $stmt = $pdo->prepare("SELECT payment_date FROM rent_payments WHERE unit_number = ? AND payment_date <= ?");
        $stmt->execute([$unit_number, $due_date]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        return $payment ? 'Paid' : 'Unpaid';
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
