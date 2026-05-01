<?php
require_once __DIR__ . "/../db.php";

// Include header
include_once __DIR__ . "/includes/header.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirectWithMessage('login.php', 'Please login to view your orders', 'error');
}

// Get user's orders
$user_id = $_SESSION['user_id'];
$orders = [];

try {
    // Backward-compatible check for payment_status column
    $hasPaymentStatus = false;
    $colStmt = $db->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='tbl_orders' AND COLUMN_NAME='payment_status' LIMIT 1");
    if ($colStmt) {
        $colStmt->execute();
        $hasPaymentStatus = $colStmt->get_result()->num_rows > 0;
        $colStmt->close();
    }

    // Prepare the query
    $stmt = $db->prepare("
        SELECT o.*, 
               COUNT(oi.id) as item_count,
               SUM(oi.quantity * oi.price) as total_amount
        FROM tbl_orders o
        LEFT JOIN tbl_order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    
    if (!$stmt) {
        throw new Exception("Database error: " . $db->error);
    }
    
    // Bind parameters and execute
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch all orders
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    $stmt->close();
} catch (Exception $e) {
    $error_message = "Error fetching orders: " . $e->getMessage();
}
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">My Orders</h1>
    
    <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($orders)): ?>
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <i class="fas fa-box-open fa-4x text-gray-400 mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-700 mb-2">No orders found</h2>
            <p class="text-gray-600 mb-6">You haven't placed any orders yet.</p>
            <a href="../Home/landingpage.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg">
                Start Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left">Order ID</th>
                        <th class="py-3 px-4 text-left">Date</th>
                        <th class="py-3 px-4 text-left">Items</th>
                        <th class="py-3 px-4 text-right">Total</th>
                        <?php if (!empty($hasPaymentStatus)): ?>
                            <th class="py-3 px-4 text-left">Payment</th>
                        <?php endif; ?>
                        <th class="py-3 px-4 text-left">Status</th>
                        <th class="py-3 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr class="border-t border-gray-200">
                            <td class="py-4 px-4">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td class="py-4 px-4"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td class="py-4 px-4"><?php echo $order['item_count']; ?> items</td>
                            <td class="py-4 px-4 text-right">₹<?php echo number_format($order['total_amount'], 2); ?></td>
                            <?php if (!empty($hasPaymentStatus)): ?>
                                <?php
                                    $ps = $order['payment_status'] ?? 'unpaid';
                                    $pClass = 'bg-yellow-100 text-yellow-800';
                                    if ($ps === 'paid') $pClass = 'bg-green-100 text-green-800';
                                    if ($ps === 'refunded') $pClass = 'bg-gray-200 text-gray-800';
                                ?>
                                <td class="py-4 px-4">
                                    <span class="px-2 py-1 rounded-full text-sm <?php echo $pClass; ?>">
                                        <?php echo strtoupper($ps); ?>
                                    </span>
                                </td>
                            <?php endif; ?>
                            <td class="py-4 px-4">
                                <span class="px-2 py-1 rounded-full text-sm 
                                    <?php 
                                    switch($order['status']) {
                                        case 'pending':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'processing':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'shipped':
                                            echo 'bg-purple-100 text-purple-800';
                                            break;
                                        case 'delivered':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'cancelled':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                                   class="text-blue-500 hover:text-blue-700 mr-3">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <?php if (!empty($hasPaymentStatus) && ($order['payment_status'] ?? 'unpaid') !== 'paid' && $order['status'] !== 'cancelled'): ?>
                                    <a href="<?php echo htmlspecialchars(getAbsoluteUrl('Home/payment.php?order_id=' . $order['id'])); ?>"
                                       class="text-green-600 hover:text-green-700">
                                        <i class="fas fa-credit-card"></i> Pay Now
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . "/includes/footer.php"; ?> 