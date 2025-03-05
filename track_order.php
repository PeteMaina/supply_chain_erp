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
$tracking_number = "";
$tracking_number_err = "";
$tracking_result = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate tracking number
    if (empty(trim($_POST["tracking_number"]))) {
        $tracking_number_err = "Please enter a tracking number.";
    } else {
        $tracking_number = trim($_POST["tracking_number"]);
    }
    
    // Check input errors before querying database
    if (empty($tracking_number_err)) {
        // Prepare a select statement
        $sql = "SELECT t.tracking_number, t.status, t.location, t.update_date, o.order_id 
                FROM tracking t 
                JOIN orders o ON t.order_id = o.id 
                WHERE t.tracking_number = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_tracking_number);
            
            // Set parameters
            $param_tracking_number = $tracking_number;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $tracking_result = "
                        <div class='tracking-details'>
                            <h3>Tracking Information</h3>
                            <p><strong>Tracking Number:</strong> " . $row["tracking_number"] . "</p>
                            <p><strong>Order ID:</strong> " . $row["order_id"] . "</p>
                            <p><strong>Status:</strong> " . $row["status"] . "</p>
                            <p><strong>Location:</strong> " . $row["location"] . "</p>
                            <p><strong>Last Update:</strong> " . $row["update_date"] . "</p>
                        </div>";
                } else {
                    $tracking_result = "<p class='error'>No tracking information found for this number.</p>";
                }
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
    <title>Track Order</title>
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
        input {
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
        .tracking-details {
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #0E3D77;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Track Order</h2>
        <p>Enter the tracking number to get shipment details.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Tracking Number</label>
                <input type="text" name="tracking_number" value="<?php echo $tracking_number; ?>">
                <span class="error"><?php echo $tracking_number_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Track Order">
                <a href="orders.php" class="btn">Back to Orders</a>
            </div>
        </form>
        
        <?php echo $tracking_result; ?>
    </div>    
</body>
</html>

