<?php include_once '../includes/header.php'?>
<?php

require_once __DIR__ . '/../../includes/mail_send.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize message variables
$error_message = '';
$success_message = '';

// Process message form submission
if(isset($_POST['form1'])) {
    $valid = 1;
    if(empty($_POST['subject_text'])) {
        $valid = 0;
        $error_message .= 'Subject cannot be empty<br>';
    }
    if(empty($_POST['message_text'])) {
        $valid = 0;
        $error_message .= 'Message cannot be empty<br>';
    }
    if(empty($_POST['cust_id']) || empty($_POST['payment_id'])) {
        $valid = 0;
        $error_message .= 'Missing customer or payment information<br>';
    }
    
    if($valid == 1) {
        $subject_text = strip_tags($_POST['subject_text']);
        $message_text = strip_tags($_POST['message_text']);
        $cust_id = isset($_POST['cust_id']) ? intval($_POST['cust_id']) : 0;
        $payment_id = isset($_POST['payment_id']) ? $_POST['payment_id'] : '';

        // Getting Customer Email Address
        $query = "SELECT * FROM tbl_customer WHERE cust_id=" . $cust_id;
        $result = mysqli_query($db, $query);
        
        if(!$result || mysqli_num_rows($result) == 0) {
            $error_message .= 'Customer information not found<br>';
            $valid = 0;
        } else {
            $row = mysqli_fetch_assoc($result);
            $cust_email = $row['cust_email'];

            // Prepare order details
            $order_detail = '';
            $query = "SELECT * FROM tbl_payment WHERE payment_id='" . mysqli_real_escape_string($db, $payment_id) . "'";
            $result = mysqli_query($db, $query);
            
            if(!$result || mysqli_num_rows($result) == 0) {
                $error_message .= 'Payment information not found<br>';
                $valid = 0;
            } else {
                $i = 0;
                $query = "SELECT * FROM tbl_order WHERE payment_id='" . mysqli_real_escape_string($db, $payment_id) . "'";
                $result = mysqli_query($db, $query);
                
                if($result && mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $i++;
                        $order_detail .= '
                            <br><b><u>Product Item '.$i.'</u></b><br>
                            Product Name: '.$row['product_name'].'<br>
                            Size: '.$row['size'].'<br>
                            Color: '.$row['color'].'<br>
                            Quantity: '.$row['quantity'].'<br>
                            Unit Price: '.$row['unit_price'].'<br>';
                    }
                } else {
                    $error_message .= 'No order details found for this payment<br>';
                    $valid = 0;
                }
            }
        }
        
        if($valid == 1) {
            // Prepare email content
            $message_body = '
                <html><body>
                <h3>Message: </h3>
                '.$message_text.'
                <h3>Order Details: </h3>
                '.$order_detail.'
                </body></html>';

            $mailErr = '';
            $mail_sent = send_html_mail($cust_email, $subject_text, $message_body, $mailErr);

            if ($mail_sent) {
                // Insert into database
                $query = "INSERT INTO tbl_customer_message (subject, message, order_detail, cust_id) VALUES (
                    '" . mysqli_real_escape_string($db, $subject_text) . "',
                    '" . mysqli_real_escape_string($db, $message_text) . "',
                    '" . mysqli_real_escape_string($db, $order_detail) . "',
                    " . intval($cust_id) . "
                )";
                
                if (mysqli_query($db, $query)) {
                    $_SESSION['success_message'] = 'Email sent successfully!';
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $error_message = "Database error: " . mysqli_error($db);
                }
            } else {
                $error_message = 'Failed to send email: ' . htmlspecialchars($mailErr);
            }
        }
    }
}

