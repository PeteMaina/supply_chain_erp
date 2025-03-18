<?php
session_start();
require 'db.php'; // Database connection

$userId = $_SESSION['user_id']; // Get logged-in user ID

// Fetch user info
$query = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$query->execute([$userId]);
$user = $query->fetch(PDO::FETCH_ASSOC);

// Fetch recent activity
$activityQuery = $pdo->prepare("SELECT * FROM user_activity WHERE user_id = ? ORDER BY timestamp DESC LIMIT 10");
$activityQuery->execute([$userId]);
$activities = $activityQuery->fetchAll(PDO::FETCH_ASSOC);
?>
