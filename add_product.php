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
$name = $price = $quantity = $category_id = "";
$name_err = $price_err = $quantity_err = $category_id_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a product name.";
    } else {
        $name = trim($_POST["name"]);
    }
    
    // Validate price
    if (empty(trim($_POST["price"]))) {
        $price_err = "Please enter a price.";
    } elseif (!is_numeric($_POST["price"]) || $_POST["price"] < 0) {
        $price_err = "Please enter a valid price.";
    } else {
        $price = trim($_POST["price"]);
    }
    
    // Validate quantity
    if (empty(trim($_POST["quantity"]))) {
        $quantity_err = "Please enter a quantity.";
    } elseif (!is_numeric($_POST["quantity"]) || $_POST["quantity"] < 0) {
        $quantity_err = "Please enter a valid quantity.";
    } else {
        $quantity = trim($_POST["quantity"]);
    }
    
    // Validate category
    if (empty(trim($_POST["category_id"]))) {
        $category_id_err = "Please select a category.";
    } else {
        $category_id = trim($_POST["category_id"]);
    }
    
    // Check input errors before inserting in database
    if (empty($name_err) && empty($price_err) && empty($quantity_err) && empty($category_id_err)) {
        // Handle file upload
        $image_path = "";
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $target_dir = "uploads/";
            
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Check if image file is an actual image
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check !== false) {
                // Generate unique filename
                $new_filename = uniqid() . "." . $imageFileType;
                $target_file = $target_dir . $new_filename;
                
                // Upload file
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_path = $target_file;
                }
            }
        }
        
        // Prepare an insert statement
        $sql = "INSERT INTO products (name, price, quantity, category_id, image_path) VALUES (?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sdiis", $param_name, $param_price, $param_quantity, $param_category_id, $param_image_path);
            
            // Set parameters
            $param_name = $name;
            $param_price = $price;
            $param_quantity = $quantity;
            $param_category_id = $category_id;
            $param_image_path = $image_path;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to inventory page
                header("location: inventory.php");
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
    <title>Add Product</title>
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
        <h2>Add New Product</h2>
        <p>Please fill this form to add a new product.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" value="<?php echo $name; ?>">
                <span class="error"><?php echo $name_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Price</label>
                <input type="number" name="price" step="0.01" value="<?php echo $price; ?>">
                <span class="error"><?php echo $price_err; ?></span>
            </div>
            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" value="<?php echo $quantity; ?>">
                <span class="error"><?php echo $quantity_err; ?></span>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id">
                    <option value="">Select Category</option>
                    <?php
                    // Reconnect to database
                    require_once "db_connect.php";
                    
                    // Get categories
                    $sql = "SELECT id, name FROM categories ORDER BY name";
                    $result = $conn->query($sql);
                    
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($category_id == $row["id"]) ? "selected" : "";
                            echo '<option value="' . $row["id"] . '" ' . $selected . '>' . $row["name"] . '</option>';
                        }
                    }
                    
                    $conn->close();
                    ?>
                </select>
                <span class="error"><?php echo $category_id_err; ?></span>
            </div>
            <div class="form-group">
                <label>Product Image</label>
                <input type="file" name="image" accept="image/*">
            </div>
            <div class="form-group">
                <input type="submit" class="btn" value="Add Product">
                <a href="inventory.php" class="btn">Cancel</a>
            </div>
        </form>
    </div>    
</body>
</html>

