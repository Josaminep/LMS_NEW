<?php
session_start(); // Start the session

// Database connection
$host = 'localhost';
$db_name = 'lms';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$course_id = $_GET['course_id'];
$student_id = $_SESSION['student_id']; // Replace with your session variable name

// Fetch course details
$course = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$course->execute([$course_id]);
$course = $course->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    die("Course not found.");
}

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_content'])) {
    $content = $_POST['post_content'];
    $image = ''; // Handle image upload if needed

    // Check if an image was uploaded
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
        // Define upload directory and filename
        $upload_dir = 'uploads/';
        $image = $upload_dir . basename($_FILES['post_image']['name']);

        // Move uploaded file to the desired directory
        if (!move_uploaded_file($_FILES['post_image']['tmp_name'], $image)) {
            echo "Error uploading image.";
        }
    }

    // Insert post into the database
    $stmt = $pdo->prepare("INSERT INTO posts (course_id, student_id, content, image) VALUES (?, ?, ?, ?)");
    $stmt->execute([$course_id, $student_id, $content, $image]);
    header("Location: courses.php?course_id=$course_id"); // Redirect to avoid resubmission
    exit;
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'])) {
    $comment_content = $_POST['comment_content'];
    $post_id = $_POST['post_id'];

    // Insert comment into the database
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, student_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$post_id, $student_id, $comment_content]);
    header("Location: courses.php?course_id=$course_id"); // Redirect to avoid resubmission
    exit;
}

// Fetch posts for the course with the specific student
$posts = $pdo->prepare("SELECT p.*, s.name as student_name FROM posts p JOIN students s ON p.student_id = s.id WHERE p.course_id = ? AND p.student_id = ?");
$posts->execute([$course_id, $student_id]);
$posts = $posts->fetchAll(PDO::FETCH_ASSOC);

// Fetch comments for each post
$comments = [];
foreach ($posts as $post) {
    $post_id = $post['id'];
    $comment_stmt = $pdo->prepare("SELECT c.*, s.name as student_name FROM comments c JOIN students s ON c.student_id = s.id WHERE c.post_id = ?");
    $comment_stmt->execute([$post_id]);
    $comments[$post_id] = $comment_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch modules for the course
$modules = $pdo->prepare("SELECT * FROM modules WHERE course_id = ?");
$modules->execute([$course_id]);
$modules = $modules->fetchAll(PDO::FETCH_ASSOC);

// Fetch completed modules for the student
$completed_modules = $pdo->prepare("SELECT module_id FROM completed_modules WHERE student_id = ?");
$completed_modules->execute([$student_id]);
$completed_modules = $completed_modules->fetchAll(PDO::FETCH_COLUMN);

// Handle module completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['module_id'])) {
    $module_id = $_POST['module_id'];
    
    // Check if the module is already marked as completed
    if (!in_array($module_id, $completed_modules)) {
        $insert = $pdo->prepare("INSERT INTO completed_modules (student_id, module_id) VALUES (?, ?)");
        $insert->execute([$student_id, $module_id]);
    }

    // Refresh the page to update progress bar and module completion status
    header("Location: course_details.php?course_id=$course_id");
    exit;
}

// Calculate progress
$total_modules = count($modules);
$completed_count = count($completed_modules);
$progress = $total_modules > 0 ? ($completed_count / $total_modules) * 100 : 0;

