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

// Get suppliers
$sql = "SELECT * FROM suppliers ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1, h2 {
            text-align: center;
            color: #0E3D77;
        }
        .form-container {
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input, select, textarea, button {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #28a745;
            color: white;
            cursor: pointer;
            border: none;
        }
        button:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
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
        .sidebar ul li:hover {
            background-color: #8EB4E3;
        }
        .sidebar ul li.active {
            background-color: #1A73E8;
        }
        .menu-icon {
            font-size: 24px;
            cursor: pointer;
            background: #0E3D77;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            display: inline-block;
            transition: 0.3s;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1000;
        }
        .menu-icon:hover {
            background: #1A73E8;
        }
        a {
            text-decoration: none;
            color: white;
            display: block;
            width: 100%;
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
        .edit-btn, .delete-btn {
            padding: 5px 10px;
            margin: 0 5px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .edit-btn {
            background-color: #17a2b8;
            color: white;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <span class="menu-icon" id="menuIcon" onclick="toggleSidebar()">☰</span>

    <div class="sidebar" id="sidebar">
        <ul>
            <li><a href="dashboard.php">📊 Dashboard Overview</a></li>
            <li><a href="inventory.php">📦 Inventory Management</a></li>
            <li><a href="orders.php">🚚 Order and logistics</a></li>
            <li class="active"><a href="suppliers.php">📈 Suppliers</a></li>
            <li><a href="#">⚙️ Settings</a></li>
        </ul>
    </div>
    
    <div class="container">
        <h1>Suppliers Management</h1>
        <div class="form-container">
            <h2>Add New Supplier</h2>
            <form id="add-supplier-form" action="add_supplier.php" method="post">
                <input type="text" id="new-name" name="name" placeholder="Supplier Name" required>
                <input type="email" id="new-email" name="email" placeholder="Email" required>
                <input type="text" id="new-phone" name="phone" placeholder="Phone" required>
                <select id="new-category" name="category" required>
                    <option value="Engine Parts">Engine Parts</option>
                    <option value="Braking System">Braking System</option>
                    <option value="Suspension">Suspension</option>
                    <option value="Electrical Components">Electrical Components</option>
                    <option value="Tires">Tires</option>
                    <option value="Accessories">Accessories</option>
                </select>
                <select id="new-status" name="status" required>
                    <option value="Processing">Processing</option>
                    <option value="In Transit">In Transit</option>
                    <option value="Awaiting Shipment">Awaiting Shipment</option>
                    <option value="Delivered">Delivered</option>
                </select>
                <button type="submit">Add Supplier</button>
            </form>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="supplier-table-body">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $row["name"] . '</td>';
                        echo '<td>' . $row["email"] . '</td>';
                        echo '<td>' . $row["phone"] . '</td>';
                        echo '<td>' . $row["category"] . '</td>';
                        echo '<td>' . $row["status"] . '</td>';
                        echo '<td>
                                <button class="edit-btn" onclick="editSupplier(' . $row["id"] . ')">Edit</button>
                                <button class="delete-btn" onclick="deleteSupplier(' . $row["id"] . ')">Delete</button>
                              </td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="6">No suppliers found</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            const menuIcon = document.getElementById("menuIcon");
            
            sidebar.classList.toggle("active");
            
            if (sidebar.classList.contains("active")) {
                menuIcon.textContent = "✖";
            } else {
                menuIcon.textContent = "☰";
            }
        }
        
        function editSupplier(id) {
            window.location.href = 'edit_supplier.php?id=' + id;
        }
        
        function deleteSupplier(id) {
            if (confirm('Are you sure you want to delete this supplier?')) {
                window.location.href = 'delete_supplier.php?id=' + id;
            }
        }
    </script>

    <footer><a href="logout.php"><strong>Logout</strong></a></footer>
</body>
</html>

