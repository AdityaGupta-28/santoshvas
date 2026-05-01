<?php
require_once __DIR__ . "/../db.php";

$title = "Products - Santosh Vastralay";

if (!isset($db) || !$db) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get category IDs if provided
$ecat_id = isset($_GET['ecat_id']) ? (int)$_GET['ecat_id'] : 0;
$mcat_id = isset($_GET['mcat_id']) ? (int)$_GET['mcat_id'] : 0;

// Initialize variables for product pagination
$itemsPerPage = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
$itemsPerPage = max(1, min(100, $itemsPerPage));
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, $currentPage);
$offset = ($currentPage - 1) * $itemsPerPage;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';

// Get category information
$category_info = null;
$category_name = "All Products";
$breadcrumb = [];

// Prepare statements to prevent SQL injection
if ($ecat_id > 0) {
    $ecat_query = "SELECT e.*, m.mcat_name, m.mcat_id, t.tcat_name, t.tcat_id 
                   FROM tbl_end_category e
                   LEFT JOIN tbl_mid_category m ON e.mcat_id = m.mcat_id
                   LEFT JOIN tbl_top_category t ON m.tcat_id = t.tcat_id
                   WHERE e.ecat_id = ? AND e.ecat_is_active = 1";
    $stmt = mysqli_prepare($db, $ecat_query);
    mysqli_stmt_bind_param($stmt, "i", $ecat_id);
    mysqli_stmt_execute($stmt);
    $ecat_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($ecat_result) > 0) {
        $category_info = mysqli_fetch_assoc($ecat_result);
        $category_name = $category_info['ecat_name'];
        $breadcrumb = [
            ['name' => $category_info['tcat_name'], 'id' => $category_info['tcat_id'], 'type' => 'tcat'],
            ['name' => $category_info['mcat_name'], 'id' => $category_info['mcat_id'], 'type' => 'mcat'],
            ['name' => $category_info['ecat_name'], 'id' => $category_info['ecat_id'], 'type' => 'ecat']
        ];
    }
    mysqli_stmt_close($stmt);
} elseif ($mcat_id > 0) {
    $mcat_query = "SELECT m.*, t.tcat_name, t.tcat_id 
                   FROM tbl_mid_category m
                   LEFT JOIN tbl_top_category t ON m.tcat_id = t.tcat_id
                   WHERE m.mcat_id = ?";
    $stmt = mysqli_prepare($db, $mcat_query);
    mysqli_stmt_bind_param($stmt, "i", $mcat_id);
    mysqli_stmt_execute($stmt);
    $mcat_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($mcat_result) > 0) {
        $category_info = mysqli_fetch_assoc($mcat_result);
        $category_name = $category_info['mcat_name'];
        $breadcrumb = [
            ['name' => $category_info['tcat_name'], 'id' => $category_info['tcat_id'], 'type' => 'tcat'],
            ['name' => $category_info['mcat_name'], 'id' => $category_info['mcat_id'], 'type' => 'mcat']
        ];
    }
    mysqli_stmt_close($stmt);
}

// Build query conditions based on category
if ($ecat_id > 0) {
    $count_condition = "WHERE p.ecat_id = ? AND p.p_is_active = 1";
    $product_condition = $count_condition;
    $category_param = $ecat_id;  // Store parameter in a variable
} elseif ($mcat_id > 0) {
    $count_condition = "WHERE p.ecat_id IN (SELECT ecat_id FROM tbl_end_category WHERE mcat_id = ?) AND p.p_is_active = 1";
    $product_condition = $count_condition;
    $category_param = $mcat_id;  // Store parameter in a variable
} else {
    $count_condition = "WHERE p.p_is_active = 1";
    $product_condition = "WHERE p.p_is_active = 1";
}

// Count total products for pagination
$count_query = "SELECT COUNT(*) as total FROM tbl_product p $count_condition";
$stmt = mysqli_prepare($db, $count_query);
if ($ecat_id > 0 || $mcat_id > 0) {
    mysqli_stmt_bind_param($stmt, "i", $category_param);  // Use the stored parameter
}
mysqli_stmt_execute($stmt);
$count_result = mysqli_stmt_get_result($stmt);
$total_products = mysqli_fetch_assoc($count_result)['total'];
mysqli_free_result($count_result);
$total_pages = ceil($total_products / $itemsPerPage);
mysqli_stmt_close($stmt);

