<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: dashboard.php");
    exit;
}

// Include database connection
require_once "db_connect.php";

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();
                
                // Check if username exists, if yes then verify password
                if ($stmt->num_rows == 1) {                    
                    // Bind result variables
                    $stmt->bind_result($id, $username, $hashed_password);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;                            
                            
                            // Redirect user to dashboard page
                            header("location: dashboard.php");
                        } else {
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    // Username doesn't exist, display a generic error message
                    $login_err = "Invalid username or password.";
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
    <title>Supply Chain ERP Authentication</title>
    <link rel="shortcut icon" href="icon.png" type="image/x-icon">
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        a {
            text-decoration: none;
            margin-top: 5px;
            justify-content: center;
            color: #E1F5FE;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #0E3D77;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: white;
        }

        .auth-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            box-shadow: rgba(0, 0, 0, 1) 10px 60px 60px -7px;
            border-radius: 26px;
            background: linear-gradient(315deg, #0E3D77, #1A73E8);
            box-shadow:  -11px -11px 13px #115269, 1px 11px 13px  #080808;
        }

        .auth-box h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .form {
            display: none;
            margin: 0 auto;
        }

        .form.active {
            display: block;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .input-group input {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 25px;
            background-color: #E1F5FE;
            color: #333;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            box-shadow: 0 0 5px 1 #8EB4E3;
        }

        .btn-primary {
            width: 100%;
            padding: 10px;
            background-color: #1A73E8;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0E3D77;
            box-shadow:  0 7px 12px  rgba(0, 0, 0, 4);
        }

        .forgot-password {
            text-align: right;
            margin-top: 10px;
        }

        .forgot-password a {
            color: #8EB4E3;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .forgot-password a:hover {
            color: #1A73E8;
        }
        
        .error-message {
            color: #ff6b6b;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div id="loginForm" class="form active">
                <h2>Login to AMYSPARES ERP</h2>
                
                <?php 
                if(!empty($login_err)){
                    echo '<div class="error-message">' . $login_err . '</div>';
                }        
                ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="input-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Enter your username" value="<?php echo $username; ?>" required>
                        <?php if (!empty($username_err)) { echo '<span class="error-message">' . $username_err . '</span>'; } ?>
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <?php if (!empty($password_err)) { echo '<span class="error-message">' . $password_err . '</span>'; } ?>
                    </div>
                    <button type="submit" class="btn-primary">Login</button>
                    <p class="forgot-password"><a href="#" onclick="showForgotPassword()">Forgot Password?</a></p>
                </form>
            </div>

            <div id="forgotPasswordForm" class="form">
                <h2>Forgot Password</h2>
                <form>
                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <button type="button" class="btn-primary" onclick="sendResetLink()">Send Reset Link</button>
                    <p><a href="#" onclick="showLoginForm()">Back to Login</a></p>
                </form>
            </div>

            <div id="resetPasswordForm" class="form">
                <h2>Reset Password</h2>
                <form>
                    <div class="input-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password" required>
                    </div>
                    <div class="input-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password" required>
                    </div>
                    <button type="button" class="btn-primary" onclick="resetPassword()">Reset Password</button>
                    <p><a href="#" onclick="showLoginForm()">Back to Login</a></p>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle forms
        function showLoginForm() {
            document.getElementById('loginForm').classList.add('active');
            document.getElementById('forgotPasswordForm').classList.remove('active');
            document.getElementById('resetPasswordForm').classList.remove('active');
        }

        function showForgotPassword() {
            document.getElementById('forgotPasswordForm').classList.add('active');
            document.getElementById('loginForm').classList.remove('active');
            document.getElementById('resetPasswordForm').classList.remove('active');
        }

        function sendResetLink() {
            const email = document.getElementById('email').value.trim();
            if (!email) {
                alert('Please enter your email address.');
                return;
            }
            alert(`Password reset link sent to ${email}.`);
            showLoginForm(); // Return to login form after sending the link
        }

        function resetPassword() {
            const newPassword = document.getElementById('newPassword').value.trim();
            const confirmPassword = document.getElementById('confirmPassword').value.trim();

            if (!newPassword || !confirmPassword) {
                alert('Please enter both new password and confirmation.');
                return;
            }

            if (newPassword !== confirmPassword) {
                alert('Passwords do not match.');
                return;
            }

            alert('Password reset successful!');
            showLoginForm(); // Return to login form after resetting the password
        }

        // Default form on page load
        document.addEventListener('DOMContentLoaded', () => {
            showLoginForm();
        });
    </script>
</body>
</html>

