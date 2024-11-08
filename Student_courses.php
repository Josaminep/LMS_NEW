<?php
// Database connection
include 'db_connection.php';
session_start(); // Start session to access logged-in student data

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php"); // Redirect to login if not logged in
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch all courses
$courses = $pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);

// Handle enrollment
if (isset($_POST['enroll'])) {
    $course_id = $_POST['course_id'];

    // Check if the student is already enrolled in the course
    $checkEnroll = $pdo->prepare("SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?");
    $checkEnroll->execute([$student_id, $course_id]);

    if ($checkEnroll->rowCount() == 0) {
        // Insert enrollment
        $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
        if ($stmt->execute([$student_id, $course_id])) {
            echo "<p>Enrolled successfully!</p>";
        } else {
            // Log error details for debugging
            error_log(print_r($stmt->errorInfo(), true));
            echo "<p>Enrollment failed. Please try again.</p>";
        }
    } else {
        echo "<p>You are already enrolled in this course.</p>";
    }
}

// Fetch enrolled courses
$enrolled_courses = $pdo->prepare("SELECT c.* FROM courses c
                                    JOIN enrollments e ON c.id = e.course_id
                                    WHERE e.student_id = ?");
$enrolled_courses->execute([$student_id]);
$enrolled_courses = $enrolled_courses->fetchAll(PDO::FETCH_ASSOC);

// Function to get course progress
function getCourseProgress($pdo, $student_id, $course_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM completed_modules WHERE student_id = ? AND module_id IN 
                            (SELECT id FROM modules WHERE course_id = ?)");
    $stmt->execute([$student_id, $course_id]);
    return $stmt->fetchColumn(); // Returns the number of completed modules for this course
}
?>




    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="styles.css">
        <title>All Courses</title>
    </head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fff;
            padding: 10px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .logo img {
            height: 40px; /* Adjust as needed */
        }

        nav ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        nav ul li {
            margin-left: 60px;
        }

        nav ul li a {
            text-decoration: none;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
        }

        .search-bar {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .search-bar input {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .course-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 30px;
            padding: 0 40px;
            position: relative;
            margin-top: 100px;
        }

        .course-button {
            background-color: #007BFF;
            color: #fff;
            border: none;
            padding: 100px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .course-button:hover {
            background-color: #0056b3;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
        }

        .pagination-button {
            background-color: #007BFF;
            color: #fff;
            border: none;
            padding: 10px 15px;
            margin: 0 5px;
            border-radius: 5px;
            cursor: pointer;
        }

        .pagination-button:hover {
            background-color: #0056b3;
        }

        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0, 0, 0, 0.5); /* Black w/ opacity */
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
            max-width: 500px;
            border-radius: 5px;
        }

        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-action {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            position: relative;
            margin-left: 400px;
        }

        .modal-action:hover {
            background-color: #0056b3;
            
        }
        h2{
            background-color: #0056b3;
            text-align: center;
            padding: 10px;
            border-radius: 10px;
            margin-top: 40px;
            margin-bottom: 50px;

        }
        .close-button{
            background-color: #0056b3;
            border-radius: 60px;
            position: relative;
            margin-right: 480px;
            bottom: 15px;
            width: 30px;
            text-align: center;
            

        }








            .container {
                max-width: 1200px;
                margin: 0 auto;
            }
            .course {
                background-color: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 16px;
                margin: 10px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                width: 300px;
                height: 300px;
                display: inline-block;
                vertical-align: top;
                text-align: center;
                overflow: hidden;
                background-color: #4682B4;

            }
            .course img {
                max-width: 100%;
                height: 150px;
                object-fit: cover;
                border-radius: 8px;
                margin-bottom: 10px;
            }
            .course h3 {
                margin: 0 0 10px;
                color: #333;
                font-size: 18px;
            }
            .button {
                display: inline-block;
                margin-top: 10px;
                padding: 10px 15px;
                background-color: #007bff;
                color: #fff;
                text-decoration: none;
                border-radius: 5px;
                cursor: pointer;
            }
            .button:hover {
                background-color: #0056b3;
            }
            /* Modal Styles */
            .modal {
                display: none;
                position: fixed;
                z-index: 1;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                overflow: auto;
            }
            .modal-content {
                background-color: #fff;
                margin: 10% auto;
                padding: 20px;
                border: 1px solid #ddd;
                width: 80%;
                max-width: 500px;
                border-radius: 8px;
            }
            .close {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
            }
            .close:hover {
                color: #000;
            }
            .progress-bar {
                width: 100%;
                background-color: #f3f3f3;
                border-radius: 8px;
                margin-bottom: 20px;
            }
            .progress {
                height: 24px;
                background-color: #4caf50;
                border-radius: 8px;
                text-align: center;
                color: #fff;
                line-height: 24px;
            }

            .button {
            display: inline-block;
            padding: 10px 15px;
            background-color: #4caf50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            transition: background-color 0.3s;
        
    }

        .button:hover {
            background-color: #45a049;
        }
    </style>
    <body>
    <header>
    <div class="logo">
        <img src="./images/logo.png" alt="e-Journo Eskwela" />
    </div>
    <nav>
        <ul>
            <li><a href="Student_courses.php">HOME</a></li>
            <li><a href="about.php">ABOUT US</a></li>
            <?php
            // Check if the user is logged in
            if (isset($_SESSION['student_username'])) {
                // Display the registered username
                echo '<li><strong><a href="profile_students.php">' . htmlspecialchars($_SESSION['student_username']) . '</a></strong></li>';
                // Add a logout link
                echo '<li><a href="logout.php">Logout</a></li>';
            } else {
                // Display a login link if not logged in
                echo '<li><a href="student_login.php">Login</a></li>';
            }
            ?>
        </ul>
    </nav>
</header>


    <main>
    <h1>ALL COURSES</h1>
    <div class="search-bar">
        <input type="text" placeholder="Search">
    </div>

    <div class="container">
        <h2>All Courses</h2>
        <?php foreach ($courses as $course): ?>
            <div class="course">
            <img src="<?php echo htmlspecialchars($course['course_image']); ?>" alt="<?php echo htmlspecialchars($course['course_name']); ?>">

                <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                <button class="button view-description" data-course-id="<?php echo $course['id']; ?>" data-course-description="<?php echo htmlspecialchars($course['course_description']); ?>">View Description</button>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<div id="descriptionModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Course Description</h3>
            <p id="modalDescription"></p>
            <form method="POST" action="">
                <input type="hidden" name="course_id" id="modalCourseId">
                <input type="submit" name="enroll" value="Enroll" class="button">
            </form>
        </div>
    </div>


<script>
        // Get modal element
        var modal = document.getElementById("descriptionModal");

        // Get modal description element
        var modalDescription = document.getElementById("modalDescription");
        var modalCourseId = document.getElementById("modalCourseId");

        // Get all "View Description" buttons
        var viewButtons = document.querySelectorAll(".view-description");

        // When the user clicks a button, open the modal and set its content
        viewButtons.forEach(function(button) {
            button.onclick = function() {
                var courseId = this.getAttribute('data-course-id');
                var courseDescription = this.getAttribute('data-course-description');
                modalDescription.textContent = courseDescription;
                modalCourseId.value = courseId; // Set the course ID for enrollment
                modal.style.display = "block"; // Show the modal
            };
        });

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        };

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        };
    </script>
</body>
</html>