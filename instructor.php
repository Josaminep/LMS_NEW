<?php
session_start(); // Start a session
// Include database connection file
require 'db_connection.php';

// Check if instructor is logged in
if (!isset($_SESSION['instructor_id'])) {
    header("Location: instructor_login.php"); // Redirect to login if not logged in
    exit();
}
$instructor_id = $_SESSION['instructor_id'];

// Fetch courses assigned to the instructor
$courses = $pdo->prepare("SELECT * FROM courses WHERE instructor_id = ?");
$courses->execute([$instructor_id]);
$courses = $courses->fetchAll(PDO::FETCH_ASSOC);

// Fetch students enrolled in each course
$students_by_course = [];
foreach ($courses as $course) {
    $course_id = $course['id'];
    $students = $pdo->prepare("SELECT s.id, s.name FROM students s 
                                JOIN enrollments e ON s.id = e.student_id 
                                WHERE e.course_id = ?");
    $students->execute([$course_id]);
    $students_by_course[$course_id] = $students->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch modules or content related to each course
$modules_by_course = [];
foreach ($courses as $course) {
    $course_id = $course['id'];
    $modules = $pdo->prepare("SELECT * FROM modules WHERE course_id = ?");
    $modules->execute([$course_id]);
    $modules_by_course[$course_id] = $modules->fetchAll(PDO::FETCH_ASSOC);
}

// Handle sending assessment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_assessment'])) {
    $course_id = $_POST['course_id'];
    $assessment_title = htmlspecialchars($_POST['assessment_title']);
    $assessment_description = htmlspecialchars($_POST['assessment_description']);

    // Save the assessment to the database
    $insert_assessment = $pdo->prepare("INSERT INTO assessments (course_id, instructor_id, assessment_title, assessment_description, created_at) 
                                         VALUES (?, ?, ?, ?, NOW())");
    $insert_assessment->execute([$course_id, $instructor_id, $assessment_title, $assessment_description]);

    // Set success message in session
    $_SESSION['successMessage'] = "Assessment sent successfully!";
    
    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF'] . "?course_id=" . urlencode($course_id));
    exit;
}


// Handle feedback submission for assessments
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_feedback'])) {
  $submission_id = $_POST['submission_id'];
  $feedback_text = htmlspecialchars($_POST['feedback_text']);

  // Save feedback to the database
  $insert_feedback = $pdo->prepare("INSERT INTO assessment_feedback (submission_id, user_id, user_type, comment, created_at) 
                                     VALUES (?, ?, 'instructor', ?, NOW())");
  $insert_feedback->execute([$submission_id, $instructor_id, $feedback_text]);

  // Redirect to the same page to prevent resubmission
  header("Location: " . $_SERVER['PHP_SELF']);
  exit(); // Ensure no further code is executed
}

// Fetch feedback for each assessment submission
$feedback_by_submission = [];
foreach ($courses as $course) {
  $course_id = $course['id'];
  $submissions = $pdo->prepare("SELECT asmt.id AS submission_id, asmt.student_id, asmt.submission_text, s.name AS student_name 
                                 FROM assessment_submissions asmt 
                                 JOIN students s ON asmt.student_id = s.id 
                                 WHERE asmt.assessment_id IN (SELECT id FROM assessments WHERE course_id = ?)");
  $submissions->execute([$course_id]);
  $feedback_by_submission[$course_id] = $submissions->fetchAll(PDO::FETCH_ASSOC);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor - <?php echo htmlspecialchars($_SESSION['instructor_name']); ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f8fc;
        }

/* Header container styles */
.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    background-color: #265fca; /* Customize background color */
}

/* Logo styles */
.logo img {
    height: 50px; /* Adjust logo size */
}

/* Navigation styles */
nav ul {
    list-style: none;
    display: flex;
    margin: 0;
    padding: 0;
}

nav ul li {
    margin-left: 20px; /* Space between menu items */
    display: flex;
    align-items: center; /* Aligns text and profile image */
}

nav ul li a {
    text-decoration: none;
    color: white; /* Menu text color */
    font-size: 1rem;
    padding: 5px 10px;
    transition: background-color 0.3s ease;
}

nav ul li a:hover {
    background-color: #27ae60; /* Hover effect for menu items */
    border-radius: 5px;
}

.profile {
    display: flex;
    align-items: center;
    margin-left: 20px;
}

.profile img {
    width: 30px; /* Profile image size */
    height: 30px;
    border-radius: 50%; /* Make profile picture circular */
    margin-right: 10px; /* Space between image and name */
}

.profile span {
    color: white;
    font-size: 1rem;
}

/* Optional: Adjust spacing for logout button */
nav ul li:last-child {
    margin-left: 30px; /* Extra space for the logout button */
}

nav ul li a.logout {
    color: #e74c3c; /* Logout button color */
}

nav ul li a.logout:hover {
    color: #c0392b; /* Darker red on hover for logout */
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
            margin: 5px 0;
            color: #666;
        }

        .profile-info .assigned-course {
            color: #333;
            font-weight: bold;
        }

        .tabs {
            display: flex;
            background-color: #f1f1f1;
            margin: 20px;
            border-radius: 8px;
            overflow: hidden;
        }

        .tabs button {
            flex: 1;
            padding: 10px;
            border: none;
            background-color: #e0e0e0;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        .tabs button.active {
            background-color: #2563eb;
            color: #fff;
        }

        .tab-content {
            background-color: #ffffff;
            padding: 20px;
            margin: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .tab-content.hidden {
           display: none;
        }

        .tab-content h3 {
            margin-top: 0;
            font-size: 18px;
            color: #333;
        }

        .tab-content p {
            color: #666;
            line-height: 1.6;
        }
        .profile-section {
          display: flex;
          align-items: center;
          background-color: #ffffff;
          padding: 20px;
          margin: 20px;
          border-radius: 10px;
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
          background-image: url('./images/newswriting.png'); /* Replace with your background image path */
          background-size: cover;
          background-position: center;
          color: #fff;
      }
      .assessment-form,
.submissions {
    background-color: #ffffff;
    padding: 20px;
    margin: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

h4 {
    margin-bottom: 15px;
    font-size: 20px;
    color: #2563eb;
}

label {
    display: block;
    margin: 10px 0 5px;
    font-weight: bold;
}

input[type="text"],
textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}

input[type="submit"] {
    background-color: #2563eb;
    color: #ffffff;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-size: 16px;
}

input[type="submit"]:hover {
    background-color: #1d4ed8;
}

.submission {
    background-color: #f9f9f9;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 5px;
}

.submission h5 {
    margin: 0 0 10px;
    font-size: 16px;
    color: #333;
}

.submission p {
    margin: 5px 0;
    font-size: 14px;
}

    </style>
</head>
<body>
<header>
    <div class="header-container">
        <div class="logo">
            <img src="./images/logo.png" alt="Logo">
        </div>
        <nav>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#">About Us</a></li>
                <li class="profile">
                    <img src="./images/instructor.png" alt="Profile">
                    <span><?php echo htmlspecialchars($_SESSION['instructor_name']); ?></span>
                </li>
                <li><a href="index.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

</header>


    <main>
    <section class="profile-section">
        <img src="./images/instructor.png" alt="Ittetsu Takeda" class="profile-img">
        <div class="profile-info">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['instructor_name']); ?></h2>
            <p>Instructor</p>
            <?php if (count($courses) > 0): ?>
                <p class="assigned-course">Assigned Course: <?php echo htmlspecialchars($courses[0]['course_name']); ?></p>
            <?php else: ?>
                <p class="assigned-course">Assigned Course: None</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="tabs">
        <button class="active" onclick="showTabContent('assigned-course', event)">ASSIGNED COURSE</button>
        <button onclick="showTabContent('my-learners', event)">MY LEARNERS</button>
        <button onclick="showTabContent('evaluate', event)">EVALUATE</button>
    </section>

    <div id="assigned-course" class="tab-content">
        <?php if (count($courses) > 0): ?>
            <?php foreach ($courses as $course): ?>
                <div class="course">
                    <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                    <p><?php echo htmlspecialchars($course['course_description']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No courses available.</p>
        <?php endif; ?>
    </div>


    <div id="my-learners" class="tab-content hidden">
    <h3>My Learners</h3>
    <?php if (count($courses) > 0): ?>
        <?php foreach ($courses as $course): ?>
            <div class="course">
                <h4>Course: <?php echo htmlspecialchars($course['course_name']); ?></h4>
                <div class="students">
                    <h5>Enrolled Students:</h5>
                    <?php
                    if (!empty($students_by_course[$course['id']])) {
                        foreach ($students_by_course[$course['id']] as $student): ?>
                            <div class="student"><?php echo htmlspecialchars($student['name']); ?></div>
                        <?php endforeach;
                    } else { ?>
                        <div class="student">No students enrolled in this course.</div>
                    <?php } ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div>No courses found.</div>
    <?php endif; ?>
</div>

<div id="evaluate" class="tab-content hidden">
    <h3>Evaluate</h3>

    <!-- Display success message if assessment was sent -->
    <?php if (isset($_SESSION['successMessage'])): ?>
        <div class="success-message" style="color: green;">
            <?php echo htmlspecialchars($_SESSION['successMessage']); ?>
        </div>
        <?php unset($_SESSION['successMessage']); ?>
    <?php endif; ?>

<!-- Assessment form (initially hidden) -->
<div class="assessment-form" id="assessment-form" style="display: none;">
    <h4>Send Assessment:</h4>
    <form method="POST" action="">
        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['id']); ?>">
        <label for="assessment_title">Assessment Title:</label>
        <input type="text" id="assessment_title" name="assessment_title" required>
        
        <label for="assessment_description">Assessment Description:</label>
        <textarea id="assessment_description" name="assessment_description" rows="4" required></textarea>
        
        <input type="submit" name="send_assessment" value="Send Assessment">
    </form>
</div>

<!-- Button to toggle the assessment form visibility -->
<button id="toggle-assessment-form-btn" onclick="toggleAssessmentForm()">Send Assessment</button>

<!-- Show Sent Assessments Button -->
<button id="show-assessments-btn" onclick="openModal()">Show Sent Assessments</button>

<!-- Modal for displaying sent assessments -->
<div id="sent-assessments-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h4>Sent Assessments:</h4>
        <?php
        $fetch_assessments = $pdo->prepare("SELECT assessment_title, assessment_description, created_at FROM assessments WHERE course_id = ? AND instructor_id = ?");
        $fetch_assessments->execute([$course['id'], $instructor_id]);
        $assessments = $fetch_assessments->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($assessments)): ?>
            <ul>
                <?php foreach ($assessments as $assessment): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($assessment['assessment_title']); ?></strong><br>
                        <?php echo nl2br(htmlspecialchars($assessment['assessment_description'])); ?><br>
                        <small>Sent on: <?php echo date('F d, Y', strtotime($assessment['created_at'])); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No assessments have been sent for this course yet.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal styling -->
<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 10% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 600px;
        border-radius: 8px;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
</style>

<!-- JavaScript to toggle the form visibility and modal functionality -->
<script>
    // Toggle visibility of the assessment form
    function toggleAssessmentForm() {
        const form = document.getElementById("assessment-form");
        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "block"; // Show form
        } else {
            form.style.display = "none"; // Hide form
        }
    }

    // Open modal for showing sent assessments
    function openModal() {
        document.getElementById("sent-assessments-modal").style.display = "block";
    }

    // Close modal
    function closeModal() {
        document.getElementById("sent-assessments-modal").style.display = "none";
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        const modal = document.getElementById("sent-assessments-modal");
        if (event.target === modal) {
            modal.style.display = "none";
        }
    }
