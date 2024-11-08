<?php
// Include database connection file
require 'db_connection.php'; // Ensure to replace with your actual DB connection file

session_start(); // Start a session

// Initialize an empty message
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $access_code = htmlspecialchars($_POST['code']);  // Access code
    $password = $_POST['password']; // Password

    // Prepare and execute the query to fetch student by access code
    $stmt = $pdo->prepare("SELECT * FROM students WHERE code = ?");
    $stmt->execute([$access_code]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if student exists
    if ($student) {
        // Check if the student is approved (approved = 1)
        if ($student['approved'] == 1) {
            // Verify password and set session
            if (password_verify($password, $student['password'])) {
                $_SESSION['student_id'] = $student['id'];
                $_SESSION['student_username'] = $student['username'];
                $_SESSION['student_name'] = $student['name']; // Store name in session
                header("Location: Student_courses.php"); // Redirect to student portal
                exit();
            } else {
                $message = "<p style='color:red;'>Invalid password.</p>";
            }
        } else {
            // If student is not approved, show the message
            $message = "<p style='color:red;'>Your account is pending approval by the admin. Please try again later.</p>";
        }
    } else {
        $message = "<p style='color:red;'>Account not found.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            color: #666;
        }
        input[type="text"],
        input[type="password"],
        input[type="submit"] {
            width: 90%; /* Set width to 100% for all inputs */
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            width: 50%;
            display: block;
            margin: 0 auto; 
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .register-link {
            text-align: center;
            margin-top: 10px;
        }
        .register-link a {
            color: #007bff;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .forgot-password-link {
            text-align: center;
            margin-top: 10px;
        }
        .forgot-password-link a {
            color: #007bff;
            text-decoration: none;
        }
        .forgot-password-link a:hover {
            text-decoration: underline;
        }
        .alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Student Login</h2>

        <!-- Display the message if it's set -->
        <?php if ($message): ?>
            <div class="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="code">Access Code:</label>
            <input type="text" id="code" name="code" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" value="Login">
        </form>
        
        <!-- Register Link -->
        <div class="register-link">
            <p>Don't have an account? <a href="student_register.php">Register here</a></p>
        </div>

        <!-- Forgot Password Link -->
        <div class="forgot-password-link">
            <p style="text-align: center; margin-top: 10px;">
                <a href="reset_password.php" style="color: red; text-decoration: none;">Forgot Password?</a>
            </p>
        </div>
    </div>
</body>
</html>
