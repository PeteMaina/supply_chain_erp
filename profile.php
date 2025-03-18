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

</style>

<body>
    <div class="profile-container">
        
        <!-- 🔹 SECTION 1: Basic Info -->
        <section class="profile-section" id="overview">
            <div class="profile-header">
                <img src="<?= $user['profile_picture'] ?: 'default.jpg'; ?>" alt="Profile Picture" class="profile-pic">
                <h2><?= htmlspecialchars($user['name']); ?></h2>
                <p class="role"><?= ucfirst($user['role']); ?></p>
                <p class="email"><?= htmlspecialchars($user['email']); ?></p>
                <p class="bio"><?= htmlspecialchars($user['bio'] ?: "No bio available."); ?></p>
            </div>
        </section>

        <!-- 🔹 SECTION 2: User Activity -->
        <section class="profile-section" id="activity">
            <h3>Recent Activity</h3>
            <ul class="activity-list">
                <?php foreach ($activities as $activity): ?>
                    <li><?= htmlspecialchars($activity['action']); ?> - <small><?= $activity['timestamp']; ?></small></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <!-- 🔹 SECTION 3: Settings -->
        <section class="profile-section" id="settings">
            <h3>Settings</h3>
            <form action="update_profile.php" method="POST">
                <label for="bio">Bio:</label>
                <textarea name="bio"><?= htmlspecialchars($user['bio']); ?></textarea>

                <label for="phone">Phone:</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']); ?>">

                <label for="address">Address:</label>
                <input type="text" name="address" value="<?= htmlspecialchars($user['address']); ?>">

                <button type="submit">Save Changes</button>
            </form>
        </section>

    </div>
</body>

<script>
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

</script>
</html>
