<?php
// Database connection parameters
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password (empty)

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS supply_erp";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db("supply_erp");

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'users' created successfully<br>";
} else {
    echo "Error creating table 'users': " . $conn->error . "<br>";
}

// Create categories table
$sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'categories' created successfully<br>";
} else {
    echo "Error creating table 'categories': " . $conn->error . "<br>";
}

// Create products table
$sql = "CREATE TABLE IF NOT EXISTS products (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT(11) NOT NULL DEFAULT 0,
    category_id INT(11),
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'products' created successfully<br>";
} else {
    echo "Error creating table 'products': " . $conn->error . "<br>";
}

// Create customers table
$sql = "CREATE TABLE IF NOT EXISTS customers (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'customers' created successfully<br>";
} else {
    echo "Error creating table 'customers': " . $conn->error . "<br>";
}

// Create suppliers table
$sql = "CREATE TABLE IF NOT EXISTS suppliers (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    category VARCHAR(100),
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'suppliers' created successfully<br>";
} else {
    echo "Error creating table 'suppliers': " . $conn->error . "<br>";
}

// Create orders table
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(20) NOT NULL UNIQUE,
    customer_id INT(11),
    status VARCHAR(50) NOT NULL,
    order_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'orders' created successfully<br>";
} else {
    echo "Error creating table 'orders': " . $conn->error . "<br>";
}

// Create order_items table
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    order_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'order_items' created successfully<br>";
} else {
    echo "Error creating table 'order_items': " . $conn->error . "<br>";
}

// Create tracking table
$sql = "CREATE TABLE IF NOT EXISTS tracking (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    tracking_number VARCHAR(50) NOT NULL UNIQUE,
    order_id INT(11) NOT NULL,
    status VARCHAR(50) NOT NULL,
    location VARCHAR(100),
    update_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'tracking' created successfully<br>";
} else {
    echo "Error creating table 'tracking': " . $conn->error . "<br>";
}

// Create carriers table
$sql = "CREATE TABLE IF NOT EXISTS carriers (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20),
    email VARCHAR(100),
    status VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'carriers' created successfully<br>";
} else {
    echo "Error creating table 'carriers': " . $conn->error . "<br>";
}

// Create delivery_summary table
$sql = "CREATE TABLE IF NOT EXISTS delivery_summary (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    delivery_date DATE NOT NULL,
    total_deliveries INT(11) NOT NULL,
    successful_deliveries INT(11) NOT NULL,
    failed_deliveries INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'delivery_summary' created successfully<br>";
} else {
    echo "Error creating table 'delivery_summary': " . $conn->error . "<br>";
}

// Create returns table
$sql = "CREATE TABLE IF NOT EXISTS returns (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    order_id INT(11) NOT NULL,
    customer_id INT(11),
    reason VARCHAR(100) NOT NULL,
    comments TEXT,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'returns' created successfully<br>";
} else {
    echo "Error creating table 'returns': " . $conn->error . "<br>";
}

// Create exchanges table
$sql = "CREATE TABLE IF NOT EXISTS exchanges (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    order_id INT(11) NOT NULL,
    new_item VARCHAR(100) NOT NULL,
    reason VARCHAR(100) NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'exchanges' created successfully<br>";
} else {
    echo "Error creating table 'exchanges': " . $conn->error . "<br>";
}

// Insert default admin user
$hashed_password = password_hash("12345678", PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, password, email) 
        VALUES ('AMYSPARES', '$hashed_password', 'admin@amyspares.com')
        ON DUPLICATE KEY UPDATE username = 'AMYSPARES'";

if ($conn->query($sql) === TRUE) {
    echo "Default admin user created successfully<br>";
} else {
    echo "Error creating default admin user: " . $conn->error . "<br>";
}

// Insert sample categories
$categories = [
    "Engine Parts",
    "Tires",
    "Accessories",
    "Braking System",
    "Electrical Components"
];

foreach ($categories as $category) {
    $sql = "INSERT INTO categories (name) 
            VALUES ('$category')
            ON DUPLICATE KEY UPDATE name = '$category'";
    
    if ($conn->query($sql) === TRUE) {
        echo "Category '$category' created successfully<br>";
    } else {
        echo "Error creating category '$category': " . $conn->error . "<br>";
    }
}

echo "<p>Database setup completed. <a href='auth.php'>Go to login page</a></p>";

$conn->close();
?>

