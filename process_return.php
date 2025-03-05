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
$order_id = $customer_name = $reason = $comments = $type = $new_item = "";
$order_id_err = $customer_name_err = $reason_err = $type_err = $new_item_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate order ID
    if (empty(trim($_POST["order_id"]))) {
        $order_id_err = "Please enter an order ID.";
    } else {
        // Check if order ID exists
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
                    // Order exists, get the order ID
                    $stmt->bind_result($order_id_db);
                    $stmt->fetch();
                    $order_id = trim($_POST["order_id"]);
                } else {
                    $order_id_err = "No order found with this ID.";
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
    
    // Validate reason
    if (empty(trim($_POST["reason"]))) {
        $reason_err = "Please select a reason.";
    } else {
        $reason = trim($_POST["reason"]);
    }
    
    // Get comments
    $comments = trim($_POST["comments"]);
    
    // Validate type
    if (empty(trim($_POST["type"]))) {
        $type_err = "Please select a type.";
    } else {
        $type = trim($_POST["type"]);
    }
    
    // Validate new item if type is Exchange
    if ($type == "Exchange" && empty(trim($_POST["new_item"]))) {
        $new_item_err = "Please enter a new item.";
    } else {
        $new_item = trim($_POST["new_item"]);
    }
    
    // Check input errors before inserting in database
    if (empty($order_id_err) && empty($customer_name_err) && empty($reason_err) && empty($type_err) && ($type != "Exchange" || empty($new_item_err))) {
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
        
        if ($type == "Return") {
            // Prepare an insert statement for return
            $sql = "INSERT INTO returns (order_id, customer_id, reason, comments, status) VALUES (?, ?, ?, ?, 'Pending')";
            
            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("iiss", $param_order_id_db, $param_customer_id, $param_reason, $param_comments);
                
                // Set parameters
                $param_order_id_db = $order_id_db;
                $param_customer_id = $customer_id;
                $param_reason = $reason;
                $param_comments = $comments;
                
                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    // Update order status
                    $stmt->close();
                    
                    $sql = "UPDATE orders SET status = 'Returned' WHERE id = ?";
                    
                    if ($stmt = $conn->prepare($sql)) {
                        // Bind variables to the prepared statement as parameters
                        $stmt->bind_param("i", $param_order_id_db);
                        
                        // Set parameters
                        $param_order_id_db = $order_id_db;
                        
                        // Attempt to execute the prepared statement
                        $stmt->execute();
                        
                        // Close statement
                        $stmt->close();
                    }
                    
                    // Redirect to orders page
                    header("location: orders.php?success=return");
                    exit();
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }
                
                // Close statement
                $stmt->close();
            }
        } else {
            // Prepare an insert statement for exchange
            $sql = "INSERT INTO exchanges (order_id, new_item, reason, status) VALUES (?, ?, ?, 'Pending')";
            
            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("iss", $param_order_id_db, $param_new_item, $param_reason);
                
                // Set parameters
                $param_order_id_db = $order_id_db;
                $param_new_item = $new_item;
                $param_reason = $reason;
                
                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    // Update order status
                    $stmt->close();
                    
                    $sql = "UPDATE orders SET status = 'Exchange Pending' WHERE id = ?";
                    
                    if ($stmt = $conn->prepare($sql)) {
                        // Bind variables to the prepared statement as parameters
                        $stmt->bind_param("i", $param_order_id_db);
                        
                        // Set parameters
                        $param_order_id_db = $order_id_db;
                        
                        // Attempt to execute the prepared statement
                        $stmt->execute();
                        
                        // Close statement
                        $stmt->close();
                    }
                    
                    // Redirect to orders page
                    header("location: orders.php?success=exchange");
                    exit();
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }
                
                // Close statement
                $stmt->close();
            }
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
    <title>Process Return/Exchange</title>
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
        input, select, textarea {
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
        <h2>Process Return/Exchange</h2>
        <p>Please fill this form to process a return or exchange.</p>
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
                <label>Reason</label>
                <select name="reason">
                    <option value="">Select Reason</option>
                    <option value="Damaged" <?php if ($reason == "Damaged") echo "selected"; ?>>Damaged</option>
                    <option value="Wrong Item" <?php if ($reason == "Wrong Item") echo "selected"; ?>>Wrong Item</option>
                    <option value="Not as Described" <?php if ($reason == "Not as Described") echo "selected"; ?>>Not as Described</option>
                    <option value="Other" <?php if ($reason == "Other") echo "selected"; ?>>Other</option>
                </select>
                <span class="error"><?php echo $reason_err; ?></span>
            </div>
            <div class="form-group">
                <label>Comments</label>
                <textarea name="comments" rows="3"><?php echo $comments; ?></textarea>
            </div>
            <div class="form-group">
                <label>Type</label>
                <select name="type" id="type" onchange="toggleNewItemField()">
                    <option value="">Select Type</option>
                    <option value="Return" <?php if ($type == "Return") echo "selected"; ?>>Return</option>
                    <option value="Exchange" <?php if ($type == "Exchange") echo "selected"; ?>>Exchange</option>
                </select>
                <span class="error"><?php echo $type_err; ?></span>
            </div>
            <div class="form-group" id="newItemGroup" style="display: <?php echo ($type == 'Exchange') ? 'block' : 'none'; ?>">
                <label>New Item</label>
                <input type="text" name="new_item" value="<?php echo $new_item; ?>">
                <span class="error"><?php echo $new_item_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Process">
                <a href="orders.php" class="btn">Cancel</a>
            </div>
        </form>
    </div>
    
    <script>
        function toggleNewItemField() {
            var type = document.getElementById('type').value;
            var newItemGroup = document.getElementById('newItemGroup');
            
            if (type === 'Exchange') {
                newItemGroup.style.display = 'block';
            } else {
                newItemGroup.style.display = 'none';
            }
        }
    </script>
</body>
</html>

