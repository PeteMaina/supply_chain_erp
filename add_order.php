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

// Define variables and initialize with empty values
$order_id = $customer_name = $status = $order_date = "";
$order_id_err = $customer_name_err = $status_err = $order_date_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate order ID
    if (empty(trim($_POST["order_id"]))) {
        $order_id_err = "Please enter an order ID.";
    } else {
        // Check if order ID already exists
        $sql = "SELECT id FROM orders WHERE order_id = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_order_id);
            
            // Set parameters
            $param_order_id = trim($_POST["order_id"]);
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {
                    $order_id_err = "This order ID already exists.";
                } else {
                    $order_id = trim($_POST["order_id"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            
            // Close statement
            $stmt->close();
        }
    }
    
    // Validate customer name
    if (empty(trim($_POST["customer_name"]))) {
        $customer_name_err = "Please enter a customer name.";
    } else {
        $customer_name = trim($_POST["customer_name"]);
    }
    
    // Validate status
    if (empty(trim($_POST["status"]))) {
        $status_err = "Please select a status.";
    } else {
        $status = trim($_POST["status"]);
    }
    
    // Validate order date
    if (empty(trim($_POST["order_date"]))) {
        $order_date_err = "Please enter an order date.";
    } else {
        $order_date = trim($_POST["order_date"]);
    }
    
    // Check input errors before inserting in database
    if (empty($order_id_err) && empty($customer_name_err) && empty($status_err) && empty($order_date_err)) {
        // Check if customer exists, if not create a new one
        $customer_id = 0;
        $sql = "SELECT id FROM customers WHERE name = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_customer_name);
            
            // Set parameters
            $param_customer_name = $customer_name;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($customer_id);
                    $stmt->fetch();
                } else {
                    // Customer doesn't exist, create a new one
                    $stmt->close();
                    
                    $sql = "INSERT INTO customers (name) VALUES (?)";
                    
                    if ($stmt = $conn->prepare($sql)) {
                        // Bind variables to the prepared statement as parameters
                        $stmt->bind_param("s", $param_customer_name);
                        
                        // Set parameters
                        $param_customer_name = $customer_name;
                        
                        // Attempt to execute the prepared statement
                        if ($stmt->execute()) {
                            $customer_id = $conn->insert_id;
                        } else {
                            echo "Oops! Something went wrong. Please try again later.";
                        }
                        
                        // Close statement
                        $stmt->close();
                    }
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // Prepare an insert statement for order
        $sql = "INSERT INTO orders (order_id, customer_id, status, order_date) VALUES (?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("siss", $param_order_id, $param_customer_id, $param_status, $param_order_date);
            
            // Set parameters
            $param_order_id = $order_id;
            $param_customer_id = $customer_id;
            $param_status = $status;
            $param_order_date = $order_date;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Generate tracking number and add tracking entry if status is Shipped
                if ($status == "Shipped") {
                    $order_id_db = $conn->insert_id;
                    $tracking_number = "TRK" . str_pad(mt_rand(1, 999999), 6, "0", STR_PAD_LEFT);
                    
                    $sql = "INSERT INTO tracking (tracking_number, order_id, status, location, update_date) VALUES (?, ?, ?, 'Warehouse', NOW())";
                    
                    if ($stmt = $conn->prepare($sql)) {
                        // Bind variables to the prepared statement as parameters
                        $stmt->bind_param("sis", $param_tracking_number, $param_order_id_db, $param_status);
                        
                        // Set parameters
                        $param_tracking_number = $tracking_number;
                        $param_order_id_db = $order_id_db;
                        $param_status = $status;
                        
                        // Attempt to execute the prepared statement
                        $stmt->execute();
                        
                        // Close statement
                        $stmt->close();
                    }
                }
                
                // Redirect to orders page
                header("location: orders.php");
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            
            // Close statement
            $stmt->close();
        }
    }
    
    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #0E3D77;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            display: inline-block;
            background: #0E3D77;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn:hover {
            background: #1A73E8;
        }
        .error {
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add New Order</h2>
        <p>Please fill this form to add a new order.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Order ID</label>
                <input type="text" name="order_id" value="<?php echo $order_id; ?>">
                <span class="error"><?php echo $order_id_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Customer Name</label>
                <input type="text" name="customer_name" value="<?php echo $customer_name; ?>">
                <span class="error"><?php echo $customer_name_err; ?></span>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="Processing" <?php if ($status == "Processing") echo "selected"; ?>>Processing</option>
                    <option value="Shipped" <?php if ($status == "Shipped") echo "selected"; ?>>Shipped</option>
                    <option value="Delivered" <?php if ($status == "Delivered") echo "selected"; ?>>Delivered</option>
                </select>
                <span class="error"><?php echo $status_err; ?></span>
            </div>
            <div class="form-group">
                <label>Order Date</label>
                <input type="date" name="order_date" value="<?php echo $order_date; ?>">
                <span class="error"><?php echo $order_date_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Add Order">
                <a href="orders.php" class="btn">Cancel</a>
            </div>
        </form>
    </div>    
</body>
</html>

