<?php
require_once __DIR__ . "/../db.php";

// Include header
include_once __DIR__ . "/../user/includes/header.php";

// Include cart functions
include_once __DIR__ . "/cart-functions.php";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $product_id => $quantity) {
            $result = updateCartQuantity($db, $product_id, $quantity);
            if (!$result['success']) {
                $error_message = $result['message'];
                break;
            }
        }
    }
}

// Handle remove item
if (isset($_GET['remove'])) {
    $result = removeFromCart($_GET['remove']);
    if (!$result['success']) {
        $error_message = $result['message'];
    }
}

// Get cart total
$cart_total = getCartTotal();
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Shopping Cart</h1>
    
    <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($_SESSION['cart'])): ?>
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <i class="fas fa-shopping-cart fa-4x text-gray-400 mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-700 mb-2">Your cart is empty</h2>
            <p class="text-gray-600 mb-6">Add products to your cart to view them here.</p>
            <a href="landingpage.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg">
                Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <form method="post" class="mb-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 px-4 text-left">#</th>
                            <th class="py-3 px-4 text-left">Product</th>
                            <th class="py-3 px-4 text-left">Price</th>
                            <th class="py-3 px-4 text-left">Quantity</th>
                            <th class="py-3 px-4 text-right">Total</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $key => $item): ?>
                            <tr class="border-t border-gray-200">
                                <td class="py-4 px-4"><?php echo $key+1; ?></td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center">
                                        <img src="<?php echo BASE_URL; ?>admin/uploadimgs/<?php echo htmlspecialchars($item['photo']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="w-16 h-16 object-cover rounded mr-4">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </div>
                                </td>
                                <td class="py-4 px-4">₹<?php echo number_format($item['price'], 2); ?></td>
                                <td class="py-4 px-4">
                                    <input type="number" 
                                           class="w-20 p-2 border rounded" 
                                           min="1" 
                                           name="quantity[<?php echo $item['p_id']; ?>]" 
                                           value="<?php echo $item['quantity']; ?>">
                                </td>
                                <td class="py-4 px-4 text-right">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td class="py-4 px-4 text-center">
                                    <a href="cart.php?remove=<?php echo $item['p_id']; ?>" 
                                       class="text-red-500 hover:text-red-700"
                                       onclick="return confirm('Are you sure you want to remove this item?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="bg-gray-50 font-bold">
                            <td colspan="4" class="py-4 px-4">Total</td>
                            <td class="py-4 px-4 text-right">₹<?php echo number_format($cart_total, 2); ?></td>
                            <td class="py-4 px-4"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap justify-between gap-4">
                <div class="flex gap-2">
                    <button type="submit" name="update_cart" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg">
                        Update Cart
                    </button>
                    <a href="landingpage.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-6 rounded-lg inline-flex items-center">
                        Continue Shopping
                    </a>
                </div>
                <a href="checkout.php" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg inline-flex items-center">
                    Proceed to Checkout <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . "/../user/includes/footer.php"; ?>