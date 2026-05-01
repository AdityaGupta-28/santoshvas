<?php
require_once __DIR__ . "/../db.php";
include_once __DIR__ . "/../user/includes/header.php";

// Get search query
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 12;
$offset = ($page - 1) * $items_per_page;

if (!empty($search_query)) {
    // Prepare the search query
    $search_term = "%{$search_query}%";
    
    // Count total results
    $count_stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM tbl_product 
        WHERE (p_name LIKE ? OR p_description LIKE ? OR p_short_description LIKE ?)
        AND p_is_active = 1
    ");
    
    if (!$count_stmt) {
        die("Error preparing count statement: " . $db->error);
    }
    
    $count_stmt->bind_param("sss", $search_term, $search_term, $search_term);
    $count_stmt->execute();
    $total_results = $count_stmt->get_result()->fetch_assoc()['total'];
    $total_pages = ceil($total_results / $items_per_page);
    
    // Get search results
    $stmt = $db->prepare("
        SELECT p.*, e.ecat_name 
        FROM tbl_product p
        LEFT JOIN tbl_end_category e ON p.ecat_id = e.ecat_id
        WHERE (p.p_name LIKE ? OR p.p_description LIKE ? OR p.p_short_description LIKE ?)
        AND p.p_is_active = 1
        ORDER BY p.p_id DESC
        LIMIT ?, ?
    ");
    
    if (!$stmt) {
        die("Error preparing search statement: " . $db->error);
    }
    
    $stmt->bind_param("sssii", $search_term, $search_term, $search_term, $offset, $items_per_page);
    $stmt->execute();
    $results = $stmt->get_result();
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Search Results</h1>
        <?php if (!empty($search_query)): ?>
            <p class="text-gray-600">
                Found <?php echo $total_results; ?> results for "<?php echo htmlspecialchars($search_query); ?>"
            </p>
        <?php endif; ?>
    </div>

    <?php if (empty($search_query)): ?>
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <i class="fas fa-search text-gray-400 text-5xl mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-700 mb-2">Enter a search term</h2>
            <p class="text-gray-600 mb-6">Type something in the search box to find products</p>
            <form action="search.php" method="GET" class="max-w-md mx-auto">
                <div class="flex gap-4">
                    <input type="text" name="q" 
                           class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Search for products...">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        Search
                    </button>
                </div>
            </form>
        </div>
    <?php elseif ($total_results === 0): ?>
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <i class="fas fa-search text-gray-400 text-5xl mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-700 mb-2">No results found</h2>
            <p class="text-gray-600 mb-6">Try different keywords or check your spelling</p>
            <a href="landingpage.php" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                Browse All Products
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
            <?php while ($product = $results->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition-transform duration-300 hover:shadow-lg hover:-translate-y-1">
                    <a href="product.php?p_id=<?= $product['p_id'] ?>">
                        <img src="<?= BASE_URL ?>admin/uploadimgs/<?= htmlspecialchars($product['p_featured_photo']) ?>"
                             alt="<?= htmlspecialchars($product['p_name']) ?>" 
                             class="w-full h-48 object-cover">
                    </a>
                    <div class="p-4">
                        <a href="product.php?p_id=<?= $product['p_id'] ?>">
                            <h3 class="text-lg font-semibold text-gray-800 hover:text-blue-600 truncate">
                                <?= htmlspecialchars($product['p_name']) ?>
                            </h3>
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

        <?php if ($total_pages > 1): ?>
            <div class="flex justify-center mt-8">
                <nav class="inline-flex rounded-lg">
                    <?php if ($page > 1): ?>
                        <a href="?q=<?= urlencode($search_query) ?>&page=<?= ($page - 1) ?>" 
                           class="px-3 py-2 border rounded-l hover:bg-gray-100">
                            Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?q=<?= urlencode($search_query) ?>&page=<?= $i ?>" 
                           class="px-3 py-2 border <?= $i === $page ? 'bg-blue-600 text-white' : 'hover:bg-gray-100' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?q=<?= urlencode($search_query) ?>&page=<?= ($page + 1) ?>" 
                           class="px-3 py-2 border rounded-r hover:bg-gray-100">
                            Next
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . "/../user/includes/footer.php"; ?> 