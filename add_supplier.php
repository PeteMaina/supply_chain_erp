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
$name = $email = $phone = $category = $status = "";
$name_err = $email_err = $phone_err = $category_err = $status_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a supplier name.";
    } else {
        $name = trim($_POST["name"]);
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Validate phone
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter a phone number.";
    } else {
        $phone = trim($_POST["phone"]);
    }
    
    // Validate category
    if (empty(trim($_POST["category"]))) {
        $category_err = "Please select a category.";
    } else {
        $category = trim($_POST["category"]);
    }
    
    // Validate status
    if (empty(trim($_POST["status"]))) {
        $status_err = "Please select a status.";
    } else {
        $status = trim($_POST["status"]);
    }
    
    // Check input errors before inserting in database
    if (empty($name_err) && empty($email_err) && empty($phone_err) && empty($category_err) && empty($status_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO suppliers (name, email, phone, category, status) VALUES (?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssss", $param_name, $param_email, $param_phone, $param_category, $param_status);
            
            // Set parameters
            $param_name = $name;
            $param_email = $email;
            $param_phone = $phone;
            $param_category = $category;
            $param_status = $status;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to suppliers page
                header("location: suppliers.php");
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
    <title>Add Supplier</title>
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
        <h2>Add New Supplier</h2>
        <p>Please fill this form to add a new supplier.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Supplier Name</label>
                <input type="text" name="name" value="<?php echo $name; ?>">
                <span class="error"><?php echo $name_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo $email; ?>">
                <span class="error"><?php echo $email_err; ?></span>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo $phone; ?>">
                <span class="error"><?php echo $phone_err; ?></span>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <option value="">Select Category</option>
                    <option value="Engine Parts" <?php if ($category == "Engine Parts") echo "selected"; ?>>Engine Parts</option>
                    <option value="Braking System" <?php if ($category == "Braking System") echo "selected"; ?>>Braking System</option>
                    <option value="Suspension" <?php if ($category == "Suspension") echo "selected"; ?>>Suspension</option>
                    <option value="Electrical Components" <?php if ($category == "Electrical Components") echo "selected"; ?>>Electrical Components</option>
                    <option value="Tires" <?php if ($category == "Tires") echo "selected"; ?>>Tires</option>
                    <option value="Accessories" <?php if ($category == "Accessories") echo "selected"; ?>>Accessories</option>
                </select>
                <span class="error"><?php echo $category_err; ?></span>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Select Status</option>
                    <option value="Processing" <?php if ($status == "Processing") echo "selected"; ?>>Processing</option>
                    <option value="In Transit" <?php if ($status == "In Transit") echo "selected"; ?>>In Transit</option>
                    <option value="Awaiting Shipment" <?php if ($status == "Awaiting Shipment") echo "selected"; ?>>Awaiting Shipment</option>
                    <option value="Delivered" <?php if ($status == "Delivered") echo "selected"; ?>>Delivered</option>
                  <?php if ($status == "Delivered") echo "selected"; ?>>Delivered</option>
                </select>
                <span class="error"><?php echo $status_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Add Supplier">
                <a href="suppliers.php" class="btn">Cancel</a>
            </div>
        </form>
    </div>    
</body>
</html>

