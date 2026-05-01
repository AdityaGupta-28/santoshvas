<?php
require_once __DIR__ . "/../db.php";

// Include header
include_once __DIR__ . "/../user/includes/header.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    redirectWithMessage('user/login.php', 'Please login to continue payment', 'error');
}

// Ensure payment columns exist
$hasPaymentColumns = false;
$colStmt = $db->prepare("SELECT COUNT(*) as c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='tbl_orders' AND COLUMN_NAME IN ('payment_status','payment_method','payment_txn_id','paid_amount','paid_at','payment_notes')");
if ($colStmt) {
    $colStmt->execute();
    $cRow = $colStmt->get_result()->fetch_assoc();
    $hasPaymentColumns = isset($cRow['c']) && intval($cRow['c']) === 6;
    $colStmt->close();
}

if (!$hasPaymentColumns) {
    redirectWithMessage('user/orders.php', 'Payment system is not configured in DB yet. Please run the SQL update.', 'error');
}

$order_id = intval($_GET['order_id'] ?? 0);
if ($order_id <= 0) {
    redirectWithMessage('user/orders.php', 'Invalid order id', 'error');
}

// Load order (must belong to current user)
$stmt = $db->prepare("SELECT id, user_id, total_amount, status, payment_status, payment_method, payment_txn_id, paid_amount, paid_at, shipping_address, created_at FROM tbl_orders WHERE id = ? AND user_id = ? LIMIT 1");
if (!$stmt) {
    die("Database error: " . htmlspecialchars($db->error));
}
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    redirectWithMessage('user/orders.php', 'Order not found', 'error');
}

// Prevent paying for cancelled orders
if ($order['status'] === 'cancelled') {
    redirectWithMessage('user/order-details.php?id=' . $order_id, 'This order is cancelled and cannot be paid.', 'error');
}

if (($order['payment_status'] ?? 'unpaid') === 'paid') {
    redirectWithMessage('user/order-details.php?id=' . $order_id, 'This order is already paid.', 'success');
}

