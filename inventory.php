<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: auth.php");
    exit;
}

// Include database connection
require_once "db_connect.php";

// Get counts for dashboard cards
$sql_users = "SELECT COUNT(*) as total FROM users";
$result_users = $conn->query($sql_users);
$users_count = $result_users->fetch_assoc()["total"];

$sql_categories = "SELECT COUNT(*) as total FROM categories";
$result_categories = $conn->query($sql_categories);
$categories_count = $result_categories->fetch_assoc()["total"];

$sql_products = "SELECT COUNT(*) as total FROM products";
$result_products = $conn->query($sql_products);
$products_count = $result_products->fetch_assoc()["total"];

$sql_customers = "SELECT COUNT(*) as total FROM customers";
$result_customers = $conn->query($sql_customers);
$customers_count = $result_customers->fetch_assoc()["total"];

$sql_suppliers = "SELECT COUNT(*) as total FROM suppliers";
$result_suppliers = $conn->query($sql_suppliers);
$suppliers_count = $result_suppliers->fetch_assoc()["total"];

$sql_purchases = "SELECT COUNT(*) as total FROM orders";
$result_purchases = $conn->query($sql_purchases);
$purchases_count = $result_purchases->fetch_assoc()["total"];

$sql_outgoing = "SELECT COUNT(*) as total FROM orders WHERE status = 'Shipped' OR status = 'Delivered'";
$result_outgoing = $conn->query($sql_outgoing);
$outgoing_count = $result_outgoing->fetch_assoc()["total"];

// Get products for table
$sql_products_list = "SELECT p.id, p.name, p.price, p.quantity, p.image_path, c.name as category 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      ORDER BY p.id DESC";
$result_products_list = $conn->query($sql_products_list);

