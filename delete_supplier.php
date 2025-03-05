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

// Check if supplier ID is provided
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    // Get supplier ID
    $id = trim($_GET["id"]);
    
    // Prepare a delete statement
    $sql = "DELETE FROM suppliers WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $param_id);
        
        // Set parameters
        $param_id = $id;
        
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Records deleted successfully. Redirect to suppliers page
            header("location: suppliers.php");
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
    // No supplier ID provided
    header("location: suppliers.php");
    exit();
}
?>

