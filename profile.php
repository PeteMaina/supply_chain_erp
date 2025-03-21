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

// Get user data
$user = [];
$activities = [];

// Check if user ID exists in session
if (isset($_SESSION["id"])) {
    $user_id = $_SESSION["id"];
    
    // Fetch user data
    $sql = "SELECT * FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
            }
        }
        $stmt->close();
    }
    
    // Fetch user activities
    $sql = "SELECT * FROM user_activity WHERE user_id = ? ORDER BY timestamp DESC LIMIT 10";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $activities = [];
            while ($row = $result->fetch_assoc()) {
                $activities[] = $row;
            }
        }
        $stmt->close();
    }
} else {
    // If no user ID in session, use username to find user
    $username = $_SESSION["username"];
    
    // Fetch user data
    $sql = "SELECT * FROM users WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                // Store user ID in session for future use
                $_SESSION["id"] = $user["id"];
                
                // Now fetch activities
                $user_id = $user["id"];
                $sql = "SELECT * FROM user_activity WHERE user_id = ? ORDER BY timestamp DESC LIMIT 10";
                if ($stmt2 = $conn->prepare($sql)) {
                    $stmt2->bind_param("i", $user_id);
                    if ($stmt2->execute()) {
                        $result2 = $stmt2->get_result();
                        while ($row = $result2->fetch_assoc()) {
                            $activities[] = $row;
                        }
                    }
                    $stmt2->close();
                }
            }
        }
        $stmt->close();
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>

<style>
    body {
    font-family: 'Poppins', sans-serif;
    background: #f4f4f4;
    margin: 0;
    padding: 0;
    scroll-behavior: smooth;
}

.profile-container {
    width: 80%;
    margin: auto;
}

.profile-section {
    min-height: 100vh;
    padding: 40px;
    background: white;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.profile-header {
    text-align: center;
    padding: 20px;
}

.profile-pic {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #00aaff;
}

h2 {
    margin-top: 10px;
}

.role {
    color: #00aaff;
    font-weight: bold;
}

.activity-list {
    list-style: none;
    padding: 0;
}

.activity-list li {
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

button {
    background: #00aaff;
    color: white;
    padding: 10px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}

button:hover {
    background: #0077cc;
}

/* Sidebar styles */
.sidebar {
    width: 250px;
    height: 100%;
    background-color: #0E3D77;
    color: white;
    position: fixed;
    left: -250px;
    top: 0;
    transition: left 0.3s ease-in-out;
    padding-top: 60px;
    z-index: 999;
}

.sidebar.active {
    left: 0;
}

.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar ul li {
    padding: 15px 20px;
    cursor: pointer;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    transition: background 0.3s;
    text-align: left;
}

.sidebar ul li:hover {
    background-color: #8EB4E3;
}

.sidebar ul li.active {
    background-color: #1A73E8;
}

.menu-icon {
    font-size: 24px;
    cursor: pointer;
    background: #0E3D77;
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    display: inline-block;
    transition: 0.3s;
    position: fixed;
    top: 15px;
    left: 15px;
    z-index: 1000;
}

.menu-icon:hover {
    background: #1A73E8;
}

a {
    text-decoration: none;
    color: white;
    display: block;
    width: 100%;
}

footer {
    width: 100%;
    background: #0E3D77;
    color: white;
    text-align: center;
    padding: 10px 0;
    position: fixed;
    bottom: 0;
    left: 0;
}

footer a {
    color: white;
    text-decoration: none;
}
</style>

<body>
    <span class="menu-icon" id="menuIcon" onclick="toggleSidebar()">☰</span>

    <div class="sidebar" id="sidebar">
        <ul>
            <li><a href="dashboard.php">📊 Dashboard Overview</a></li>
            <li><a href="inventory.php">📦 Inventory Management</a></li>
            <li><a href="orders.php">🚚 Order and logistics</a></li>
            <li><a href="suppliers.php">📈 Suppliers</a></li>
            <li class="active"><a href="profile.php">⚙️ Profile</a></li>
        </ul>
    </div>
    
    <div class="profile-container">
        
        <!-- 🔹 SECTION 1: Basic Info -->
        <section class="profile-section" id="overview">
            <div class="profile-header">
                <img src="<?= isset($user['profile_picture']) && !empty($user['profile_picture']) ? $user['profile_picture'] : 'default.jpg'; ?>" alt="Profile Picture" class="profile-pic">
                <h2><?= htmlspecialchars(isset($user['name']) ? $user['name'] : $_SESSION["username"]); ?></h2>
                <p class="role"><?= ucfirst(isset($user['role']) ? $user['role'] : 'User'); ?></p>
                <p class="email"><?= htmlspecialchars(isset($user['email']) ? $user['email'] : ''); ?></p>
                <p class="bio"><?= htmlspecialchars(isset($user['bio']) ? $user['bio'] : "No bio available."); ?></p>
            </div>
        </section>

        <!-- 🔹 SECTION 2: User Activity -->
        <section class="profile-section" id="activity">
            <h3>Recent Activity</h3>
            <ul class="activity-list">
                <?php if (count($activities) > 0): ?>
                    <?php foreach ($activities as $activity): ?>
                        <li><?= htmlspecialchars($activity['action']); ?> - <small><?= $activity['timestamp']; ?></small></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No recent activity</li>
                <?php endif; ?>
            </ul>
        </section>

        <!-- 🔹 SECTION 3: Settings -->
        <section class="profile-section" id="settings">
            <h3>Settings</h3>
            <form action="update_profile.php" method="POST">
                <input type="hidden" name="user_id" value="<?= isset($user['id']) ? $user['id'] : $_SESSION['id']; ?>">
                
                <label for="bio">Bio:</label>
                <textarea name="bio"><?= htmlspecialchars(isset($user['bio']) ? $user['bio'] : ''); ?></textarea>

                <label for="phone">Phone:</label>
                <input type="text" name="phone" value="<?= htmlspecialchars(isset($user['phone']) ? $user['phone'] : ''); ?>">

                <label for="address">Address:</label>
                <input type="text" name="address" value="<?= htmlspecialchars(isset($user['address']) ? $user['address'] : ''); ?>">

                <button type="submit">Save Changes</button>
            </form>
        </section>

    </div>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            const menuIcon = document.getElementById("menuIcon");
            
            sidebar.classList.toggle("active");
            
            if (sidebar.classList.contains("active")) {
                menuIcon.textContent = "✖";
            } else {
                menuIcon.textContent = "☰";
            }
        }
        
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
    
    <footer><a href="logout.php"><strong>Logout</strong></a></footer>
</body>
</html>