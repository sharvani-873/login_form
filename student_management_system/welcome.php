<?php
// MUST BE FIRST
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Welcome</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .welcome { color: green; font-size: 24px; }
    </style>
</head>
<body>
    <div class="welcome">
        Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
    </div>
    <p>You have successfully logged in.</p>
    <a href="view.php">View Students</a> | 
    <a href="logout.php">Logout</a>
</body>
</html>