// Handle payment submission (simulated/manual)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_now'])) {
    if (
        !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token'])
    ) {
        $error_message = "Invalid request token. Please refresh and try again.";
    } else {
    $method = strtoupper(trim($_POST['payment_method'] ?? ''));
    $allowedMethods = ['COD', 'UPI', 'CARD', 'NETBANKING', 'WALLET', 'BANK_TRANSFER'];
    if (!in_array($method, $allowedMethods, true)) {
        $error_message = "Please select a valid payment method.";
    } else {
        $txn = trim($_POST['payment_txn_id'] ?? '');
        $notes = trim($_POST['payment_notes'] ?? '');

        // COD: keep unpaid until delivered, but store method
        if ($method === 'COD') {
            $stmt = $db->prepare("UPDATE tbl_orders SET payment_method = ?, payment_status = 'unpaid', payment_txn_id = NULL, paid_amount = NULL, paid_at = NULL, payment_notes = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssii", $method, $notes, $order_id, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            redirectWithMessage('user/order-details.php?id=' . $order_id, 'COD selected. You will pay on delivery.', 'success');
        }

        // Online methods: require txn id (simple validation)
        if ($txn === '') {
            $error_message = "Transaction ID is required for online payment.";
        } else {
            $amount = floatval($order['total_amount']);
            $paidAt = date('Y-m-d H:i:s');

            $stmt = $db->prepare("UPDATE tbl_orders SET payment_method = ?, payment_status = 'paid', payment_txn_id = ?, paid_amount = ?, paid_at = ?, payment_notes = ?, status = CASE WHEN status = 'pending' THEN 'processing' ELSE status END, updated_at = NOW() WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssdssii", $method, $txn, $amount, $paidAt, $notes, $order_id, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            redirectWithMessage('user/order-details.php?id=' . $order_id, 'Payment successful!', 'success');
        }
    }
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-2">Payment</h1>
    <p class="text-gray-600 mb-6">Order #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></p>

    <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Choose payment method</h2>

            <form method="post" class="space-y-4" id="paymentForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="border rounded-lg p-4 cursor-pointer hover:border-blue-500 flex items-start gap-3">
                        <input type="radio" name="payment_method" value="COD" class="mt-1" checked>
                        <div>
                            <p class="font-semibold">Cash on Delivery (COD)</p>
                            <p class="text-sm text-gray-600">Pay when you receive the order.</p>
                        </div>
                    </label>
                    <label class="border rounded-lg p-4 cursor-pointer hover:border-blue-500 flex items-start gap-3">
                        <input type="radio" name="payment_method" value="UPI" class="mt-1">
                        <div>
                            <p class="font-semibold">UPI</p>
                            <p class="text-sm text-gray-600">Enter transaction reference after paying.</p>
                        </div>
                    </label>
                    <label class="border rounded-lg p-4 cursor-pointer hover:border-blue-500 flex items-start gap-3">
                        <input type="radio" name="payment_method" value="CARD" class="mt-1">
                        <div>
                            <p class="font-semibold">Card</p>
                            <p class="text-sm text-gray-600">Enter transaction reference after paying.</p>
                        </div>
                    </label>
                    <label class="border rounded-lg p-4 cursor-pointer hover:border-blue-500 flex items-start gap-3">
                        <input type="radio" name="payment_method" value="NETBANKING" class="mt-1">
                        <div>
                            <p class="font-semibold">Netbanking</p>
                            <p class="text-sm text-gray-600">Enter transaction reference after paying.</p>
                        </div>
                    </label>
                    <label class="border rounded-lg p-4 cursor-pointer hover:border-blue-500 flex items-start gap-3">
                        <input type="radio" name="payment_method" value="WALLET" class="mt-1">
                        <div>
                            <p class="font-semibold">Wallet</p>
                            <p class="text-sm text-gray-600">Enter transaction reference after paying.</p>
                        </div>
                    </label>
                    <label class="border rounded-lg p-4 cursor-pointer hover:border-blue-500 flex items-start gap-3">
                        <input type="radio" name="payment_method" value="BANK_TRANSFER" class="mt-1">
                        <div>
                            <p class="font-semibold">Bank Transfer</p>
                            <p class="text-sm text-gray-600">Enter bank reference after paying.</p>
                        </div>
                    </label>
                </div>

                <div class="space-y-2">
                    <label class="block text-gray-700 text-sm font-bold" for="payment_txn_id">Transaction ID (for Online)</label>
                    <input type="text" id="payment_txn_id" name="payment_txn_id"
                           class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g. UPI Ref / Razorpay Payment ID">
                    <p class="text-xs text-gray-500">Leave empty for COD.</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-gray-700 text-sm font-bold" for="payment_notes">Notes (optional)</label>
                    <textarea id="payment_notes" name="payment_notes" rows="3"
                              class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Any note for payment"></textarea>
                </div>

                <div class="flex items-center justify-between pt-4 border-t">
                    <a href="<?php echo htmlspecialchars(getAbsoluteUrl('user/order-details.php?id=' . $order_id)); ?>"
                       class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded">
                        Back
                    </a>
                    <button type="submit" name="pay_now"
                            class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded">
                        Confirm & Continue
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Order summary</h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Amount</span>
                    <span class="font-semibold">₹<?php echo number_format((float)$order['total_amount'], 2); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Order status</span>
                    <span class="font-semibold"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Payment status</span>
                    <span class="font-semibold"><?php echo htmlspecialchars(strtoupper($order['payment_status'] ?? 'UNPAID')); ?></span>
                </div>
                <div class="pt-3 border-t">
                    <p class="text-gray-600 font-semibold mb-1">Shipping address</p>
                    <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('paymentForm');
    if (!form) {
        return;
    }

    const methodInputs = form.querySelectorAll('input[name="payment_method"]');
    const txnField = document.getElementById('payment_txn_id');

    function updateTxnFieldState() {
        const selected = form.querySelector('input[name="payment_method"]:checked');
        const method = selected ? selected.value : 'COD';
        const isOnlineMethod = method !== 'COD';
        txnField.required = isOnlineMethod;
        txnField.disabled = !isOnlineMethod;
        if (!isOnlineMethod) {
            txnField.value = '';
        }
    }

    methodInputs.forEach(function (input) {
        input.addEventListener('change', updateTxnFieldState);
    });

    updateTxnFieldState();
});
</script>

<?php include_once __DIR__ . "/../user/includes/footer.php"; ?>