// Fetch course details with instructor's name
$course = $pdo->prepare("
    SELECT c.*, i.name AS instructor_name 
    FROM courses c 
    JOIN instructors i ON c.instructor_id = i.id 
    WHERE c.id = ?
");
$course->execute([$course_id]);
$course = $course->fetch(PDO::FETCH_ASSOC);

$replies = $pdo->prepare("SELECT r.user_id, r.reply_content, r.created_at, i.name AS instructor_name
                          FROM replies r
                          LEFT JOIN instructors i ON r.user_id = i.id 
                          WHERE r.comment_id = ?");
$replies = $replies->fetchAll(PDO::FETCH_ASSOC);


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title><?php echo htmlspecialchars($course['course_name']); ?></title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #AFEEEE;
            margin: 0;
            padding: 20px;
            height: 120vh;

        }

        .header {
            background-color: #343a40; /* Dark background */
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start; /* Align items to the left */
        }

        .back-button {
            background-color: #007BFF; /* Blue button color */
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .back-button:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        h2 {
            margin: 0; /* Remove default margin */
            font-weight: 900;
        }

        .course-image {
            width: 500px; /* Full width image */
            height: auto; /* Maintain aspect ratio */
            margin-top: 1px;
        }

        .tabs {
            display: flex;
            cursor: pointer;
            margin: 20px 0;
            background-color: #333;
            padding: 10px;
        }

        .tab {
            flex: 1;
            text-align: center;
            color: white;
            padding: 10px;
            transition: background-color 0.3s;
        }

        .tab:hover {
            background-color: #444;
        }

        .tab-content {
            display: none;
            margin: 20px 0;
            margin-top: 100px;
        }

        .active {
            display: block;
        }

        /* Additional styles for course content */
        .module {
            margin-bottom: 10px;
        }

        .modal {
            display: none; /* Hidden by default */
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            background-color: #fff;
            width: 100%; /* Full width */
            max-width: 1000px; /* Limit width */
            margin: 20px auto; /* Center the modal */
            overflow: auto; /* Allow scrolling if content is too big */
            margin-right: 200px;
            padding: 20px;
            
            
        }

        .modal-header {
            padding: 10px;
            background-color: #4caf50;
            color: white;
            text-align: center;
        }

        .modal-content {
            padding: 10px;
        }

        .close {
            cursor: pointer;
            color: red;
            float: right;
            margin-top: -10px;
        }



       /* General styles for the post button */
.post-button {
    background-color: #007BFF; /* Facebook blue */
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

.post-button:hover {
    background-color: #0056b3; /* Darker blue on hover */
}

/* Post form styles */
.post-form {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 15px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    
}

/* Textarea styling */
.post-form textarea {
    width: 100%;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 10px;
    resize: none; /* Prevent resizing */
    margin-bottom: 10px;
}

/* File input styling */
.post-form input[type="file"] {
    margin-bottom: 10px;
}

/* Form actions styling */
.form-actions {
    display: flex;
    justify-content: flex-end;
}

.form-actions button {
    margin-left: 10px; /* Space between buttons */
}

/* Cancel button styling */
.cancel-button {
    background-color: #ccc; /* Light gray */
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
}

.cancel-button:hover {
    background-color: #bbb; /* Darker gray on hover */
}

/* Forum post styles */
.forum-post {
    background: #fff;
    border: 1px solid #ddd;
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
    background-color: #FFC0CB;

}

.forum-post img.post-image {
    max-width: 100%;
    height: auto;
    margin: 10px 0;
}

/* Comment styles */
.comment {
    margin-left: 20px;
    background: #f9f9f9;
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ddd;
}
/* Assessment Tab Styles */
#assessmentTab {
    padding: 20px;
    background-color: #f9f9f9; /* Light background for better readability */
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.assessment {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #ddd; /* Light border for separation */
    border-radius: 5px;
    background-color: #fff; /* White background for assessments */
}

.assessment p {
    margin: 0 0 10px; /* Spacing for paragraphs */
}

.feedback {
    margin-top: 15px;
    padding: 10px;
    border-top: 1px solid #ddd; /* Separator line for feedback section */
}

.feedback h4 {
    margin: 0 0 10px; /* Margin for feedback title */
    font-size: 1.2em; /* Slightly larger font for the title */
}

.feedback-item {
    padding: 10px;
    border: 1px solid #e1e1e1; /* Border for feedback items */
    border-radius: 4px;
    margin-bottom: 10px;
    background-color: #f1f1f1; /* Light background for feedback items */
}

.feedback-item strong {
    color: #333; /* Darker color for the user type */
}

.feedback-item em {
    color: #777; /* Grey color for submission date */
}

/* Submit Button Styles */
button {
    background-color: #007bff; /* Primary button color */
    color: white;
    border: none;
    border-radius: 4px;
    padding: 10px 15px;
    cursor: pointer;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #0056b3; /* Darker shade on hover */
}

/* Textarea Styles */
textarea {
    width: 100%;
    border: 1px solid #ccc; /* Light border */
    border-radius: 4px;
    padding: 8px;
    resize: vertical; /* Allow vertical resizing only */
}
.course-image {
        width: 300px;  /* Adjust this value as needed */
        height: auto;  /* Maintains aspect ratio */
    }
    .comment-box {
    margin-bottom: 15px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.comment-content {
    font-size: 1em;
    margin-top: 5px;
    line-height: 1.5;
}

.comment-time {
    display: block;
    text-align: right;
    color: #888;
    margin-top: 5px;
}
.reply-item {
    margin-top: 15px;
    padding: 10px;
    border-left: 3px solid #4CAF50; /* Green color for instructor reply */
    background-color: #e9f7e1; /* Light green background */
    border-radius: 5px;
}

.reply-content {
    font-size: 1em;
    margin-top: 5px;
    line-height: 1.5;
    color: #333;
}

.reply-time {
    display: block;
    text-align: right;
    font-size: 0.85em;
    color: #777;
    margin-top: 5px;
}
.student-comment {
    background-color: #f7f7f7;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    margin-top: 30px;
}

.student-comment h4 {
    font-size: 1.4em;
    color: #333;
    margin-bottom: 15px;
}

.student-comment form {
    display: flex;
    flex-direction: column;
}

.student-comment textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 5px;
    border: 1px solid #ddd;
    font-size: 1em;
    resize: vertical;
}

.student-comment .submit-btn {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    font-size: 1em;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.student-comment .submit-btn:hover {
    background-color: #45a049;
}

    </style>
    
    <script>
        function showContent(title, type, file) {
            const modal = document.getElementById('modal');
            const modalContent = document.getElementById('modal-content');
            const modalTitle = document.getElementById('modal-title');

            modalTitle.textContent = title;

            if (type === 'pdf') {
                modalContent.innerHTML = `<iframe src="${file}" style="width: 100%; height: 900px; " frameborder="0"></iframe>`;
            } else if (type === 'video') {
                modalContent.innerHTML = `<video controls style="width: 100%; height: 800px;"><source src="${file}" type="video/mp4">Your browser does not support the video tag.</video>`;
            }

            modal.style.display = 'block'; // Show the modal
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
            document.getElementById('modal-content').innerHTML = ''; // Clear content
        }

        function openTab(event, tabName) {
            const tabContent = document.querySelectorAll('.tab-content');
            const tabs = document.querySelectorAll('.tab');

            tabContent.forEach(tab => {
                tab.style.display = 'none';
            });
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });

            document.getElementById(tabName).style.display = 'block';
            event.currentTarget.classList.add('active');

            // Close the modal if it's open
            closeModal();
        }

        function togglePostForm() {
    const postForm = document.getElementById('postForm');
    if (postForm.style.display === 'none') {
        postForm.style.display = 'block'; // Show the form
    } else {
        postForm.style.display = 'none'; // Hide the form
    }
}
function showContent(title, type, file) {
            const modal = document.getElementById('modal');
            const modalContent = document.getElementById('modal-content');
            const modalTitle = document.getElementById('modal-title');
            modalTitle.textContent = title;
            if (type === 'pdf') {
                modalContent.innerHTML = `<iframe src="${file}" style="width: 100%; height: 900px;" frameborder="0"></iframe>`;
            } else if (type === 'video') {
                modalContent.innerHTML = `<video controls style="width: 100%; height: 800px;"><source src="${file}" type="video/mp4">Your browser does not support the video tag.</video>`;
            }
            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
            document.getElementById('modal-content').innerHTML = '';
        }

        function openTab(event, tabName) {
            const tabContent = document.querySelectorAll('.tab-content');
            const tabs = document.querySelectorAll('.tab');
            tabContent.forEach(tab => tab.style.display = 'none');
            tabs.forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabName).style.display = 'block';
            event.currentTarget.classList.add('active');
            closeModal();
        }
    </script>
</head>
<body>
    <header class="header">
    <button class="back-button" onclick="window.location.href='profile_students.php'">←</button>
        <div class="course-details">
            <h2><?php echo htmlspecialchars($course['course_name']); ?></h2>
            <?php if ($course['course_image']): ?>
                <img src="<?php echo htmlspecialchars($course['course_image']); ?>" alt="Course Image" class="course-image" style="width: 300px; height: auto;">
                <?php endif; ?>
        </div>
    </header>

    <div class="tabs">
        <div class="tab active" onclick="openTab(event, 'overviewTab')">Overview</div>
        <div class="tab" onclick="openTab(event, 'contentTab')">Content</div>
        <div class="tab" onclick="openTab(event, 'modulesTab')">Modules</div>
        <div class="tab" onclick="openTab(event, 'forumTab')">Forum</div>
        <div class="tab" onclick="openTab(event, 'assessmentTab')">Assessment</div>
    </div>

    <!-- Overview Tab -->
<div id="overviewTab" class="tab-content active">
    <h3>Overview</h3>
    <p><strong>Description:</strong> <?php echo htmlspecialchars($course['course_description']); ?></p>
    <p><strong>Instructor:</strong> <?php echo htmlspecialchars($course['instructor_name'] ?? 'Not available'); ?></p>
</div>


<!-- Content Tab -->
<div id="contentTab" class="tab-content">
    <h3>Uploaded Videos</h3>
    <div class="uploaded-modules" style="list-style-type: none; padding: 0; margin-top: 20px;">
        <?php if (empty($modules)): ?>
            <p>No videos available for this course yet.</p>
        <?php else: ?>
            <?php foreach ($modules as $module): ?>
                <?php if ($module['video_file']): ?>
                    <div class="module" style="display: flex; align-items: center; justify-content: space-between; padding: 10px; border-bottom: 1px solid #ddd;">
                        <div class="module-title" style="font-size: 16px; font-weight: bold; color: #333;"><?php echo htmlspecialchars($module['title']); ?></div>
                        <button onclick="showContent('<?php echo htmlspecialchars($module['title']); ?>', '<?php echo htmlspecialchars($module['video_file']); ?>')" style="padding: 5px 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">View Video</button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Video Modal -->
<div id="videoModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.7); align-items: center; justify-content: center;">
    <div style="background: white; width: 80%; max-width: 700px; max-height: 80vh; overflow-y: auto; padding: 20px; position: relative;">
        <span onclick="closeModal()" style="position: absolute; top: 10px; right: 15px; font-size: 24px; cursor: pointer;">&times;</span>
        <h2 id="videoTitle"></h2>
        <video id="videoPlayer" controls style="width: 100%; margin-top: 15px;">
            <source src="" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
</div>

<!-- JavaScript for Modal -->
<script>
function showContent(title, videoFile) {
    // Set video title and source
    document.getElementById('videoTitle').textContent = title;
    document.getElementById('videoPlayer').src = videoFile;
    
    // Display the modal
    document.getElementById('videoModal').style.display = 'flex';
}

function closeModal() {
    // Hide the modal
    document.getElementById('videoModal').style.display = 'none';
    
    // Stop the video playback
    document.getElementById('videoPlayer').pause();
    document.getElementById('videoPlayer').currentTime = 0;
}
</script>

<!-- Modules Tab -->
<div id="modulesTab" class="tab-content">
    <h3>Uploaded Modules</h3>
    <div class="uploaded-modules">
        <?php if (empty($modules)): ?>
            <p>No PDF files available for this course yet.</p>
        <?php else: ?>
            <?php foreach ($modules as $module): ?>
                <?php if ($module['module_file']): ?>
                    <div class="module">
                        <div class="module-title"><?php echo htmlspecialchars($module['title']); ?></div>
                        <button onclick="viewPDF('<?php echo htmlspecialchars($module['module_file']); ?>')">View PDF</button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function viewPDF(filePath) {
    // Open the PDF in a new tab or window
    window.open(filePath, '_blank');
}
</script>


    <!-- Forum Tab -->
<!-- Forum Tab -->
<div id="forumTab" class="tab-content">
    <h3>Forum</h3>
    
    <!-- Button to trigger the post form -->
    <button id="postButton" class="post-button" onclick="togglePostForm()">Post a new message</button>

    <!-- Post Form (hidden by default) -->
    <div id="postForm" class="post-form" style="display: none; margin-top: 20px;">
        <form method="POST" enctype="multipart/form-data"> <!-- Added form tag and enctype -->
            <textarea name="post_content" rows="4" placeholder="What's on your mind?" required></textarea>
            <input type="file" name="post_image" accept="image/*">
            <div class="form-actions">
                <button type="submit">Post</button> <!-- Submit button for the form -->
                <button type="button" class="cancel-button" onclick="togglePostForm()">Cancel</button>
            </div>
        </form>
    </div>

    <h4>Posts</h4>
    <?php if (empty($posts)): ?>
        <p>No posts yet.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div class="forum-post">
                <p><strong><?php echo htmlspecialchars($post['student_name']); ?></strong> <span style="color: #888;">(<?php echo htmlspecialchars($post['created_at']); ?>)</span></p>
                <p><?php echo htmlspecialchars($post['content']); ?></p>
                <?php if ($post['image']): ?>
                    <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" class="post-image">
                <?php endif; ?>
                
                <div class="comments">
                    <h5>Comments:</h5>
                    <?php if (isset($comments[$post['id']]) && !empty($comments[$post['id']])): ?>
                        <?php foreach ($comments[$post['id']] as $comment): ?>
                            <div class="comment">
                                <p><strong><?php echo htmlspecialchars($comment['student_name']); ?></strong> <span style="color: #888;">(<?php echo htmlspecialchars($comment['created_at']); ?>)</span></p>
                                <p><?php echo htmlspecialchars($comment['content']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No comments yet.</p>
                    <?php endif; ?>
                </div>

                <!-- Comment Form -->
                <form method="POST" class="comment-form">
                    <textarea name="comment_content" rows="2" placeholder="Add a comment..." required></textarea>
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <button type="submit">Comment</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div id="assessmentTab" class="tab-content">
    <h3>Assessments</h3>

    <?php
    // Fetch assessments for the course
    $assessments = $pdo->prepare("
        SELECT a.*, i.name AS instructor_name 
        FROM assessments a
        JOIN instructors i ON a.instructor_id = i.id
        WHERE a.course_id = ?
    ");
    $assessments->execute([$course_id]);
    $assessments = $assessments->fetchAll(PDO::FETCH_ASSOC);

    // Check if assessments are available
    if (!empty($assessments)): 
        foreach ($assessments as $assessment):
            // Fetch submission for the specific student for this assessment
            $submission = $pdo->prepare("SELECT * FROM assessment_submissions WHERE assessment_id = ? AND student_id = ?");
            $submission->execute([$assessment['id'], $student_id]);
            $submission = $submission->fetch(PDO::FETCH_ASSOC);
    ?>
            <div class="assessment">
                <p><strong>Assessment Title:</strong> <?php echo htmlspecialchars($assessment['assessment_title']); ?></p>
                <p><strong>Instructor:</strong> <?php echo htmlspecialchars($assessment['instructor_name']); ?></p>
                <p><strong>Assessment Description:</strong> <?php echo nl2br(htmlspecialchars($assessment['assessment_description'])); ?></p>
                <p><em>Posted on: <?php echo date('F d, Y', strtotime($assessment['created_at'])); ?></em></p>
                
                <?php if ($submission): ?>
                    <div class="feedback">
                        <h4>Your Submission</h4>
                        <p><?php echo nl2br(htmlspecialchars($submission['submission_text'])); ?></p>
                        <p><em>Submitted on: <?php echo date('F d, Y', strtotime($submission['created_at'])); ?></em></p>
                    </div>
                <?php else: ?>
                    <form method="POST" action="submit_submissionas.php">
                        <input type="hidden" name="assessment_id" value="<?php echo htmlspecialchars($assessment['id']); ?>">
                        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>">
                        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id); ?>">
                        <textarea name="submission_text" required placeholder="Write your submission here..."></textarea>
                        <button type="submit">Submit</button>
                    </form>
                <?php endif; ?>

                <!-- Feedback Section -->
                <div class="feedback">
                    <h4>Feedback</h4>
                    <?php
                    if ($submission) { // Check if submission exists before querying feedback
                        $feedbacks = $pdo->prepare("SELECT f.*, 
                            CASE WHEN f.user_type = 'instructor' THEN i.name 
                                 WHEN f.user_type = 'student' THEN s.name 
                            END AS user_name
                            FROM assessment_feedback f
                            LEFT JOIN instructors i ON f.user_id = i.id AND f.user_type = 'instructor'
                            LEFT JOIN students s ON f.user_id = s.id AND f.user_type = 'student'
                            WHERE f.submission_id = ?");
                        $feedbacks->execute([$submission['id']]);
                        $feedbacks = $feedbacks->fetchAll(PDO::FETCH_ASSOC);

                        if (!empty($feedbacks)): 
                            foreach ($feedbacks as $feedback): ?>
                                <div class="feedback-item">
                                    <strong><?php echo htmlspecialchars($feedback['user_name']); ?> (<?php echo htmlspecialchars($feedback['user_type']); ?>):</strong>
                                    <p><?php echo nl2br(htmlspecialchars($feedback['comment'])); ?></p>
                                    <em><?php echo date('F d, Y', strtotime($feedback['created_at'])); ?></em>
                                </div>
                            <?php endforeach; 
                        else: ?>
                            <p>No feedback yet.</p>
                        <?php endif; 
                    }
                    ?>
                </div>

                <!-- Comment Section for Students -->
                <div class="student-comment">
                    <h4>Add Your Comment:</h4>
                    <form method="POST" action="save_comment.php">
                        <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($submission['id']); ?>"> <!-- 'id' refers to 'assessment_submission.id' -->
                        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($submission['student_id']); ?>">
                        
                        <textarea name="content" placeholder="Enter your comment" required rows="4"></textarea>
                        <button type="submit" class="submit-btn">Post Comment</button>
                    </form>
                </div>


                <!-- Displaying Comments -->
                <?php
                if ($submission) {
                    // Fetch comments for the current submission (using the submission's 'id' as the 'post_id')
                    $comments = $pdo->prepare("SELECT * FROM comments WHERE post_id = ?");
                    $comments->execute([$submission['id']]);  // Use the submission 'id' as the 'post_id'
                    $comments = $comments->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($comments)):
                        foreach ($comments as $comment): ?>
                            <div class="comment-item">
                                <br>
                                <div class="comment-box">
                                <strong>You:</strong>
                                <p class="comment-content"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                <em class="comment-time" style="font-size: 0.85em; color: #888;"><?php echo date('F d, Y', strtotime($comment['created_at'])); ?></em>
                            </div>
                                <?php echo date('F d, Y', strtotime($comment['created_at'])); ?></em>

                                <!-- Fetching and Displaying Instructor's Reply to the Comment -->
                                <?php
                                $replies = $pdo->prepare("SELECT * FROM replies WHERE comment_id = ?");
                                $replies->execute([$comment['comment_id']]);  // Use the correct comment_id for replies
                                $replies = $replies->fetchAll(PDO::FETCH_ASSOC);

                                if (!empty($replies)):
                                    foreach ($replies as $reply): ?>
                                        <div class="reply-item">
                                            <strong>Instructor (Reply):</strong>
                                            <p class="reply-content"><?php echo nl2br(htmlspecialchars($reply['reply_content'])); ?></p>
                                            <em class="reply-time"><?php echo date('F d, Y', strtotime($reply['created_at'])); ?></em>
                                        </div>
                                    <?php endforeach;
                                else: ?>
                                    <p>No replies yet.</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach;
                    else: ?>
                        <p>No comments yet.</p>
                    <?php endif;
                }
                ?>
            </div>
        <?php endforeach; 
    else: ?>
        <p>No assessments available for this course.</p>
    <?php endif; ?>
</div>




<div id="modal" class="modal">
    <div class="modal-header">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modal-title"></h2>
    </div>
    <div class="modal-content" id="modal-content"></div>
</div>

</body>
</html>