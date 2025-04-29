<?php
include('db.php');

// Handle form submission for adding student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $photo_path = '';

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo_name = $_FILES['photo']['name'];
        $photo_tmp_name = $_FILES['photo']['tmp_name'];
        $photo_size = $_FILES['photo']['size'];
        $upload_dir = 'uploads/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if ($photo_size <= 2 * 1024 * 1024) {
            $photo_path = $upload_dir . uniqid('photo_', true) . '.' . pathinfo($photo_name, PATHINFO_EXTENSION);
            move_uploaded_file($photo_tmp_name, $photo_path);
        }
    }

    $sql = "INSERT INTO students (name, email, phone, photo) VALUES ('$name', '$email', '$phone', '$photo_path')";
    if (mysqli_query($conn, $sql)) {
        header("Location: view.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Delete student
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM students WHERE id = $delete_id";
    if (mysqli_query($conn, $sql)) {
        header("Location: view.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Update student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $photo_path = mysqli_real_escape_string($conn, $_POST['existing_photo']);

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo_name = $_FILES['photo']['name'];
        $photo_tmp_name = $_FILES['photo']['tmp_name'];
        $photo_size = $_FILES['photo']['size'];
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if ($photo_size <= 2 * 1024 * 1024) {
            $photo_path = $upload_dir . uniqid('photo_', true) . '.' . pathinfo($photo_name, PATHINFO_EXTENSION);
            move_uploaded_file($photo_tmp_name, $photo_path);
        }
    }

    $sql = "UPDATE students SET name = '$name', email = '$email', phone = '$phone', photo = '$photo_path' WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        header("Location: view.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;
$sql = "SELECT * FROM students ORDER BY id DESC LIMIT $start, $limit";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
        }

        .container {
            width: 80%;
            margin: 30px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #4CAF50;
        }

        .add-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            float: right;
            margin-bottom: 10px;
        }

        .add-btn:hover {
            background-color: #45a049;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        td img {
            border-radius: 50%;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover {
            background-color: #45a049;
        }

        .pagination {
            text-align: center;
            margin-top: 20px;
        }

        .pagination a {
            color: #4CAF50;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
        }

        .pagination a:hover {
            background-color: #45a049;
            color: white;
        }

        /* Add Student Form Styles */
        .form-popup {
            display: none;
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-container {
            display: flex;
            flex-direction: column;
        }

        .form-container input,
        .form-container button {
            margin-bottom: 10px;
            padding: 10px;
            font-size: 14px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .cancel {
            background-color: #f44336;
        }

        .cancel:hover {
            background-color: #e53935;
        }
        .back-btn {
          background-color: #555;
          color: white;
          padding: 10px 15px;
          margin-bottom: 15px;
          border: none;
          cursor: pointer;
          border-radius: 4px;
        }

        .back-btn:hover {
          background-color: #333;
        }

    </style>
</head>
<body>
    <div class="container">
    <h2>Student Management System</h2>
    <a href="welcome.php"><button class="back-btn">Back</button></a>
    <button onclick="document.getElementById('addStudentForm').style.display='block'" class="add-btn">Add Student Data</button>

        <div id="addStudentForm" class="form-popup">
            <form action="view.php" method="POST" enctype="multipart/form-data" class="form-container">
                <h3>Add New Student</h3>
                <input type="hidden" name="action" value="add">
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="phone" placeholder="Phone" required>
                <input type="file" name="photo" accept="image/*" required>
                <button type="submit">Submit</button>
                <button type="button" class="cancel" onclick="document.getElementById('addStudentForm').style.display='none'">Close</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Name</th><th>Email</th><th>Phone</th><th>Photo</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $photo = !empty($row['photo']) ? $row['photo'] : 'default.jpg';
                    echo "<tr>
                        <td>{$row['name']}</td>
                        <td>{$row['email']}</td>
                        <td>{$row['phone']}</td>
                        <td><img src='$photo' width='50'></td>
                        <td>
                            <button onclick='updateStudent({$row['id']}, \"{$row['name']}\", \"{$row['email']}\", \"{$row['phone']}\", \"$photo\")'>Update</button>
                            <a href='view.php?delete_id={$row['id']}' onclick='return confirm(\"Are you sure?\")'><button>Delete</button></a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No students found.</td></tr>";
            }
            ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php
            $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM students");
            $total = mysqli_fetch_assoc($res)['total'];
            $pages = ceil($total / $limit);
            for ($i = 1; $i <= $pages; $i++) {
                echo "<a href='view.php?page=$i'>$i</a>";
            }
            ?>
        </div>
    </div>

    <script>
        function updateStudent(id, name, email, phone, photo) {
            const popup = document.createElement('div');
            popup.className = 'form-popup';
            popup.innerHTML = `
                <form action="view.php" method="POST" enctype="multipart/form-data" class="form-container">
                    <h3>Update Student</h3>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="${id}">
                    <input type="hidden" name="existing_photo" value="${photo}">
                    <input type="text" name="name" value="${name}" required>
                    <input type="email" name="email" value="${email}" required>
                    <input type="text" name="phone" value="${phone}" required>
                    <input type="file" name="photo" accept="image/*">
                    <button type="submit">Update</button>
                    <button type="button" class="cancel" onclick="this.closest('.form-popup').remove()">Close</button>
                </form>
            `;
            document.body.appendChild(popup);
        }
    </script>
</body>
</html>
