<?php
require_once __DIR__ . "/../../db.php";

if (!headers_sent()) {
    if (!isset($title)) {
        $title = "Santosh Vastralay";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
        .fancy-header {
            background: linear-gradient(45deg, #1a202c, #2d3748);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .nav-item {
            transition: all 0.3s ease;
        }
        .nav-item:hover {
            color: #4a90e2;
            transform: translateY(-2px);
        }
        .search-bar {
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .search-button {
            background: linear-gradient(45deg, #4a90e2, #63b3ed);
            transition: all 0.3s ease;
        }
        .search-button:hover {
            background: linear-gradient(45deg, #357abd, #4a90e2);
        }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #4a90e2;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            min-width: 20px;
            text-align: center;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            background: white;
            min-width: 200px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            z-index: 1000;
        }
        .dropdown-menu.show {
            display: block;
        }
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }
        .mobile-menu.show {
            transform: translateX(0);
        }
        .user-menu-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: #4a5568;
            transition: all 0.2s ease;
        }
        .user-menu-item:hover {
            background-color: #f7fafc;
            color: #2d3748;
        }
        .user-menu-item i {
            margin-right: 0.75rem;
            width: 1.25rem;
            text-align: center;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div id="mobileMenu" class="mobile-menu fixed inset-y-0 left-0 bg-gray-900 text-white w-64 z-50 shadow-lg">
        <div class="p-6 border-b border-gray-700">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-indigo-400">Santosh Vastralay</h1>
                <button id="closeMobileMenu" class="text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <nav class="mt-6">
            <a href="<?php echo BASE_URL; ?>index.php" class="block py-3 px-6 hover:bg-gray-800 hover:text-indigo-300 transition-colors duration-200">
                <i class="fas fa-home mr-2"></i> Home
            </a>
            <a href="<?php echo BASE_URL; ?>Home/landingpage.php" class="block py-3 px-6 hover:bg-gray-800 hover:text-indigo-300 transition-colors duration-200">
                <i class="fas fa-store mr-2"></i> Shop
            </a>
            <a href="<?php echo BASE_URL; ?>Home/about.html" class="block py-3 px-6 hover:bg-gray-800 hover:text-indigo-300 transition-colors duration-200">
                <i class="fas fa-info-circle mr-2"></i> About us
            </a>
            <a href="<?php echo BASE_URL; ?>Home/contact.php" class="block py-3 px-6 hover:bg-gray-800 hover:text-indigo-300 transition-colors duration-200">
                <i class="fas fa-envelope mr-2"></i> Contact
            </a>
            <a href="<?php echo BASE_URL; ?>Home/cart.php" class="block py-3 px-6 hover:bg-gray-800 hover:text-indigo-300 transition-colors duration-200">
                <i class="fas fa-shopping-cart mr-2"></i> Cart
                <span class="cart-badge"><?php echo getCartItemCount(); ?></span>
            </a>
            <?php if($isLoggedIn): ?>
                <a href="<?php echo BASE_URL; ?>user/userprofile.php" class="block py-3 px-6 hover:bg-gray-800 hover:text-indigo-300 transition-colors duration-200">
                    <i class="fas fa-user mr-2"></i> My Profile
                </a>
                <a href="<?php echo BASE_URL; ?>user/orders.php" class="block py-3 px-6 hover:bg-gray-800 hover:text-indigo-300 transition-colors duration-200">
                    <i class="fas fa-box mr-2"></i> My Orders
                </a>
                <a href="<?php echo BASE_URL; ?>actions/logoutaction.php" class="block py-3 px-6 hover:bg-gray-800 hover:text-indigo-300 transition-colors duration-200">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>user/login.php" class="block py-3 px-6 hover:bg-gray-800 hover:text-indigo-300 transition-colors duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </a>
                <a href="<?php echo BASE_URL; ?>user/reg.php" class="block py-3 px-6 hover:bg-gray-800 hover:text-indigo-300 transition-colors duration-200">
                    <i class="fas fa-user-plus mr-2"></i> Register
                </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>user/adminlogin.php" class="block py-3 px-6 hover:bg-gray-800 hover:text-indigo-300 transition-colors duration-200">
                <i class="fas fa-user-shield mr-2"></i> Admin
            </a>
        </nav>
    </div>

    <header class="fancy-header text-white p-4 flex justify-between items-center">
        <div class="flex items-center">
            <img src="<?php echo BASE_URL; ?>Home/images/logo.png" alt="Santosh Vastralay Logo" class="h-12 rounded-full shadow-md">
        </div>

        <button id="mobileMenuButton" class="sm:hidden text-white focus:outline-none p-2 hover:bg-gray-700 rounded">
            <i class="fas fa-bars text-xl"></i>
        </button>
    
        <nav class="hidden sm:flex items-center gap-8 text-lg">
            <a href="<?php echo BASE_URL; ?>index.php" class="nav-item font-semibold">
                 Home
            </a>
            <a href="<?php echo BASE_URL; ?>Home/landingpage.php" class="nav-item font-semibold">
                 Shop
            </a>
            <a href="<?php echo BASE_URL; ?>Home/about.html" class="nav-item font-semibold">
                 About us
            </a>
            <a href="<?php echo BASE_URL; ?>Home/contact.php" class="nav-item font-semibold">
                 Contact
            </a>
        </nav>
    
        <div class="hidden sm:flex items-center space-x-8">
            <a href="<?php echo BASE_URL; ?>Home/cart.php" class="text-white nav-item relative">
                <i class="fas fa-shopping-cart text-xl"></i>
                <span class="cart-badge"><?php echo getCartItemCount(); ?></span>
            </a>

            <div class="relative">
                <button id="userMenuButton" class="nav-item text-white focus:outline-none flex items-center">
                    <i class="fas fa-user text-xl"></i>
                    <span class="ml-2"><?php echo htmlspecialchars($userName); ?></span>
                </button>
                <div id="userMenuDropdown" class="dropdown-menu">
                    <?php if($isLoggedIn): ?>
                        <a href="<?php echo BASE_URL; ?>user/userprofile.php" class="user-menu-item">
                            <i class="fas fa-user-circle"></i> My Profile
                        </a>
                        <a href="<?php echo BASE_URL; ?>user/orders.php" class="user-menu-item">
                            <i class="fas fa-box"></i> My Orders
                        </a>
                        <a href="<?php echo BASE_URL; ?>actions/logoutaction.php" class="user-menu-item">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>user/login.php" class="user-menu-item">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="<?php echo BASE_URL; ?>user/reg.php" class="user-menu-item">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <a href="<?php echo BASE_URL; ?>user/adminlogin.php" class="nav-item text-white">
                <i class="fas fa-user-shield text-xl"></i>
            </a>
        </div>
    </header>

    <script>
        // Mobile Menu Toggle
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const closeMobileMenu = document.getElementById('closeMobileMenu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.add('show');
        });

        closeMobileMenu.addEventListener('click', () => {
            mobileMenu.classList.remove('show');
        });

        // User Dropdown Toggle
        const userMenuButton = document.getElementById('userMenuButton');
        const userMenuDropdown = document.getElementById('userMenuDropdown');

        userMenuButton.addEventListener('click', () => {
            userMenuDropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!userMenuButton.contains(e.target) && !userMenuDropdown.contains(e.target)) {
                userMenuDropdown.classList.remove('show');
            }
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!mobileMenu.contains(e.target) && !mobileMenuButton.contains(e.target) && mobileMenu.classList.contains('show')) {
                mobileMenu.classList.remove('show');
            }
        });
    </script>
<?php } ?>