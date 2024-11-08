<?php
// Include database connection file
require 'db_connection.php'; // Ensure to replace with your actual DB connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize form data
    $name = htmlspecialchars($_POST['name']); // New input for name
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $code = htmlspecialchars($_POST['code']);

    // Insert data into students table with 'approved' set to 0 (not approved)
    $stmt = $pdo->prepare("INSERT INTO students (name, username, email, password, code, approved) VALUES (?, ?, ?, ?, ?, 0)"); // Include 'approved' column as 0
    if ($stmt->execute([$name, $username, $email, $password, $code])) {
        // If registration is successful, alert the user
        echo "<script type='text/javascript'>alert('Registration successful! Your account is pending approval.'); window.location.href = 'student_login.php';</script>";
    } else {
        echo "<script type='text/javascript'>alert('Registration failed! Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 350px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
            color: #666;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="submit"],
        input[type="text"][id="code"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Student Registration</h2>
        <form method="POST" action="">
            <label for="name">Fullname:</label>
            <input type="text" id="name" name="name" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="code">Access Code:</label>
<input type="text" id="code" name="code" required readonly>

<p style="color: #cc0808; font-size: 14px; text-align: center;">Remember this access code, you will use it for login.</p>


            <input type="submit" value="Register">
        </form>
        
        <div class="footer">
            <p>Already have an account? <a href="student_login.php">Login here</a></p>
        </div>
    </div>

    <script>
        // Function to generate a 3 uppercase letter followed by 3 digits access code
        function generateCode() {
            var code = '';
            var letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; // Uppercase letters
            var numbers = '0123456789'; // Numbers

            // Generate 3 random uppercase letters
            for (var i = 0; i < 3; i++) {
                var randomLetter = letters.charAt(Math.floor(Math.random() * letters.length)); 
                code += randomLetter;
            }

            // Generate 3 random numbers
            for (var i = 0; i < 3; i++) {
                var randomNumber = numbers.charAt(Math.floor(Math.random() * numbers.length));
                code += randomNumber;
            }

            return code;
        }

        // Assign the generated code to the access code input field
        document.getElementById('code').value = generateCode();
    </script>
</body>
</html>
