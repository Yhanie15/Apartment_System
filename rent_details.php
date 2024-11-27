<?php
// Include your database connection
include 'db.php';

// Fetch the room number from GET or POST
$unit_number = isset($_GET['unit_number']) ? $_GET['unit_number'] : null;

if ($unit_number === null) {
    echo "No room number provided.";
    exit;
}

// Function to fetch the move-in date of the first tenant
function fetchMoveInDate($pdo, $unit_number) {
    $stmt = $pdo->prepare("SELECT MIN(move_in_date) AS move_in_date FROM tenant_account WHERE unit_number = ?");
    $stmt->execute([$unit_number]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['move_in_date'];
}

// Function to fetch rent balances per month for a specific room number
function fetchRentBalances($pdo, $unit_number, $move_in_date) {
    $currentDate = new DateTime();
    $moveInDate = new DateTime($move_in_date);
    $rentBalances = [];

    // Set the initial due date as exactly one month after the move-in date
    $moveInDate->modify('+1 month');
    $dueDate = clone $moveInDate; // Set the initial due date

    // Calculate the due dates from the adjusted move-in date to the current date
    while ($dueDate <= $currentDate) {
        $month = $dueDate->format('Y-m'); // Due date is based on the month

        $stmt = $pdo->prepare("SELECT 
            r.rent AS monthly_rate, 
            IFNULL(SUM(rp.amount_paid), 0) AS total_paid
        FROM rooms r
        LEFT JOIN rent_payments rp ON r.unit_number = rp.unit_number 
        AND DATE_FORMAT(rp.bill_date, '%Y-%m') = ? 
        WHERE r.unit_number = ?");
        
        $stmt->execute([$month, $unit_number]);
        $paymentData = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalPaid = $paymentData['total_paid'] ?? 0;
        $monthlyRate = $paymentData['monthly_rate'] ?? 0;
        $totalBalance = $monthlyRate - $totalPaid;

        // Only add to rentBalances if total balance is greater than 0
        if ($totalBalance > 0) {
            $rentBalances[] = [
                'monthly_rate' => $monthlyRate,
                'bill_date' => $dueDate->format('Y-m-d'),
                'due_date' => $dueDate->format('Y-m-d'),
                'total_balance' => $totalBalance // Directly store total balance
            ];
        }

        // Move to the next due date (1 month later)
        $dueDate->modify('+1 month');
    }

    return $rentBalances;
}

// Function to fetch payment history for a specific room number
function fetchPaymentHistory($pdo, $unit_number) {
    $stmt = $pdo->prepare("SELECT * FROM rent_payments WHERE unit_number = ? ORDER BY payment_date DESC");
    $stmt->execute([$unit_number]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $month = $_POST['month']; // Expects 'YYYY-MM' format
    $paymentDate = $_POST['payment_date'];
    $amountPaid = $_POST['amount_paid'];
    $unit_number = $_POST['unit_number'];

    // Create a valid bill date format for insertion
    $bill_date = $month . '-01'; // Ensuring the format is 'YYYY-MM-DD'

    // Insert payment into rent_payments table
    $stmt = $pdo->prepare("INSERT INTO rent_payments (unit_number, bill_date, amount_paid, payment_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$unit_number, $bill_date, $amountPaid, $paymentDate]);

    // Check if the payment was inserted successfully
    if ($stmt->rowCount() > 0) {
        // Refresh the rent balances after payment
        header("Location: " . $_SERVER['PHP_SELF'] . "?unit_number=" . urlencode($unit_number));
        exit;
    } else {
        echo "Error inserting payment.";
    }
}

// Fetch the move-in date, rent balances, and payment history
$moveInDate = fetchMoveInDate($pdo, $unit_number);
$rentBalances = fetchRentBalances($pdo, $unit_number, $moveInDate);
$paymentHistory = fetchPaymentHistory($pdo, $unit_number); // Fetch payment history here
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Details for Room <?php echo htmlspecialchars($unit_number); ?></title>
    <link rel="stylesheet" href="JRSLCSS/rent_details.css"> 
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <a href="rent.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Rent Page</a>
        <h2>Rent Details for Unit <?php echo htmlspecialchars($unit_number); ?></h2>

        <!-- Quick Search -->
        <div class="search-section">
            <input type="text" placeholder="Quick Search" id="searchRentDetails">
            <button onclick="openAddPaymentModal()" class="add-payment-btn">+ Add Payment</button>
        </div>

        <!-- Rent Balances Table -->
        <h3>Rent Balances</h3>
        <table class="rent-balances-table">
            <thead>
                <tr>
                    <th>Monthly Bill</th>
                    <th>Month</th>
                    <th>Due Date</th>
                 
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rentBalances as $balance): ?>
                    <tr>
                        <td>PHP <?php echo number_format($balance['monthly_rate'], 2); ?></td>
                        <td><?php echo date('F Y', strtotime($balance['bill_date'])); ?></td>
                        <td><?php echo date('F j, Y', strtotime($balance['due_date'])); ?></td>
                       
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Total Balance Section -->
        <div class="total-balance" style="text-align:left; margin-top:20px;">
            <span>Total Balance:</span> PHP <span id="totalBalance"><?php echo number_format(array_sum(array_column($rentBalances, 'total_balance')), 2); ?></span>
        </div>

        <!-- Payment History Table -->
        <h3>Payment History</h3>
        <table class="payment-history-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date Time Added</th>
                    <th>Month Of</th>
                    <th>Amount Paid</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paymentHistory as $index => $payment): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                        <td><?php echo isset($payment['bill_date']) ? date('F Y', strtotime($payment['bill_date'])) : 'N/A'; ?></td>
                        <td>PHP <?php echo number_format($payment['amount_paid'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Add Payment Modal -->
        <div id="addPaymentModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeAddPaymentModal()">&times;</span>
                <h3>Add Payment for Unit <?php echo htmlspecialchars($unit_number); ?></h3>
                <form id="addPaymentForm" method="POST">
                    <input type="hidden" name="unit_number" value="<?php echo htmlspecialchars($unit_number); ?>">

                    <!-- Month Picker -->
                    <label for="month">Month of:</label>
                    <input type="month" name="month" required>

                    <!-- Payment Date -->
                    <label for="payment_date"><br>Date of Payment:</label>
                    <input type="date" name="payment_date" required>

                    <!-- Amount Paid -->
                    <label for="amount_paid"><br>Amount Paid:</label>
                    <input type="number" name="amount_paid" required><br>

                    <button type="submit" class="submit-btn">Submit</button>
                    <button type="button" class="cancel-btn" onclick="closeAddPaymentModal()">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddPaymentModal() {
            document.getElementById('addPaymentModal').style.display = 'flex';
        }

        function closeAddPaymentModal() {
            document.getElementById('addPaymentModal').style.display = 'none';
        }

        window.onload = function() {
            closeAddPaymentModal();
        };
    </script>

</body>
</html>