// Determine sort order
$order_by = "p.p_id DESC"; // Default: latest
if ($sort === 'price-low') {
    $order_by = "p.p_current_price ASC";
} elseif ($sort === 'price-high') {
    $order_by = "p.p_current_price DESC";
} elseif ($sort === 'name-az') {
    $order_by = "p.p_name ASC";
} elseif ($sort === 'name-za') {
    $order_by = "p.p_name DESC";
}

$product_query = "SELECT p.*, e.ecat_name 
                  FROM tbl_product p 
                  LEFT JOIN tbl_end_category e ON p.ecat_id = e.ecat_id 
                  $product_condition 
                  ORDER BY $order_by 
                  LIMIT $offset, $itemsPerPage";
$product_stmt = mysqli_prepare($db, $product_query);
if ($ecat_id > 0 || $mcat_id > 0) {
    mysqli_stmt_bind_param($product_stmt, "i", $category_param);
}
mysqli_stmt_execute($product_stmt);
$product_result = mysqli_stmt_get_result($product_stmt);

$tcat_query = "SELECT * FROM tbl_top_category WHERE show_on_menu = 1 ORDER BY tcat_name";
$tcat_result = mysqli_query($db, $tcat_query);
$top_categories = [];

while ($tcat = mysqli_fetch_assoc($tcat_result)) {
    $tcat_id = $tcat['tcat_id'];
    $mcat_query = "SELECT * FROM tbl_mid_category WHERE tcat_id = ? ORDER BY mcat_name";
    $mcat_stmt = mysqli_prepare($db, $mcat_query);
    mysqli_stmt_bind_param($mcat_stmt, "i", $tcat_id);
    mysqli_stmt_execute($mcat_stmt);
    $mcat_result = mysqli_stmt_get_result($mcat_stmt);

    $mid_categories = [];
    while ($mcat = mysqli_fetch_assoc($mcat_result)) {
        $mid_categories[] = $mcat;
    }
    mysqli_free_result($mcat_result);
    mysqli_stmt_close($mcat_stmt);

    $top_categories[] = [
        'tcat' => $tcat,
        'mcats' => $mid_categories
    ];
}
mysqli_free_result($tcat_result);

include_once __DIR__ . "/../user/includes/header.php";
?>