// Get categories for dropdown
$sql_categories_list = "SELECT id, name FROM categories ORDER BY name";
$result_categories_list = $conn->query($sql_categories_list);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <link rel="shortcut icon" href="icon.png" type="image/x-icon">
    <style>
        /* Bootstrap core styles */
        :root {
            --bs-blue: #0d6efd;
            --bs-indigo: #6610f2;
            --bs-purple: #6f42c1;
            --bs-pink: #d63384;
            --bs-red: #dc3545;
            --bs-orange: #fd7e14;
            --bs-yellow: #ffc107;
            --bs-green: #198754;
            --bs-success: #28a745;
            --bs-info: #17a2b8;
            --bs-warning: #ffc107;
            --bs-danger: #dc3545;
        }

        /* Reset and base styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            line-height: 1.5;
            color: #212529;
            background-color: #FFFFFF;
            background-image: linear-gradient(
                135deg,
                #ffffff 0%,
                #f8f9fa 50%,
                #ffffff 100%
            );
            background-attachment: fixed;
        }

        /* Container and Grid */
        .container-fluid {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }

        .col-md-3 {
            flex: 0 0 25%;
            max-width: 25%;
            padding: 0 15px;
        }

        .col-md-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
            padding: 0 15px;
        }

        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 15px;
        }

        /* Navbar styles */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
            background-color: rgba(40, 167, 69, 0.95) !important;
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            margin-bottom: 1rem;
        }

        .navbar-dark {
            background-color: var(--bs-success);
        }

        .navbar-brand {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 0.5rem 0;
            white-space: nowrap;
        }

        .container-fluid.position-relative {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 70px;
        }

        .dark-mode-btn {
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            border-radius: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .dark-mode-btn:hover {
            transform: translateY(-50%) scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        /* Dashboard cards */
        .dashboard-card {
            padding: 20px;
            border-radius: 8px;
            color: white;
            margin-bottom: 20px;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .users-card { background-color: var(--bs-info); }
        .category-card { background-color: var(--bs-success); }
        .product-card { background-color: var(--bs-warning); }
        .customer-card { background-color: var(--bs-danger); }
        .supplier-card { background-color: var(--bs-purple); }
        .purchase-card { background-color: #e83e8c; }
        .outgoing-card { background-color: var(--bs-blue); }

        .card-number {
            font-size: 24px;
            font-weight: bold;
            animation: countUp 2s ease-out;
        }

        .more-info {
            text-align: right;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .more-info:hover {
            color: white;
        }

        /* Table styles */
        .table-container {
            margin-top: 30px;
        }

        .table {
            width: 100%;
            margin-bottom: 1rem;
            border-collapse: collapse;
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .table-bordered {
            border: 1px solid #dee2e6;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            border: 1px solid #dee2e6;
        }

        /* Button styles */
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            border-radius: 0.25rem;
            border: 1px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-success {
            color: #fff;
            background-color: var(--bs-success);
            border-color: var(--bs-success);
        }

        .btn-info {
            color: #fff;
            background-color: var(--bs-info);
            border-color: var(--bs-info);
        }

        .btn-danger {
            color: #fff;
            background-color: var(--bs-danger);
            border-color: var(--bs-danger);
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .action-btn {
            margin: 0 2px;
            transition: all 0.3s ease;
        }

        /* Form controls */
        .form-control {
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 0.25rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .form-select {
            display: block;
            width: 100%;
            padding: 0.375rem 2.25rem 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 0.25rem;
            transition: all 0.3s ease;
        }

        /* Utility classes */
        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .justify-content-end {
            justify-content: flex-end;
        }

        .align-items-center {
            align-items: center;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }

        .mx-2 {
            margin-left: 0.5rem;
            margin-right: 0.5rem;
        }

        .p-4 {
            padding: 1.5rem;
        }

        /* Pagination */
        .pagination {
            display: flex;
            padding-left: 0;
            list-style: none;
        }

        .page-item {
            margin: 0 2px;
        }

        .page-link {
            position: relative;
            display: block;
            padding: 0.5rem 0.75rem;
            color: var(--bs-blue);
            text-decoration: none;
            background-color: #fff;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .page-item.active .page-link {
            background-color: var(--bs-blue);
            border-color: var(--bs-blue);
            color: white;
        }

        .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #fff;
            border-color: #dee2e6;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .col-md-3, .col-md-4, .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            .navbar-brand {
                font-size: 1.4rem;
            }
        }

        /* Dark mode styles */
        body.dark-mode {
            background-color: #1a1a1a;
            background-image: none;
            color: #ffffff;
        }

        body.dark-mode .table {
            color: #ffffff;
            background-color: #2d2d2d;
        }

        body.dark-mode .table-bordered {
            border-color: #404040;
        }

        body.dark-mode .table th,
        body.dark-mode .table td {
            border-color: #404040;
        }

        body.dark-mode .form-control,
        body.dark-mode .form-select {
            background-color: #2d2d2d;
            border-color: #404040;
            color: #ffffff;
        }

        .theme-switch {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            transition: background-color 0.3s ease;
        }

        .modal-content {
            background-color: #ffffff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            transform: scale(0.7);
            opacity: 0;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .modal.show .modal-content {
            transform: scale(1);
            opacity: 1;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        body.dark-mode .modal-content {
            background-color: #2d2d2d;
            color: #ffffff;
            border-color: #404040;
        }

        body.dark-mode .close {
            color: #ffffff;
        }

        body.dark-mode .navbar {
            background-color: #1a1a1a !important;
        }

        body.dark-mode .btn-light {
            background-color: #404040;
            color: #ffffff;
            border-color: #505050;
        }

        body.dark-mode .page-link {
            background-color: #2d2d2d;
            border-color: #404040;
            color: #ffffff;
        }

        body.dark-mode .page-item.disabled .page-link {
            background-color: #1a1a1a;
            border-color: #404040;
            color: #666666;
        }

        /* Card hover effects */
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                rgba(255,255,255,0) 0%,
                rgba(255,255,255,0.2) 50%,
                rgba(255,255,255,0) 100%
            );
            transition: left 0.5s ease;
        }

        .dashboard-card:hover::before {
            left: 100%;
        }

        /* Button hover effects */
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }

        .btn:active::after {
            width: 300px;
            height: 300px;
            opacity: 0;
        }

        /* Table row hover effect */
        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(0,0,0,0.05);
            transform: scale(1.01);
        }

        body.dark-mode .table tbody tr:hover {
            background-color: rgba(255,255,255,0.05);
        }

        /* Action buttons hover */
        .action-btn:hover {
            transform: scale(1.1);
        }

        /* Parallax header */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
            background-color: rgba(40, 167, 69, 0.95) !important;
        }

        /* Modal animations */
        .modal {
            transition: background-color 0.3s ease;
        }

        .modal-content {
            transform: scale(0.7);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .modal.show .modal-content {
            transform: scale(1);
            opacity: 1;
        }

        /* Form input effects */
        .form-control:focus, .form-select:focus {
            border-color: var(--bs-success);
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
            transform: translateY(-2px);
        }

        /* Pagination hover effects */
        .page-link:hover:not(.disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Loading animation for buttons */
        .btn-loading {
            position: relative;
            pointer-events: none;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: calc(50% - 10px);
            right: 10px;
            border: 2px solid #fff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: button-loading 0.8s linear infinite;
        }

        @keyframes button-loading {
            to {
                transform: rotate(360deg);
            }
        }

        /* Card number counter animation */
        @keyframes countUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Add these to your existing styles */
        .position-relative {
            position: relative;
        }

        .position-absolute {
            position: absolute;
        }

        .text-center {
            text-align: center;
        }

        .w-100 {
            width: 100%;
        }

        /* Add a subtle text shadow to the title */
        .navbar-brand {
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        /* Sidebar styles */
        .sidebar {
            width: 250px;
            height: 100%;
            background-color: #0E3D77;
            color: white;
            position: fixed;
            left: -250px;
            top: 0;
            transition: left 0.3s ease-in-out;
            padding-top: 60px;
            z-index: 999;
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            padding: 15px 20px;
            cursor: pointer;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            transition: background 0.3s;
            text-align: left;
        }
        
        .sidebar ul li:hover a {
            background-color: #8EB4E3;
        }

        .sidebar ul li.active a {
            background-color: #1A73E8;
        }

        /* Menu Icon styles */
        .menu-icon {
            font-size: 24px;
            cursor: pointer;
            background: #0E3D77;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            display: inline-block;
            transition: all 0.3s ease;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }

        .menu-icon:hover {
            background: #1A73E8;
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        body.dark-mode .menu-icon {
            background: #1A73E8;
            color: white;
        }

        body.dark-mode .menu-icon:hover {
            background: #2196F3;
        }

        @keyframes menuIconPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .menu-icon {
            animation: menuIconPulse 2s infinite;
        }

        .menu-icon:hover {
            animation: none;
        }

        /* Adjust main content for sidebar */
        .container-fluid {
            transition: margin-left 0.3s ease-in-out;
        }

        .container-fluid.sidebar-active {
            margin-left: 250px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container-fluid.sidebar-active {
                margin-left: 0;
            }
        }

        /* Update sidebar link styles */
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
            width: 100%;
            height: 100%;
        }

        .sidebar ul li {
            padding: 0;  /* Remove padding from li */
        }

        .sidebar ul li a {
            padding: 15px 20px;  /* Add padding to anchor instead */
            transition: background 0.3s;
            display: block;
        }

        .sidebar ul li:hover a {
            background-color: #8EB4E3;
        }

        .sidebar ul li.active a {
            background-color: #1A73E8;
        }

        /* Ensure links don't change color when visited */
        .sidebar ul li a:visited {
            color: white;
        }

        /* Add hover effect */
        .sidebar ul li a:hover {
            color: white;
            transform: translateX(5px);
            transition: transform 0.3s ease;
        }

        footer {
            width: 100%;
            background: #0E3D77;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            left: 0;
        }
        
        footer a {
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <span class="menu-icon" id="menuIcon" onclick="toggleSidebar()">☰</span>

    <div class="sidebar" id="sidebar">
        <ul>
            <li><a href="dashboard.php">📊 Dashboard Overview</a></li>
            <li class="active"><a href="inventory.php">📦 Inventory Management</a></li>
            <li><a href="orders.php">🚚 Order and logistics</a></li>
            <li><a href="suppliers.php">📈 Suppliers</a></li>
            <li><a href="#">⚙️ Settings</a></li>
        </ul>
    </div>

    <div class="container-fluid p-4">
        <!-- Top Navigation -->
        <nav>
            <div class="container-fluid position-relative">
                <span class="navbar-brand w-100 text-center">Inventory</span>
                <button onclick="toggleDarkMode()" class="btn btn-light position-absolute dark-mode-btn">
                    <i class="fas fa-moon"></i> Darkmode
                </button>
            </div>
        </nav>

        <!-- Dashboard Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="dashboard-card users-card">
                    <div>
                        <div class="card-number"><?php echo $users_count; ?></div>
                        <div>System Users</div>
                    </div>
                    <a href="#" class="more-info">More Info →</a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card category-card">
                    <div>
                        <div class="card-number"><?php echo $categories_count; ?></div>
                        <div>Category</div>
                    </div>
                    <a href="#" class="more-info">More Info →</a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card product-card">
                    <div>
                        <div class="card-number"><?php echo $products_count; ?></div>
                        <div>Product</div>
                    </div>
                    <a href="#" class="more-info">More Info →</a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card customer-card">
                    <div>
                        <div class="card-number"><?php echo $customers_count; ?></div>
                        <div>Customer</div>
                    </div>
                    <a href="#" class="more-info">More Info →</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="dashboard-card supplier-card">
                    <div>
                        <div class="card-number"><?php echo $suppliers_count; ?></div>
                        <div>Supplier</div>
                    </div>
                    <a href="#" class="more-info">More Info →</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card purchase-card">
                    <div>
                        <div class="card-number"><?php echo $purchases_count; ?></div>
                        <div>Total Purchase</div>
                    </div>
                    <a href="#" class="more-info">More Info →</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card outgoing-card">
                    <div>
                        <div class="card-number"><?php echo $outgoing_count; ?></div>
                        <div>Total Outgoing</div>
                    </div>
                    <a href="#" class="more-info">More Info →</a>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>List of Products</h5>
                <button class="btn btn-success" onclick="showAddProductModal()">+ Add Products</button>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <span>Show</span>
                        <select class="form-select mx-2" style="width: auto;">
                            <option>10</option>
                            <option>25</option>
                            <option>50</option>
                            <option>100</option>
                        </select>
                        <span>entries</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <input type="search" class="form-control" style="width: 200px;" placeholder="Search:">
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <select class="form-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <?php
                        if ($result_categories_list->num_rows > 0) {
                            while ($row = $result_categories_list->fetch_assoc()) {
                                echo '<option value="' . $row["name"] . '">' . $row["name"] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="priceFilter">
                        <option value="">Price Range</option>
                        <option value="0-50">$0 - $50</option>
                        <option value="51-100">$51 - $100</option>
                        <option value="101+">$101+</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="stockFilter">
                        <option value="">Stock Status</option>
                        <option value="instock">In Stock</option>
                        <option value="lowstock">Low Stock</option>
                        <option value="outofstock">Out of Stock</option>
                    </select>
                </div>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Qty.</th>
                        <th>Image</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_products_list->num_rows > 0) {
                        while ($row = $result_products_list->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . $row["id"] . '</td>';
                            echo '<td>' . $row["name"] . '</td>';
                            echo '<td>' . $row["price"] . '</td>';
                            echo '<td>' . $row["quantity"] . '</td>';
                            echo '<td><img src="' . ($row["image_path"] ? $row["image_path"] : 'placeholder.jpg') . '" width="50" height="50" alt="Product"></td>';
                            echo '<td>' . $row["category"] . '</td>';
                            echo '<td>
                                <button class="btn btn-info btn-sm action-btn" onclick="editProduct(' . $row["id"] . ')">Edit</button>
                                <button class="btn btn-danger btn-sm action-btn" onclick="deleteProduct(' . $row["id"] . ')">Delete</button>
                              </td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="7" class="text-center">No products found</td></tr>';
                    }
                    ?>
                </tbody>
            </table>

            <div class="d-flex justify-content-between align-items-center">
                <div>Showing 1 to <?php echo $result_products_list->num_rows; ?> entries</div>
                <nav>
                    <ul class="pagination">
                        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add New Product</h2>
            <form id="addProductForm" action="add_product.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input type="number" name="price" step="0.01" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="quantity" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php
                        // Reset the pointer to the beginning of the result set
                        $result_categories_list->data_seek(0);
                        if ($result_categories_list->num_rows > 0) {
                            while ($row = $result_categories_list->fetch_assoc()) {
                                echo '<option value="' . $row["id"] . '">' . $row["name"] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Product Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <button type="submit" class="btn btn-success mt-3">Add Product</button>
            </form>
        </div>
    </div>

    <script>
        // Dark mode toggle
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        }

        // Check for saved dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }

        // Search functionality
        document.querySelector('input[type="search"]').addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Add product modal
        function showAddProductModal() {
            const modal = document.getElementById('addProductModal');
            modal.classList.add('show');
            modal.style.display = 'block';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addProductModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Filter functionality
        function applyFilters() {
            const category = document.getElementById('categoryFilter').value;
            const price = document.getElementById('priceFilter').value;
            const stock = document.getElementById('stockFilter').value;
            
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const rowCategory = row.cells[5].textContent;
                const rowPrice = parseFloat(row.cells[2].textContent);
                const rowStock = parseInt(row.cells[3].textContent);
                
                let showRow = true;
                
                if (category && rowCategory !== category) showRow = false;
                if (price) {
                    const [min, max] = price.split('-').map(n => n.replace('+', ''));
                    if (max && (rowPrice < min || rowPrice > max)) showRow = false;
                    if (!max && rowPrice < min) showRow = false;
                }
                if (stock) {
                    if (stock === 'lowstock' && rowStock > 20) showRow = false;
                    if (stock === 'outofstock' && rowStock > 0) showRow = false;
                    if (stock === 'instock' && rowStock === 0) showRow = false;
                }
                
                row.style.display = showRow ? '' : 'none';
            });
        }

        // Add event listeners to filters
        document.getElementById('categoryFilter').addEventListener('change', applyFilters);
        document.getElementById('priceFilter').addEventListener('change', applyFilters);
        document.getElementById('stockFilter').addEventListener('change', applyFilters);

        // Add this after the showAddProductModal function
        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('addProductModal').style.display = 'none';
        });

        // Edit product function
        function editProduct(id) {
            // Redirect to edit page or show edit modal
            alert('Edit product with ID: ' + id);
        }

        // Delete product function
        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                window.location.href = 'delete_product.php?id=' + id;
            }
        }

        // Add the toggleSidebar function
        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            const container = document.querySelector(".container-fluid");
            sidebar.classList.toggle("active");
            container.classList.toggle("sidebar-active");
        }

        // Close sidebar when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById("sidebar");
            const menuIcon = document.getElementById("menuIcon");
            
            if (!sidebar.contains(event.target) && !menuIcon.contains(event.target) && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });
    </script>

    <footer><a href="logout.php"><strong>Logout</strong></a></footer>
</body>
</html>

