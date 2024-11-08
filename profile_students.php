<?php
// Database connection
session_start(); // Start session to access logged-in student data

include 'db_connection.php';

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
            echo "<p>Enrollment failed. Please try again.</p>";
        }
    } else {
        echo "<p>You are already enrolled in this course.</p>";
    }
}

// Handle unenrollment
if (isset($_POST['unenroll'])) {
    $course_id = $_POST['course_id'];

    // Remove the enrollment
    $stmt = $pdo->prepare("DELETE FROM enrollments WHERE student_id = ? AND course_id = ?");
    if ($stmt->execute([$student_id, $course_id])) {
        echo "<p>Unenrolled successfully!</p>";
    } else {
        echo "<p>Unenrollment failed. Please try again.</p>";
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
    <title>My Courses - Hitoka Yachi</title>
    <link rel="stylesheet" href="styles.css">
</head>
<style>
    body {
        font-family: 'Arial', sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f7f8fc;
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
    .profile img {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin-right: 5px;
    }

    .profile-section {
        display: flex;
        align-items: center;
        background-color: #ffffff;
        padding: 20px;
        margin: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        background-image: url('./images/background.jpg'); /* Replace with your background image */
        background-size: cover;
        background-position: center;
        color: #fff;
    }

    .profile-img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        margin-right: 20px;
    }

    .profile-info h1 {
        margin: 0;
        font-size: 24px;
        color: #333;
    }

    .profile-info p {
        margin: 5px 0 0;
        color: #666;
    }

    .courses {
        padding: 20px;
        margin: 20px;
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .courses h2 {
        font-size: 20px;
        margin-bottom: 20px;
        border-bottom: 2px solid #2563eb;
        display: inline-block;
        padding-bottom: 5px;
    }

    .filter select {
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .course-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 100px;
        
    }

    .course-card {
        background-color: #ffffff;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid black;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
        height: 22vh;
    }

    .course-card:hover {
        transform: translateY(-5px);
    }

    .course-card h3 {
        margin: 0 0 10px;
        font-size: 18px;
        color: #333;
    }

    .course-card p {
        margin: 0 0 10px;
        color: #666;
    }

    .progress-bar {
        background-color: #e0e0e0;
        border-radius: 5px;
        height: 8px;
        width: 100%;
        margin-bottom: 10px;
    }

    .progress {
        height: 100%;
        background-color: #2563eb;
        border-radius: 5px;
    }

    .btn {
        display: inline-block;
        padding: 8px 15px;
        background-color: #3b82f6;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        text-align: center;
        transition: background-color 0.3s ease;
        font-weight: bold;
    }

    .btn:hover {
        background-color: #2563eb;
    }

    .status-badge {
        background-color: #2563eb;
        color: #fff;
        padding: 3px 8px;
        border-radius: 5px;
        font-size: 12px;
        display: inline-block;
        margin-bottom: 10px;
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
            margin-top: 80px;
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
            margin-top: 5px;
            padding: 10px 15px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        
           
        }

        .view-coursebutton{
            display: inline-block;
            padding: 10px 15px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .view-coursebutton:hover{
            background-color: #0056b3;

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

  

    .button:hover {
        background-color: #45a049;
    }
    .logout-button {
    display: inline-block;
    padding: 10px 15px;
    background-color: #dc3545; /* Bootstrap danger color */
    color: #fff;
    text-decoration: none;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
    margin-left: 1600px; 
}

.logout-button:hover {
    background-color: #c82333; /* Darker shade for hover effect */
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
                <li><a href="#">ABOUT US</a></li>
                <li><a href="profile_students.php">Profile</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="profile-section">
            <img src="./images/profile.png" alt="Profile Image" class="profile-img">
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($_SESSION['student_username']); ?></h1>
                <p>Status: Registered Student</p>
            </div>
        </section>
    </main>    

        <section class="courses">
            <h2>My Courses</h2>
            <div class="filter">
                <select>
                    <option value="all">All</option>
                    <!-- Add more filter options here if needed -->
                </select>
            </div>

            <h2>Your Enrolled Courses</h2>
        <?php if (count($enrolled_courses) > 0): ?>
            <?php foreach ($enrolled_courses as $enrolled_course): ?>
                <div class="course">
                    <img src="<?php echo htmlspecialchars($enrolled_course['course_image']); ?>" alt="Course Image">
                    <h3><?php echo htmlspecialchars($enrolled_course['course_name']); ?></h3>
                    <form method="POST" action="" style="margin-top: 10px; margin-bottom:10px;">
                        <input type="hidden" name="course_id" value="<?php echo $enrolled_course['id']; ?>">
                        <input type="submit" name="unenroll" value="Unenroll" class="button unenroll">
                    </form>
                    <a class="view-coursebutton" href="courses.php?course_id=<?php echo $enrolled_course['id']; ?>">View Course</a>
                    
                    <!-- Progress Bar -->
                    <?php
                    $completed_modules = getCourseProgress($pdo, $student_id, $enrolled_course['id']);
                    $total_modules = $pdo->query("SELECT COUNT(*) FROM modules WHERE course_id = " . $enrolled_course['id'])->fetchColumn();
                    $progress = $total_modules > 0 ? ($completed_modules / $total_modules) * 100 : 0;
                    ?>
                    
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>You have not enrolled in any courses yet.</p>
        <?php endif; ?>
    </div>

        </section>
    </main>


    <form method="POST" action="logout.php" style="margin-top: 20px;">
    <button type="submit" class="logout-button">Logout</button>
</form>

</body>
</html>