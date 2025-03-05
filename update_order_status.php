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
$order_id = $status = "";
$order_id_err = $status_err = "";
$update_result = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate order ID
    if (empty(trim($_POST["order_id"]))) {
        $order_id_err = "Please enter an order ID.";
    } else {
        $order_id = trim($_POST["order_id"]);
    }
    
    // Validate status
    if (empty(trim($_POST["status"]))) {
        $status_err = "Please select a status.";
    } else {
        $status = trim($_POST["status"]);
    }
    
    // Check input errors before updating in database
    if (empty($order_id_err) && empty($status_err)) {
        // Prepare a select statement to check if order exists
        $sql = "SELECT id FROM orders WHERE order_id = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_order_id);
            
            // Set parameters
            $param_order_id = $order_id;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {
                    // Order exists, get the order ID
                    $stmt->bind_result($order_id_db);
                    $stmt->fetch();
                    
                    // Close statement
                    $stmt->close();
                    
                    // Prepare an update statement
                    $sql = "UPDATE orders SET status = ? WHERE id = ?";
                    
                    if ($stmt = $conn->prepare($sql)) {
                        // Bind variables to the prepared statement as parameters
                        $stmt->bind_param("si", $param_status, $param_order_id_db);
                        
                        // Set parameters
                        $param_status = $status;
                        $param_order_id_db = $order_id_db;
                        
                        // Attempt to execute the prepared statement
                        if ($stmt->execute()) {
                            // Add tracking entry if status is Shipped or Delivered
                            if ($status == "Shipped" || $status == "Delivered") {
                                // Check if tracking entry already exists
                                $stmt->close();
                                
                                $sql = "SELECT id FROM tracking WHERE order_id = ?";
                                
                                if ($stmt = $conn->prepare($sql)) {
                                    // Bind variables to the prepared statement as parameters
                                    $stmt->bind_param("i", $param_order_id_db);
                                    
                                    // Set parameters
                                    $param_order_id_db = $order_id_db;
                                    
                                    // Attempt to execute the prepared statement
                                    if ($stmt->execute()) {
                                        // Store result
                                        $stmt->store_result();
                                        
                                        if ($stmt->num_rows == 0) {
                                            // No tracking entry exists, create a new one
                                            $stmt->close();
                                            
                                            $tracking_number = "TRK" . str_pad(mt_rand(1, 999999), 6, "0", STR_PAD_LEFT);
                                            $location = ($status == "Shipped") ? "Distribution Center" : "Customer Address";
                                            
                                            $sql = "INSERT INTO tracking (tracking_number, order_id, status, location, update_date) VALUES (?, ?, ?, ?, NOW())";
                                            
                                            if ($stmt = $conn->prepare($sql)) {
                                                // Bind variables to the prepared statement as parameters
                                                $stmt->bind_param("siss", $param_tracking_number, $param_order_id_db, $param_status, $param_location);
                                                
                                                // Set parameters
                                                $param_tracking_number = $tracking_number;
                                                $param_order_id_db = $order_id_db;
                                                $param_status = $status;
                                                $param_location = $location;
                                                
                                                // Attempt to execute the prepared statement
                                                $stmt->execute();
                                                
                                                // Close statement
                                                $stmt->close();
                                            }
                                        } else {
                                            // Tracking entry exists, update it
                                            $stmt->close();
                                            
                                            $location = ($status == "Shipped") ? "Distribution Center" : "Customer Address";
                                            
                                            $sql = "UPDATE tracking SET status = ?, location = ?, update_date = NOW() WHERE order_id = ?";
                                            
                                            if ($stmt = $conn->prepare($sql)) {
                                                // Bind variables to the prepared statement as parameters
                                                $stmt->bind_param("ssi", $param_status, $param_location, $param_order_id_db);
                                                
                                                // Set parameters
                                                $param_status = $status;
                                                $param_location = $location;
                                                $param_order_id_db = $order_id_db;
                                                
                                                // Attempt to execute the prepared statement
                                                $stmt->execute();
                                                
                                                // Close statement
                                                $stmt->close();
                                            }
                                        }
                                    }
                                }
                            }
                            
                            $update_result = "<p class='success'>Order status updated successfully!</p>";
                        } else {
                            $update_result = "<p class='error'>Oops! Something went wrong. Please try again later.</p>";
                        }
                        
                        // Close statement
                        $stmt->close();
                    }
                } else {
                    $order_id_err = "No order found with this ID.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
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
    <title>Update Order Status</title>
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
        .success {
            color: green;
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Update Order Status</h2>
        <p>Please fill this form to update an order's status.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Order ID</label>
                <input type="text" name="order_id" value="<?php echo $order_id; ?>">
                <span class="error"><?php echo $order_id_err; ?></span>
            </div>
            <div class="form-group">
                <label>New Status</label>
                <select name="status">
                    <option value="Processing" <?php if ($status == "Processing") echo "selected"; ?>>Processing</option>
                    <option value="Shipped" <?php if ($status == "Shipped") echo "selected"; ?>>Shipped</option>
                    <option value="Delivered" <?php if ($status == "Delivered") echo "selected"; ?>>Delivered</option>
                    <option value="Returned" <?php if ($status == "Returned") echo "selected"; ?>>Returned</option>
                </select>
                <span class="error"><?php echo $status_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Update Status">
                <a href="orders.php" class="btn">Back to Orders</a>
            </div>
        </form>
        
        <?php echo $update_result; ?>
    </div>    
</body>
</html>

