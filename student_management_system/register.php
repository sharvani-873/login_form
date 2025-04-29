<?php
session_start();
require 'db.php'; // Your database connection file

$error = "";
$preserved_values = [
    'name' => '',
    'email' => '',
    'username' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and preserve form values
    $preserved_values = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'username' => trim($_POST['username'] ?? '')
    ];
    
    $password = trim($_POST['password'] ?? '');

    // Validate inputs
    if (empty($preserved_values['name']) || empty($preserved_values['email']) || 
        empty($preserved_values['username']) || empty($password)) {
        $error = "All fields are required!";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters!";
    } elseif (!filter_var($preserved_values['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Check if username exists
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $preserved_values['username']);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // 1. Insert into users (only username/password)
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $user_stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $user_stmt->bind_param("ss", $preserved_values['username'], $hashedPassword);
                $user_stmt->execute();

                // 2. Insert into students (without user_id)
                $student_stmt = $conn->prepare("INSERT INTO students (name, email) VALUES (?, ?)");
                $student_stmt->bind_param("ss", $preserved_values['name'], $preserved_values['email']);
                $student_stmt->execute();

                $conn->commit();
                $_SESSION['registration_success'] = true;
                header("Location: login.php");
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Registration failed: " . $e->getMessage();
            }
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .registration-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .error {
            color: #e74c3c;
            margin: 10px 0;
            padding: 10px;
            background: #fdecea;
            border-radius: 4px;
            display: <?= !empty($error) ? 'block' : 'none' ?>;
        }
        .submit-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            width: 100%;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .submit-btn:hover {
            background-color: #2980b9;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
        .login-link a {
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <h2>Create Student Account</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required 
                       value="<?= htmlspecialchars($preserved_values['name']) ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required 
                       value="<?= htmlspecialchars($preserved_values['email']) ?>">
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required 
                       value="<?= htmlspecialchars($preserved_values['username']) ?>">
            </div>

            <div class="form-group">
                <label for="password">Password (8+ characters)</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="submit-btn">Register</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>