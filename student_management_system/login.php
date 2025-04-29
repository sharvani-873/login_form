<?php
// MUST BE FIRST - NO WHITESPACE BEFORE THIS
session_start();
require 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password (assuming passwords are hashed)
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Debug output
                error_log("Login successful for user: ".$username);
                
                // Ensure no output has been sent
                if (!headers_sent()) {
                    header("Location: welcome.php");
                    exit();
                } else {
                    die("Redirect failed. Headers already sent.");
                }
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "User not found.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .error {
            color: #e74c3c;
            padding: 10px;
            background: #fdecea;
            border-radius: 4px;
            margin-bottom: 15px;
            display: <?= !empty($error) ? 'block' : 'none' ?>;
        }
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
        .btn-secondary {
            background-color: #f1f1f1;
            color: #333;
        }
        .btn-secondary:hover {
            background-color: #ddd;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required 
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            </div>
            
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            
            <div class="btn-group">
                <button type="submit" name="login" class="btn btn-primary">Login</button>
                <button type="button" onclick="window.location.href='register.php'" class="btn btn-secondary">Register</button>
                <button type="button" onclick="window.location.href='reset.php'" class="btn btn-secondary">Reset Password</button>
            </div>
        </form>
    </div>
</body>
</html>