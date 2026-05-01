<?php
// Start session
session_start();

// Set the page title
$title = "Product Details - Santosh Vastralay";

// Include the header (assumes $db is defined here)
include_once "../user/includes/header.php";

// Check database connection
if (!isset($db) || !$db) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get product ID from URL
$p_id = isset($_GET['p_id']) ? (int)$_GET['p_id'] : 0;

if ($p_id <= 0) {
    header('Location: landingpage.php');
    exit;
}

// Get product details
$product_query = "SELECT p.*, e.ecat_name, m.mcat_name, m.mcat_id, t.tcat_name, t.tcat_id
                  FROM tbl_product p
                  LEFT JOIN tbl_end_category e ON p.ecat_id = e.ecat_id
                  LEFT JOIN tbl_mid_category m ON e.mcat_id = m.mcat_id
                  LEFT JOIN tbl_top_category t ON m.tcat_id = t.tcat_id
                  WHERE p.p_id = ? AND p.p_is_active = 1";
$stmt = mysqli_prepare($db, $product_query);
mysqli_stmt_bind_param($stmt, "i", $p_id);
mysqli_stmt_execute($stmt);
$product_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($product_result) == 0) {
    header('Location: landingpage.php');
    exit;
}

$product = mysqli_fetch_assoc($product_result);
mysqli_stmt_close($stmt);

// Get additional product images
$images_query = "SELECT * FROM tbl_product_photo WHERE p_id = ? ORDER BY pp_id";
$stmt = mysqli_prepare($db, $images_query);
mysqli_stmt_bind_param($stmt, "i", $p_id);
mysqli_stmt_execute($stmt);
$images_result = mysqli_stmt_get_result($stmt);
$product_images = [];

$product_images[] = [
    'photo' => $product['p_featured_photo'],
    'is_featured' => true
];

while ($image = mysqli_fetch_assoc($images_result)) {
    $product_images[] = [
        'photo' => $image['photo'],
        'is_featured' => false
    ];
}
mysqli_stmt_close($stmt);

// Get related products
$related_query = "SELECT p.*, e.ecat_name 
                  FROM tbl_product p 
                  LEFT JOIN tbl_end_category e ON p.ecat_id = e.ecat_id 
                  WHERE p.ecat_id = ? 
                  AND p.p_id != ? 
                  AND p.p_is_active = 1 
                  ORDER BY p.p_id DESC 
                  LIMIT 4";
$stmt = mysqli_prepare($db, $related_query);
mysqli_stmt_bind_param($stmt, "ii", $product['ecat_id'], $p_id);
mysqli_stmt_execute($stmt);
$related_result = mysqli_stmt_get_result($stmt);

// Include cart functions
include_once "cart-functions.php";

// Handle Add to Cart action
if (isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $result = addToCart($db, $p_id, $quantity);
    
    if ($result['success']) {
        $success_message = $result['message'] . " <a href='cart.php' class='ml-2 text-blue-600 hover:underline'>View Cart</a>";
    } else {
        $error_message = $result['message'];
    }
}

// Handle Buy Now action
if (isset($_POST['buy_now'])) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $result = addToCart($db, $p_id, $quantity);
    
    if ($result['success']) {
        header('Location: checkout.php');
        exit;
    } else {
        $error_message = $result['message'];
    }
}
?>

<!-- Add this at the very top of the file, right after the opening <body> tag -->
<div id="imageModal" class="fixed inset-0 hidden bg-black bg-opacity-90 z-50 p-4">
    <div class="absolute top-4 right-4">
        <button onclick="closeModal()" class="text-white hover:text-gray-300">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    
    <!-- Previous button -->
    <button onclick="modalChangeImage('prev')" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white p-3 rounded-full focus:outline-none transition-all duration-300">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
    </button>
    
    <!-- Next button -->
    <button onclick="modalChangeImage('next')" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white p-3 rounded-full focus:outline-none transition-all duration-300">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
    </button>
    
    <div class="flex items-center justify-center h-full">
        <img id="modalImage" src="" alt="" class="max-h-[90vh] max-w-[90vw] object-contain">
    </div>
</div>