</script>


    <div class="submissions">
        <h4>Assessment Submissions:</h4>
        <?php if (empty($course['id'])): ?>
            <p>No course assigned for you</p>
        <?php elseif (!empty($feedback_by_submission[$course['id']])): ?>
            <?php foreach ($feedback_by_submission[$course['id']] as $submission): ?>
                <div class="submission">
                    <h5>Submission by: <?php echo htmlspecialchars($submission['student_name']); ?></h5>
                    <h5>Submitted Assessment: <?php echo htmlspecialchars($submission['submission_text']); ?></h5>

                    <?php
                    $feedback_check = $pdo->prepare("SELECT * FROM assessment_feedback WHERE submission_id = ? AND user_id = ? AND user_type = 'instructor'");
                    $feedback_check->execute([$submission['submission_id'], $_SESSION['user_id']]);
                    $existing_feedback = $feedback_check->fetch(PDO::FETCH_ASSOC);

                    if ($existing_feedback): ?>
                        <div class="existing-feedback">
                            <strong>Your Feedback:</strong>
                            <h5><?php echo nl2br(htmlspecialchars($existing_feedback['comment'])); ?></h5>
                            <p>Submitted on: <?php echo date('F d, Y', strtotime($existing_feedback['created_at'])); ?></p>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <input type="hidden" name="submission_id" value="<?php echo htmlspecialchars($submission['submission_id']); ?>">
                            <label for="feedback_text_<?php echo $submission['submission_id']; ?>">Feedback:</label>
                            <textarea id="feedback_text_<?php echo $submission['submission_id']; ?>" name="feedback_text" rows="3" required></textarea>
                            <input type="submit" name="submit_feedback" value="Submit Feedback">
                        </form>
                    <?php endif; ?>

                    <?php
                    $comments_query = $pdo->prepare("SELECT * FROM comments WHERE post_id = ?");
                    $comments_query->execute([$submission['submission_id']]);
                    $comments = $comments_query->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <?php if (!empty($comments)): ?>
                        <div class="student-comments">
                            <p style="color: green;">Student Comments</p>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment">
                                    <p><strong>Comment:</strong></p><h5><?php echo nl2br(htmlspecialchars($comment['content'])); ?></h5>
                                    <p>Posted on: <?php echo date('F d, Y', strtotime($comment['created_at'])); ?></p>

                                    <?php
                                    $replies_query = $pdo->prepare("SELECT * FROM replies WHERE comment_id = ?");
                                    $replies_query->execute([$comment['comment_id']]);
                                    $replies = $replies_query->fetchAll(PDO::FETCH_ASSOC);
                                    ?>

                                    <?php if (!empty($replies)): ?>
                                        <div class="replies">
                                            <p style="color: blue;">Your Replies</p>
                                            <?php foreach ($replies as $reply): ?>
                                                <div class="reply">
                                                    <p><strong>Reply:</strong></p>
                                                    <h5><?php echo nl2br(htmlspecialchars($reply['reply_content'])); ?></h5>
                                                    <p>Posted on: <?php echo date('F d, Y', strtotime($reply['created_at'])); ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <form method="POST" action="reply.php">
                                        <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comment['comment_id']); ?>">
                                        <label for="reply_text_<?php echo htmlspecialchars($comment['comment_id']); ?>">Reply:</label>
                                        <textarea id="reply_text_<?php echo htmlspecialchars($comment['comment_id']); ?>" name="reply_text" rows="2" required></textarea>
                                        <input type="submit" name="submit_reply" value="Reply">
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No comments for this submission.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class='submission'>No submissions available for this assessment.</div>
        <?php endif; ?>
    </div>
</div>



<script>
    function showTabContent(tabId, event) {
        // Hide all tab content
        const contents = document.querySelectorAll('.tab-content');
        contents.forEach(content => content.classList.add('hidden'));

        // Remove active class from all buttons
        const buttons = document.querySelectorAll('.tabs button');
        buttons.forEach(button => button.classList.remove('active'));

        // Show the selected tab content and add active class to the button
        document.getElementById(tabId).classList.remove('hidden');
        event.target.classList.add('active');
    }
</script>

</body>
</html>