<!-- Navigation Bar -->
<nav class="bg-gray-300 text-black shadow-md mb-6">
    <div class="container mx-auto px-4">
        <div class="flex justify-between">
            <div class="flex">
                <div class="flex items-center space-x-4">
                    <?php foreach($top_categories as $category): ?>
                        <div class="relative group dropdown-container">
                            <button class="py-4 px-3 hover:bg-blue-800 hover:text-white focus:outline-none flex items-center dropdown-toggle">
                                <?= htmlspecialchars($category['tcat']['tcat_name']) ?>
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="absolute left-0 mt-0 w-48 bg-white rounded-b-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 dropdown-menu z-50">
                                <div class="py-2">
                                    <?php foreach($category['mcats'] as $mcat): ?>
                                        <a href="<?= BASE_URL ?>Home/landingpage.php?mcat_id=<?= $mcat['mcat_id'] ?>"
                                           class="block px-4 py-2 text-gray-800 hover:bg-blue-100">
                                            <?= htmlspecialchars($mcat['mcat_name']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                    <?php if(count($category['mcats']) === 0): ?>
                                        <span class="block px-4 py-2 text-gray-500">No subcategories</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="hidden md:flex items-center">
                <form action="<?= BASE_URL ?>Home/search.php" method="GET" class="flex">
                    <input type="text" name="q" placeholder="Search products..." 
                           class="px-4 py-1 rounded-l text-black focus:outline-none">
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 px-4 py-1 rounded-r">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container mx-auto p-4">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($category_name) ?></h1>
        <?php if($category_info && !empty($category_info['ecat_description'] ?? $category_info['mcat_description'] ?? '')): ?>
            <p class="text-gray-600"><?= htmlspecialchars($category_info['ecat_description'] ?? $category_info['mcat_description']) ?></p>
        <?php else: ?>
            <p class="text-gray-600">Browse our collection of <?= htmlspecialchars($category_name) ?></p>
        <?php endif; ?>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="mb-4 md:mb-0">
                <span class="text-gray-700">Showing <?= min($total_products, 1 + $offset) ?>-<?= min($offset + $itemsPerPage, $total_products) ?> of <?= $total_products ?> products</span>
            </div>
            <div class="flex flex-col sm:flex-row gap-4">
                <form method="GET" class="flex items-center gap-4">
                    <?php if(isset($_GET['mcat_id'])): ?>
                        <input type="hidden" name="mcat_id" value="<?= htmlspecialchars($_GET['mcat_id']) ?>">
                    <?php endif; ?>
                    <?php if(isset($_GET['ecat_id'])): ?>
                        <input type="hidden" name="ecat_id" value="<?= htmlspecialchars($_GET['ecat_id']) ?>">
                    <?php endif; ?>
                    <div class="flex items-center">
                        <label for="sort" class="mr-2 text-gray-700">Sort by:</label>
                        <select id="sort" name="sort" class="border rounded px-2 py-1" onchange="this.form.submit()">
                            <option value="latest" <?= $sort == 'latest' ? 'selected' : '' ?>>Latest</option>
                            <option value="price-low" <?= $sort == 'price-low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price-high" <?= $sort == 'price-high' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="name-az" <?= $sort == 'name-az' ? 'selected' : '' ?>>Name: A to Z</option>
                            <option value="name-za" <?= $sort == 'name-za' ? 'selected' : '' ?>>Name: Z to A</option>
                        </select>
                    </div>
                    <div class="flex items-center">
                        <label for="limit" class="mr-2 text-gray-700">Show:</label>
                        <select id="limit" name="limit" class="border rounded px-2 py-1" onchange="this.form.submit()">
                            <option value="12" <?= $itemsPerPage == 12 ? 'selected' : '' ?>>12</option>
                            <option value="24" <?= $itemsPerPage == 24 ? 'selected' : '' ?>>24</option>
                            <option value="36" <?= $itemsPerPage == 36 ? 'selected' : '' ?>>36</option>
                            <option value="48" <?= $itemsPerPage == 48 ? 'selected' : '' ?>>48</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php if(mysqli_num_rows($product_result) > 0): ?>
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
            <?php while($product = mysqli_fetch_assoc($product_result)): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition-transform duration-300 hover:shadow-lg hover:-translate-y-1">
                    <a href="<?= BASE_URL ?>Home/product.php?p_id=<?= $product['p_id'] ?>">
                        <img src="<?= BASE_URL ?>admin/uploadimgs/<?= htmlspecialchars($product['p_featured_photo']) ?>"
                             alt="<?= htmlspecialchars($product['p_name']) ?>" 
                             class="w-full max-h-76 object-fit">
                    </a>
                    <div class="p-4">
                        <a href="<?= BASE_URL ?>Home/product.php?p_id=<?= $product['p_id'] ?>">
                            <h3 class="text-lg font-semibold text-gray-800 hover:text-blue-600 truncate"><?= htmlspecialchars($product['p_name']) ?></h3>
                        </a>
                        <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($product['ecat_name']) ?></p>
                        <div class="mt-2">
                            <?php if($product['p_old_price'] > 0): ?>
                                <span class="text-lg font-bold text-blue-600">₹<?= number_format($product['p_current_price']) ?></span>
                                <span class="text-sm text-gray-500 line-through ml-2">₹<?= number_format($product['p_old_price']) ?></span>
                            <?php else: ?>
                                <span class="text-lg font-bold text-blue-600">₹<?= number_format($product['p_current_price']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md p-8 text-center mb-8">
            <i class="fas fa-search text-gray-400 text-5xl mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-700 mb-2">No Products Found</h2>
            <p class="text-gray-600 mb-4">We couldn't find any products in this category.</p>
            <a href="<?= BASE_URL ?>Home/landingpage.php" class="inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Browse All Categories
            </a>
        </div>
    <?php endif; ?>

    <?php if($total_pages > 1): ?>
        <div class="flex justify-center my-8">
            <nav aria-label="Page navigation">
                <ul class="flex space-x-2">
                    <?php if($currentPage > 1): ?>
                        <li>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>" 
                               class="px-4 py-2 bg-white border rounded hover:bg-gray-100">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($total_pages, $startPage + 4);
                    
                    if ($startPage > 1) {
                        echo '<li><a href="?'.http_build_query(array_merge($_GET, ['page' => 1])).'" 
                              class="px-4 py-2 bg-white border rounded hover:bg-gray-100">1</a></li>';
                        if ($startPage > 2) {
                            echo '<li><span class="px-4 py-2 text-gray-500">...</span></li>';
                        }
                    }
                    
                    for ($i = $startPage; $i <= $endPage; $i++) {
                        $activeClass = $i == $currentPage ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100';
                        echo '<li>
                                <a href="?'.http_build_query(array_merge($_GET, ['page' => $i])).'" 
                                   class="px-4 py-2 border rounded '.$activeClass.'">'.$i.'</a>
                              </li>';
                    }
                    
                    if ($endPage < $total_pages) {
                        if ($endPage < $total_pages - 1) {
                            echo '<li><span class="px-4 py-2 text-gray-500">...</span></li>';
                        }
                        echo '<li>
                                <a href="?'.http_build_query(array_merge($_GET, ['page' => $total_pages])).'" 
                                   class="px-4 py-2 bg-white border rounded hover:bg-gray-100">'.$total_pages.'</a>
                              </li>';
                    }
                    ?>
                    
                    <?php if($currentPage < $total_pages): ?>
                        <li>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>" 
                               class="px-4 py-2 bg-white border rounded hover:bg-gray-100">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="bg-gray-800 text-white mt-12">
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- About Section -->
            <div>
                <h3 class="text-lg font-bold mb-4">About Us</h3>
                <p class="text-gray-400">Santosh Vastralay - Your one-stop destination for quality clothing and accessories.</p>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="landingpage.php" class="text-gray-400 hover:text-white">Home</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white">New Arrivals</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white">Best Sellers</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white">Deals & Offers</a></li>
                </ul>
            </div>
            
            <!-- Customer Service -->
            <div>
                <h3 class="text-lg font-bold mb-4">Customer Service</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-400 hover:text-white">Contact Us</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white">Shipping Policy</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white">Returns & Exchange</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white">FAQs</a></li>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div>
                <h3 class="text-lg font-bold mb-4">Contact Info</h3>
                <ul class="space-y-2 text-gray-400">
                    <li class="flex items-center">
                        <i class="fas fa-map-marker-alt w-5"></i>
                        <span>Bishunpura, Gopalganj, Bihar, India</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-phone w-5"></i>
                        <span>+91 9006645817</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-envelope w-5"></i>
                        <span>santoshvastralay@gmail.com</span>
                    </li>
                </ul>
                <!-- Social Media Links -->
                <div class="mt-4 flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Bottom Bar -->
        <div class="border-t border-gray-700 mt-8 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400">© 2025 Santosh Vastralay. All rights reserved.</p>
                <div class="mt-4 md:mt-0">
                    <img src="<?= BASE_URL ?>assets/images/payment-methods.png" alt="Payment Methods" class="h-8">
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Add this style section in the head of your document or just before the closing </head> tag -->
<style>
    .group:hover .dropdown-menu {
        display: block !important;
    }
    
    .dropdown-menu {
        display: none;
        min-width: 200px;
    }
    
    @media (min-width: 768px) {
        .group:hover .dropdown-menu {
            opacity: 1 !important;
            visibility: visible !important;
        }
    }
</style>

<!-- Update the JavaScript section -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    const dropdownContainers = document.querySelectorAll('.dropdown-container');
    
    // For mobile devices only (touch devices)
    if ('ontouchstart' in window) {
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const container = this.closest('.dropdown-container');
                const menu = container.querySelector('.dropdown-menu');
                const isVisible = !menu.classList.contains('opacity-0');
                
                // Hide all other dropdowns
                document.querySelectorAll('.dropdown-menu').forEach(dropdown => {
                    dropdown.classList.add('opacity-0', 'invisible');
                });
                
                // Toggle current dropdown
                if (!isVisible) {
                    menu.classList.remove('opacity-0', 'invisible');
                    
                    // Close dropdown when clicking outside
                    const closeDropdown = function(event) {
                        if (!container.contains(event.target)) {
                            menu.classList.add('opacity-0', 'invisible');
                            document.removeEventListener('click', closeDropdown);
                        }
                    };
                    
                    setTimeout(() => {
                        document.addEventListener('click', closeDropdown);
                    }, 0);
                }
            });
        });
    }
    
    // Ensure dropdowns are properly positioned
    dropdownContainers.forEach(container => {
        const menu = container.querySelector('.dropdown-menu');
        const button = container.querySelector('.dropdown-toggle');
        
        // Adjust dropdown position if needed
        function adjustPosition() {
            const rect = button.getBoundingClientRect();
            menu.style.top = `${rect.height}px`;
            menu.style.minWidth = `${Math.max(200, rect.width)}px`;
        }
        
        adjustPosition();
        window.addEventListener('resize', adjustPosition);
    });
});
</script>

<?php include_once __DIR__ . "/../user/includes/footer.php"; ?>