<!-- Breadcrumb -->
<div class="container mx-auto px-4 py-4">
    <nav class="text-sm mb-4">
        <ol class="list-none p-0 inline-flex">
            <li class="flex items-center">
                <a href="<?= BASE_URL ?>Home/landingpage.php" class="text-gray-600 hover:text-blue-600">Home</a>
            </li>
            <?php if(isset($product['tcat_id']) && isset($product['tcat_name'])): ?>
            <li class="flex items-center">
                <span class="mx-2 text-gray-400">/</span>
                <a href="<?= BASE_URL ?>Home/landingpage.php?tcat_id=<?= $product['tcat_id'] ?>" class="text-gray-600 hover:text-blue-600">
                    <?= htmlspecialchars($product['tcat_name']) ?>
                </a>
            </li>
            <?php endif; ?>
            <?php if(isset($product['mcat_id']) && isset($product['mcat_name'])): ?>
            <li class="flex items-center">
                <span class="mx-2 text-gray-400">/</span>
                <a href="<?= BASE_URL ?>Home/landingpage.php?mcat_id=<?= $product['mcat_id'] ?>" class="text-gray-600 hover:text-blue-600">
                    <?= htmlspecialchars($product['mcat_name']) ?>
                </a>
            </li>
            <?php endif; ?>
            <?php if(isset($product['ecat_id']) && isset($product['ecat_name'])): ?>
            <li class="flex items-center">
                <span class="mx-2 text-gray-400">/</span>
                <a href="<?= BASE_URL ?>Home/landingpage.php?ecat_id=<?= $product['ecat_id'] ?>" class="text-gray-600 hover:text-blue-600">
                    <?= htmlspecialchars($product['ecat_name']) ?>
                </a>
            </li>
            <?php endif; ?>
            <li class="flex items-center">
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-blue-600"><?= htmlspecialchars($product['p_name']) ?></span>
            </li>
        </ol>
    </nav>
</div>