// Handle payment status toggle
if(isset($_GET['payment_id']) && isset($_GET['payment_task'])) {
    $payment_id = intval($_GET['payment_id']);
    $new_status = $_GET['payment_task'];
    
    if($new_status === 'Completed' || $new_status === 'Pending') {
        $query = "UPDATE tbl_payment SET payment_status = '" . mysqli_real_escape_string($db, $new_status) . "' WHERE id = " . $payment_id;
        if(mysqli_query($db, $query)) {
            $_SESSION['success_message'] = "Payment status updated to {$new_status}";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['error_message'] = 'Failed to update payment status: ' . mysqli_error($db);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        $_SESSION['error_message'] = 'Invalid status value';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle shipping status toggle
if(isset($_GET['shipping_id']) && isset($_GET['shipping_task'])) {
    $shipping_id = intval($_GET['shipping_id']);
    $new_status = $_GET['shipping_task'];
    
    if($new_status === 'Completed' || $new_status === 'Pending') {
        $query = "UPDATE tbl_payment SET shipping_status = '" . mysqli_real_escape_string($db, $new_status) . "' WHERE id = " . $shipping_id;
        if(mysqli_query($db, $query)) {
            $_SESSION['success_message'] = "Shipping status updated to {$new_status}";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['error_message'] = 'Failed to update shipping status: ' . mysqli_error($db);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        $_SESSION['error_message'] = 'Invalid status value';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle deletion of orders
if(isset($_GET['delete_id']) && isset($_SESSION['csrf_token']) && isset($_GET['token'])) {
    if($_SESSION['csrf_token'] === $_GET['token']) {
        $id = intval($_GET['delete_id']);
        
        // First get the payment_id to delete related orders
        $query = "SELECT payment_id FROM tbl_payment WHERE id = " . $id;
        $result = mysqli_query($db, $query);
        
        if($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $payment_id = $row['payment_id'];
            
            // Delete from tbl_order
            $query = "DELETE FROM tbl_order WHERE payment_id = '" . mysqli_real_escape_string($db, $payment_id) . "'";
            mysqli_query($db, $query);
            
            // Delete from tbl_payment
            $query = "DELETE FROM tbl_payment WHERE id = " . $id;
            if(mysqli_query($db, $query)) {
                $_SESSION['success_message'] = 'Order deleted successfully';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $_SESSION['error_message'] = 'Failed to delete order: ' . mysqli_error($db);
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        } else {
            $_SESSION['error_message'] = 'Order not found';
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        $_SESSION['error_message'] = 'Invalid security token';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Generate CSRF token
if(!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check for session messages and display them
if(isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear the message after retrieving it
}

if(isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear the message after retrieving it
}

require_once __DIR__ . "/../../db.php";

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    $stmt = $db->prepare("UPDATE tbl_orders SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Order status updated successfully!";
    } else {
        $_SESSION['error_msg'] = "Error updating order status!";
    }
    
    header("Location: ordermanagement.php");
    exit();
}

// Handle email sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {
    $order_id = $_POST['order_id'];
    $customer_email = $_POST['customer_email'];
    $subject = $_POST['email_subject'];
    $message = $_POST['email_message'];
    
    // Create HTML message with better formatting
    $htmlMessage = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <div style='background-color: #f8f9fa; padding: 20px; text-align: center;'>
            <h1 style='color: #333;'>Santosh Vastralay</h1>
        </div>
        <div style='padding: 20px; background-color: #ffffff;'>
            " . nl2br(htmlspecialchars($message)) . "
        </div>
        <div style='background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666;'>
            <p>This is an automated message, please do not reply directly to this email.</p>
        </div>
    </div>";
    
    $mailErr = '';
    if (send_html_mail($customer_email, $subject, $htmlMessage, $mailErr)) {
        $_SESSION['success_msg'] = "Email sent successfully to customer!";
    } else {
        $_SESSION['error_msg'] = 'Failed to send email: ' . htmlspecialchars($mailErr);
    }
    
    header("Location: ordermanagement.php");
    exit();
}

// Get all orders with customer details and order items
$query = "
    SELECT 
        o.id,
        o.user_id,
        o.total_amount,
        o.status,
        o.shipping_address,
        o.created_at,
        o.updated_at,
        u.name as customer_name,
        u.email as customer_email,
        u.phone as customer_phone
    FROM tbl_orders o
    JOIN tbl_users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
";

$orders = $db->query($query);
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Order Management</h1>
    </div>

    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
            <?php 
            echo $_SESSION['success_msg'];
            unset($_SESSION['success_msg']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
            <?php 
            echo $_SESSION['error_msg'];
            unset($_SESSION['error_msg']);
            ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100 border-2 border-gray-400">
                    <tr>
                    <th class="px-1 py-3 text-left  font-medium text-gray-900 uppercase tracking-wider">Order ID</th>
                    <th class="px-6 py-3 text-left  font-medium text-gray-900 uppercase tracking-wider">Customer Details</th>

                        <th class="px-6 py-3 text-left  font-medium text-gray-900 uppercase tracking-wider">Order Items</th> 
                        <th class="px-6 py-3 text-left  font-medium text-gray-900 uppercase tracking-wider">Total Amount</th>
                        <th class="px-1 py-3 text-left  font-medium text-gray-900 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left  font-medium text-gray-900 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left  font-medium text-gray-900 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while ($order = $orders->fetch_assoc()): ?>
                <?php
                        // Get order items for this order
                        $items_query = "
                            SELECT 
                                oi.*,
                                p.p_name,
                                p.p_featured_photo
                            FROM tbl_order_items oi
                            JOIN tbl_product p ON oi.product_id = p.p_id
                            WHERE oi.order_id = ?
                        ";
                        $items_stmt = $db->prepare($items_query);
                        $items_stmt->bind_param("i", $order['id']);
                        $items_stmt->execute();
                        $items_result = $items_stmt->get_result();
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                #<?php echo $order['id']; ?>
                            </td>
                            <td class="px-2 py-4">
                                <div class="text-sm">
                                    <p class="font-medium"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                                    <p class="text-gray-500"><?php echo htmlspecialchars($order['customer_email']); ?></p>
                                    <p class="text-gray-500"><?php echo htmlspecialchars($order['customer_phone']); ?></p>
                                    <details class="mt-2">
                                        <summary class="text-blue-600 cursor-pointer">Shipping Address</summary>
                                        <p class="text-gray-500 mt-1"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                                    </details>
                                        </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">
                                    <?php while ($item = $items_result->fetch_assoc()): ?>
                                        <div class="flex items-center mb-2">
                                            <img src="/santoshvas/Ecommerce/admin/uploadimgs/<?php echo htmlspecialchars($item['p_featured_photo']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['p_name']); ?>"
                                                 class="w-15 h-10 object-cover rounded mr-2">
                                            <div>
                                                <p class="font-medium"><?php echo htmlspecialchars($item['p_name']); ?></p>
                                                <p class="text-gray-500">Qty: <?php echo $item['quantity']; ?> × ₹<?php echo number_format($item['price'], 2); ?></p>
                                            </div>
                                            </div>
                                    <?php endwhile; ?>
                            </div>
                        </td>
                            <td class="pl-8 py-4 whitespace-nowrap">
                                ₹<?php echo number_format($order['total_amount'], 2); ?>
                            </td>
                            <td class="px-1 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?php
                                    switch($order['status']) {
                                        case 'completed':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'cancelled':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        case 'processing':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        default:
                                            echo 'bg-yellow-100 text-yellow-800';
                                    }
                                    ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <form method="post" class="inline-block">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="new_status" class="text-sm border rounded px-2 py-1 mr-2">
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                        Update
                                    </button>
                                </form>
                                <button onclick="openEmailModal(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['customer_email']); ?>', '<?php echo $order['status']; ?>', '<?php echo htmlspecialchars($order['customer_name']); ?>', <?php echo $order['total_amount']; ?>)"
                                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm ml-2">
                                    <i class="fas fa-envelope mr-1"></i> Email
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Improved Email Modal -->
<div id="emailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
        <div class="absolute top-0 right-0 pt-4 pr-4">
            <button onclick="closeEmailModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="mt-3">
            <div class="flex items-center mb-4">
                <i class="fas fa-envelope text-blue-500 text-2xl mr-3"></i>
                <h3 class="text-xl font-medium leading-6 text-gray-900">Send Email to Customer</h3>
            </div>
            
            <form method="post" id="emailForm" class="space-y-4" onsubmit="return validateEmailForm()">
                <input type="hidden" name="order_id" id="email_order_id">
                <input type="hidden" name="customer_email" id="email_customer_email">
                
                <div class="space-y-2">
                    <label class="block text-gray-700 text-sm font-bold" for="email_subject">
                        Subject
                    </label>
                    <input type="text" id="email_subject" name="email_subject" 
                           class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-gray-700 text-sm font-bold" for="email_message">
                        Message
                    </label>
                    <textarea id="email_message" name="email_message" rows="8"
                            class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required></textarea>
                </div>
                
                <div class="flex items-center justify-between pt-4 border-t">
                    <button type="button" onclick="insertTemplate()"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded focus:outline-none focus:shadow-outline flex items-center">
                        <i class="fas fa-file-alt mr-2"></i> Insert Template
                    </button>
                    
                    <div class="flex gap-3">
                        <button type="button" onclick="closeEmailModal()"
                                class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Cancel
                        </button>
                        <button type="submit" name="send_email"
                                class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded focus:outline-none focus:shadow-outline flex items-center">
                            <i class="fas fa-paper-plane mr-2"></i> Send Email
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Enhanced JavaScript -->
<script>
const emailTemplates = {
    pending: {
        subject: "Order #{orderId} Received",
        message: `Dear {customerName},

Thank you for your order #{orderId}. We have received your order and will begin processing it shortly.

Order Details:
- Order Number: #{orderId}
- Status: Pending
- Total Amount: ₹{totalAmount}

We will notify you once your order has been processed.

Best regards,
Santosh Vastralay`
    },
    processing: {
        subject: "Order #{orderId} is Being Processed",
        message: `Dear {customerName},

Your order #{orderId} is now being processed. We're preparing your items for shipment.

Order Details:
- Order Number: #{orderId}
- Status: Processing
- Total Amount: ₹{totalAmount}

We will send you another notification once your order has been shipped.

Best regards,
Santosh Vastralay`
    },
    completed: {
        subject: "Order #{orderId} Completed",
        message: `Dear {customerName},

Great news! Your order #{orderId} has been completed and shipped.

Order Details:
- Order Number: #{orderId}
- Status: Completed
- Total Amount: ₹{totalAmount}

Thank you for shopping with us. We hope you enjoy your purchase!

Best regards,
Santosh Vastralay`
    },
    cancelled: {
        subject: "Order #{orderId} Cancelled",
        message: `Dear {customerName},

Your order #{orderId} has been cancelled as requested.

Order Details:
- Order Number: #{orderId}
- Status: Cancelled
- Total Amount: ₹{totalAmount}

If you have any questions about this cancellation, please don't hesitate to contact us.

Best regards,
Santosh Vastralay`
    }
};

let currentOrderData = null;

function openEmailModal(orderId, customerEmail, orderStatus, customerName, totalAmount) {
    currentOrderData = {
        orderId,
        customerEmail,
        orderStatus,
        customerName,
        totalAmount: parseFloat(totalAmount).toFixed(2),
        storeName: 'Santosh Vastralay'
    };
    
    const modal = document.getElementById('emailModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.getElementById('email_order_id').value = orderId;
        document.getElementById('email_customer_email').value = customerEmail;
        
        insertTemplate();
    } else {
        console.error('Email modal element not found');
    }
}

function insertTemplate() {
    if (!currentOrderData) {
        console.error('No order data available');
        return;
    }
    
    const template = emailTemplates[currentOrderData.orderStatus.toLowerCase()] || emailTemplates.pending;
    
    let subject = template.subject.replace('{orderId}', currentOrderData.orderId);
    let message = template.message
        .replace(/{orderId}/g, currentOrderData.orderId)
        .replace(/{customerName}/g, currentOrderData.customerName)
        .replace(/{totalAmount}/g, currentOrderData.totalAmount)
        .replace(/{storeName}/g, currentOrderData.storeName);
    
    document.getElementById('email_subject').value = subject;
    document.getElementById('email_message').value = message;
}

function closeEmailModal() {
    const modal = document.getElementById('emailModal');
    if (modal) {
        modal.classList.add('hidden');
        currentOrderData = null;
    }
}

function validateEmailForm() {
    const subject = document.getElementById('email_subject').value.trim();
    const message = document.getElementById('email_message').value.trim();
    
    if (!subject || !message) {
        alert('Please fill in both subject and message fields.');
        return false;
    }
    return true;
}

// Close modal when clicking outside
document.getElementById('emailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEmailModal();
    }
});
</script>

<?php include_once "../includes/footer.php" ?>