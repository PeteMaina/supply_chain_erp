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

// Check if product ID is provided
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    // Get product ID
    $id = trim($_GET["id"]);
    
    // Get product image path before deleting
    $sql = "SELECT image_path FROM products WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $param_id);
        
        // Set parameters
        $param_id = $id;
        
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $stmt->store_result();
            
            if ($stmt->num_rows == 1) {
                $stmt->bind_result($image_path);
                $stmt->fetch();
                
                // Delete product image if exists
                if (!empty($image_path) && file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        }
        
        // Close statement
        $stmt->close();
    }
    
    // Prepare a delete statement
    $sql = "DELETE FROM products WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $param_id);
        
        // Set parameters
        $param_id = $id;
        
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Records deleted successfully. Redirect to inventory page
            header("location: inventory.php");
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        
        // Close statement
        $stmt->close();
    }
    
    // Close connection
    $conn->close();
} else {
    // No product ID provided
    header("location: inventory.php");
    exit();
}
?>