<!-- Main Content -->
<div class="container mx-auto px-4 pb-12">
    <?php if(isset($success_message)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
        <span class="block sm:inline"><?= $success_message ?></span>
        <button class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
            <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
            </svg>
        </button>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="md:flex">
            <!-- Image Gallery -->
            <div class="md:w-1/2 lg:w-2/5">
                <div class="product-gallery">
                    <div class="main-image-container relative h-96 md:h-[32rem] lg:h-[40rem] overflow-hidden bg-gray-100 flex items-center justify-center">
                        <?php foreach($product_images as $index => $image): ?>
                        <div class="main-image absolute inset-0 transition-opacity duration-500 ease-in-out <?= $index === 0 ? 'opacity-100' : 'opacity-0' ?> flex items-center justify-center cursor-pointer"
                             data-index="<?= $index ?>"
                             onclick="openModal('<?= BASE_URL ?>admin/uploadimgs/<?= htmlspecialchars($image['photo']) ?>')">
                            <img src="<?= BASE_URL ?>admin/uploadimgs/<?= htmlspecialchars($image['photo']) ?>"
                                 alt="<?= htmlspecialchars($product['p_name']) ?>" 
                                 class="max-w-full max-h-full object-contain hover:scale-105 transition-transform duration-300">
                        </div>
                        <?php endforeach; ?>
                        <button class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-30 text-white p-2 rounded-r focus:outline-none hover:bg-opacity-50"
                                onclick="changeImage('prev')">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-30 text-white p-2 rounded-l focus:outline-none hover:bg-opacity-50"
                                onclick="changeImage('next')">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                    <?php if(count($product_images) > 1): ?>
                    <div class="thumbnail-container flex overflow-x-auto p-2 space-x-2 bg-gray-100">
                        <?php foreach($product_images as $index => $image): ?>
                        <div class="thumbnail cursor-pointer flex-shrink-0 border-2 <?= $index === 0 ? 'border-blue-500' : 'border-transparent' ?> hover:border-blue-300"
                             onclick="selectImage(<?= $index ?>)">
                            <img src="<?= BASE_URL ?>admin/uploadimgs/<?= htmlspecialchars($image['photo']) ?>"
                                 alt="Thumbnail" 
                                 class="h-20 w-20 object-cover">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="md:w-1/2 lg:w-3/5 p-6">
                <div class="mb-4">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($product['p_name']) ?></h1>
                    <div class="text-sm text-gray-600 mb-2">
                        Category: <a href="<?= BASE_URL ?>Home/landingpage.php?ecat_id=<?= $product['ecat_id'] ?>" class="text-blue-600 hover:underline">
                            <?= htmlspecialchars($product['ecat_name']) ?>
                        </a>
                    </div>
                    <?php if(!empty($product['p_sku'])): ?>
                    <div class="text-sm text-gray-600 mb-4">
                        Product Code: <span class="font-medium"><?= htmlspecialchars($product['p_sku']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="mb-6">
                        <?php if(isset($product['p_old_price']) && $product['p_old_price'] > 0): ?>
                            <div class="flex items-baseline">
                                <span class="text-3xl font-bold text-blue-700">₹<?= number_format($product['p_current_price']) ?></span>
                                <span class="text-lg text-gray-500 line-through ml-3">₹<?= number_format($product['p_old_price']) ?></span>
                                <?php $discount_percent = round(($product['p_old_price'] - $product['p_current_price']) / $product['p_old_price'] * 100); ?>
                                <span class="ml-3 px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded">
                                    <?= $discount_percent ?>% OFF
                                </span>
                            </div>
                        <?php else: ?>
                            <span class="text-3xl font-bold text-blue-700">₹<?= number_format($product['p_current_price']) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if(!empty($product['p_short_description'])): ?>
                    <div class="mb-6">
                        <p class="text-gray-700"><?= nl2br(htmlspecialchars($product['p_short_description'])) ?></p>
                    </div>
                    <?php endif; ?>
                    <div class="mb-6">
                        <?php if(isset($product['p_qty']) && $product['p_qty'] > 0): ?>
                        <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded">
                            In Stock (<?= $product['p_qty'] ?> available)
                        </span>
                        <?php else: ?>
                        <span class="inline-block px-3 py-1 bg-red-100 text-red-800 text-sm font-semibold rounded">
                            Out of Stock
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php if(isset($product['p_qty']) && $product['p_qty'] > 0): ?>
                    <form action="" method="post" class="mb-6">
                        <div class="flex items-center mb-4">
                            <label for="quantity" class="mr-3 text-gray-700">Quantity:</label>
                            <div class="custom-number-input h-10 w-32">
                                <div class="flex flex-row h-10 w-full rounded-lg relative bg-transparent mt-1">
                                    <button type="button" class="bg-gray-200 text-gray-600 hover:text-gray-700 hover:bg-gray-300 h-full w-10 rounded-l cursor-pointer outline-none"
                                            onclick="decrementQuantity()">
                                        <span class="m-auto text-xl font-bold">−</span>
                                    </button>
                                    <input type="number" id="quantity" name="quantity" 
                                           class="outline-none focus:outline-none text-center w-full bg-gray-100 font-semibold text-md hover:text-black focus:text-black md:text-base cursor-default flex items-center text-gray-700"
                                           value="1" min="1" max="<?= $product['p_qty'] ?>">
                                    <button type="button" class="bg-gray-200 text-gray-600 hover:text-gray-700 hover:bg-gray-300 h-full w-10 rounded-r cursor-pointer"
                                            onclick="incrementQuantity(<?= $product['p_qty'] ?>)">
                                        <span class="m-auto text-xl font-bold">+</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button type="submit" name="add_to_cart" 
                                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md 
                                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 
                                    transition duration-150 ease-in-out">
                                <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                            </button>
                            <button type="submit" name="buy_now" 
                                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md 
                                    focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 
                                    transition duration-150 ease-in-out">
                                <i class="fas fa-bolt mr-2"></i> Buy Now
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Product Tabs -->
        <div class="border-t border-gray-200">
            <div class="product-tabs" x-data="{ activeTab: 'description' }">
                <div class="flex border-b">
                    <button @click="activeTab = 'description'" 
                            :class="{ 'border-b-2 border-blue-500 text-blue-600': activeTab === 'description' }"
                            class="px-4 py-2 font-medium text-gray-600 hover:text-blue-500 focus:outline-none">
                        Description
                    </button>
                    <button @click="activeTab = 'features'" 
                            :class="{ 'border-b-2 border-blue-500 text-blue-600': activeTab === 'features' }"
                            class="px-4 py-2 font-medium text-gray-600 hover:text-blue-500 focus:outline-none">
                        Features
                    </button>
                    <button @click="activeTab = 'conditions'" 
                            :class="{ 'border-b-2 border-blue-500 text-blue-600': activeTab === 'conditions' }"
                            class="px-4 py-2 font-medium text-gray-600 hover:text-blue-500 focus:outline-none">
                        Shipping & Returns
                    </button>
                </div>
                <div class="p-6">
                    <div x-show="activeTab === 'description'" class="prose max-w-none">
                        <?php if(!empty($product['p_description'])): ?>
                            <?= nl2br(htmlspecialchars($product['p_description'])) ?>
                        <?php else: ?>
                            <p>No detailed description available for this product.</p>
                        <?php endif; ?>
                    </div>
                    <div x-show="activeTab === 'features'" class="prose max-w-none" style="display: none;">
                        <?php if(!empty($product['p_feature'])): ?>
                            <?= nl2br(htmlspecialchars($product['p_feature'])) ?>
                        <?php else: ?>
                            <p>No specific features listed for this product.</p>
                        <?php endif; ?>
                    </div>
                    <div x-show="activeTab === 'conditions'" class="prose max-w-none" style="display: none;">
                        <?php if(!empty($product['p_condition'])): ?>
                            <?= nl2br(htmlspecialchars($product['p_condition'])) ?>
                        <?php else: ?>
                            <div>
                                <h3 class="text-lg font-semibold mb-3">Shipping</h3>
                                <ul class="list-disc pl-5 mb-4">
                                    <li>Free standard shipping on orders over ₹999</li>
                                    <li>Standard delivery within 4-6 business days</li>
                                    <li>Express delivery available at checkout</li>
                                </ul>
                                <h3 class="text-lg font-semibold mb-3">Returns</h3>
                                <ul class="list-disc pl-5">
                                    <li>30-day return policy</li>
                                    <li>Items must be unused and in original packaging</li>
                                    <li>Return shipping fee may apply</li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if(mysqli_num_rows($related_result) > 0): ?>
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Related Products</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <?php while($related = mysqli_fetch_assoc($related_result)): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden transition-transform duration-300 hover:shadow-lg hover:-translate-y-1">
                <a href="<?= BASE_URL ?>Home/product.php?p_id=<?= $related['p_id'] ?>">
                    <img src="<?= BASE_URL ?>admin/uploadimgs/<?= htmlspecialchars($related['p_featured_photo']) ?>"
                         alt="<?= htmlspecialchars($related['p_name']) ?>" 
                         class="w-full h-48 object-cover">
                </a>
                <div class="p-4">
                    <a href="<?= BASE_URL ?>Home/product.php?p_id=<?= $related['p_id'] ?>">
                        <h3 class="text-lg font-semibold text-gray-800 hover:text-blue-600 truncate"><?= htmlspecialchars($related['p_name']) ?></h3>
                    </a>
                    <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($related['ecat_name']) ?></p>
                    <div class="mt-2">
                        <?php if(isset($related['p_old_price']) && $related['p_old_price'] > 0): ?>
                            <span class="text-lg font-bold text-blue-600">₹<?= number_format($related['p_current_price']) ?></span>
                            <span class="text-sm text-gray-500 line-through ml-2">₹<?= number_format($related['p_old_price']) ?></span>
                        <?php else: ?>
                            <span class="text-lg font-bold text-blue-600">₹<?= number_format($related['p_current_price']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
let currentIndex = 0;
const totalImages = <?= count($product_images) ?>;
const productImages = [
    <?php foreach($product_images as $image): ?>
    "<?= BASE_URL ?>admin/uploadimgs/<?= htmlspecialchars($image['photo']) ?>",
    <?php endforeach; ?>
];

// Modal functions
function openModal(imageSrc) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    modalImage.src = imageSrc;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Find the index of the current image
    currentIndex = productImages.indexOf(imageSrc);
}

function closeModal() {
    const modal = document.getElementById('imageModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function modalChangeImage(direction) {
    let newIndex;
    if (direction === 'next') {
        newIndex = (currentIndex + 1) % totalImages;
    } else {
        newIndex = (currentIndex - 1 + totalImages) % totalImages;
    }
    
    // Update modal image
    const modalImage = document.getElementById('modalImage');
    modalImage.src = productImages[newIndex];
    
    // Update main gallery
    updateMainGallery(newIndex);
    
    currentIndex = newIndex;
}

function updateMainGallery(newIndex) {
    // Update main image visibility
    document.querySelectorAll('.main-image').forEach((img, index) => {
        if (index === newIndex) {
            img.classList.remove('opacity-0');
            img.classList.add('opacity-100');
        } else {
            img.classList.add('opacity-0');
            img.classList.remove('opacity-100');
        }
    });
    
    // Update thumbnails if they exist
    if (document.querySelectorAll('.thumbnail').length > 0) {
        document.querySelectorAll('.thumbnail').forEach((thumb, index) => {
            if (index === newIndex) {
                thumb.classList.add('border-blue-500');
                thumb.classList.remove('border-transparent');
            } else {
                thumb.classList.remove('border-blue-500');
                thumb.classList.add('border-transparent');
            }
        });
    }
}

// Click outside to close
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (document.getElementById('imageModal').classList.contains('hidden')) {
        return;
    }
    
    switch(e.key) {
        case 'ArrowLeft':
            modalChangeImage('prev');
            break;
        case 'ArrowRight':
            modalChangeImage('next');
            break;
        case 'Escape':
            closeModal();
            break;
    }
});

// Original gallery functions remain the same
function changeImage(direction) {
    if (totalImages <= 1) return;
    
    document.querySelector(`.main-image[data-index="${currentIndex}"]`).classList.add('opacity-0');
    document.querySelector(`.main-image[data-index="${currentIndex}"]`).classList.remove('opacity-100');
    
    if (document.querySelectorAll('.thumbnail').length > 0) {
        document.querySelectorAll('.thumbnail')[currentIndex].classList.remove('border-blue-500');
        document.querySelectorAll('.thumbnail')[currentIndex].classList.add('border-transparent');
    }
    
    if (direction === 'next') {
        currentIndex = (currentIndex + 1) % totalImages;
    } else {
        currentIndex = (currentIndex - 1 + totalImages) % totalImages;
    }
    
    document.querySelector(`.main-image[data-index="${currentIndex}"]`).classList.remove('opacity-0');
    document.querySelector(`.main-image[data-index="${currentIndex}"]`).classList.add('opacity-100');
    
    if (document.querySelectorAll('.thumbnail').length > 0) {
        document.querySelectorAll('.thumbnail')[currentIndex].classList.add('border-blue-500');
        document.querySelectorAll('.thumbnail')[currentIndex].classList.remove('border-transparent');
    }
}

function selectImage(index) {
    if (index === currentIndex || index >= totalImages) return;
    
    document.querySelector(`.main-image[data-index="${currentIndex}"]`).classList.add('opacity-0');
    document.querySelector(`.main-image[data-index="${currentIndex}"]`).classList.remove('opacity-100');
    
    if (document.querySelectorAll('.thumbnail').length > 0) {
        document.querySelectorAll('.thumbnail')[currentIndex].classList.remove('border-blue-500');
        document.querySelectorAll('.thumbnail')[currentIndex].classList.add('border-transparent');
    }
    
    currentIndex = index;
    
    document.querySelector(`.main-image[data-index="${currentIndex}"]`).classList.remove('opacity-0');
    document.querySelector(`.main-image[data-index="${currentIndex}"]`).classList.add('opacity-100');
    
    if (document.querySelectorAll('.thumbnail').length > 0) {
        document.querySelectorAll('.thumbnail')[currentIndex].classList.add('border-blue-500');
        document.querySelectorAll('.thumbnail')[currentIndex].classList.remove('border-transparent');
    }
}

function decrementQuantity() {
    const input = document.getElementById('quantity');
    const value = parseInt(input.value, 10);
    if (value > 1) {
        input.value = value - 1;
    }
}

function incrementQuantity(max) {
    const input = document.getElementById('quantity');
    const value = parseInt(input.value, 10);
    if (value < max) {
        input.value = value + 1;
    }
}

document.getElementById('quantity')?.addEventListener('input', function() {
    const max = parseInt(this.max);
    const min = parseInt(this.min);
    const value = parseInt(this.value);
    if (value < min) this.value = min;
    if (value > max) this.value = max;
});
</script>

<?php
// Include the footer
include_once "../user/includes/footer.php";
?>