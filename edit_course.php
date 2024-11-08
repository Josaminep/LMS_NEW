<?php
// Database connection setup
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

// Check if course_id is provided
if (!isset($_GET['course_id'])) {
    die("Course ID is required.");
}

$course_id = $_GET['course_id'];

// Fetch course data
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    die("Course not found.");
}

// Fetch enrolled students
$students_stmt = $pdo->prepare("SELECT s.id AS student_id, s.name AS student_name FROM enrollments e
                                JOIN students s ON e.student_id = s.id
                                WHERE e.course_id = ?");
$students_stmt->execute([$course_id]);
$enrolled_students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);

// Unenroll student if unenroll_student_id is set
if (isset($_GET['unenroll_student_id'])) {
    $unenroll_student_id = $_GET['unenroll_student_id'];
    $unenroll_stmt = $pdo->prepare("DELETE FROM enrollments WHERE student_id = ? AND course_id = ?");
    
    if ($unenroll_stmt->execute([$unenroll_student_id, $course_id])) {
        echo "<script>alert('Student unenrolled successfully!');</script>";
        header("Location: edit_course.php?course_id=$course_id");
        exit();
    } else {
        echo "<script>alert('Error unenrolling student.');</script>";
    }
}

// Update course details and handle file uploads
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_course'])) {
    $course_name = $_POST['course_name'];
    $course_description = $_POST['course_description'];

    // Update course details
    $update_stmt = $pdo->prepare("UPDATE courses SET course_name = ?, course_description = ? WHERE id = ?");
    $update_stmt->execute([$course_name, $course_description, $course_id]);
    echo "<script>alert('Course updated successfully!');</script>";

    // Handle module and video file uploads
    $target_dir = "uploads/";

    // Process each module file
    foreach ($_FILES['module_file']['name'] as $key => $filename) {
        if ($_FILES['module_file']['error'][$key] == 0) {
            $target_file = $target_dir . basename($filename);
            move_uploaded_file($_FILES['module_file']['tmp_name'][$key], $target_file);
            
            $module_title = $_POST['module_title'][$key];
            $insert_stmt = $pdo->prepare("INSERT INTO modules (course_id, module_file, title) VALUES (?, ?, ?)");
            $insert_stmt->execute([$course_id, $target_file, $module_title]);
        }
    }

    // Process each video file
    foreach ($_FILES['video_file']['name'] as $key => $filename) {
        if ($_FILES['video_file']['error'][$key] == 0) {
            $target_file = $target_dir . basename($filename);
            move_uploaded_file($_FILES['video_file']['tmp_name'][$key], $target_file);
            
            $video_title = $_POST['video_title'][$key];
            $insert_stmt = $pdo->prepare("INSERT INTO modules (course_id, video_file, title) VALUES (?, ?, ?)");
            $insert_stmt->execute([$course_id, $target_file, $video_title]);
        }
    }
}

// Update module titles and files
if (isset($_POST['update_title'])) {
    foreach ($_POST['module_id'] as $index => $module_id) {
        $new_title = $_POST['module_title'][$index];
        $update_title_stmt = $pdo->prepare("UPDATE modules SET title = ? WHERE id = ?");
        $update_title_stmt->execute([$new_title, $module_id]);

        // Check and handle updated file
        if (!empty($_FILES['module_file_update']['name'][$index])) {
            $new_file = $_FILES['module_file_update']['name'][$index];
            $target_file = $target_dir . basename($new_file);
            move_uploaded_file($_FILES['module_file_update']['tmp_name'][$index], $target_file);

            $update_file_stmt = $pdo->prepare("UPDATE modules SET module_file = ? WHERE id = ?");
            $update_file_stmt->execute([$target_file, $module_id]);
        }
    }
}

// Delete a module if delete_module_id is set
if (isset($_GET['delete_module_id'])) {
    $module_id = $_GET['delete_module_id'];

    // Fetch module files before deletion
    $delete_stmt = $pdo->prepare("SELECT module_file, video_file FROM modules WHERE id = ?");
    $delete_stmt->execute([$module_id]);
    $module = $delete_stmt->fetch(PDO::FETCH_ASSOC);

    // Delete files from the server
    if ($module) {
        if ($module['module_file'] && file_exists($module['module_file'])) {
            unlink($module['module_file']);
        }
        if ($module['video_file'] && file_exists($module['video_file'])) {
            unlink($module['video_file']);
        }
    }

    // Delete the module from the database
    $delete_stmt = $pdo->prepare("DELETE FROM modules WHERE id = ?");
    if ($delete_stmt->execute([$module_id])) {
        echo "<script>alert('Module deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting module.');</script>";
    }
    
    // Redirect to avoid form resubmission prompt
    header("Location: edit_course.php?course_id=$course_id");
    exit();
}


// Fetch modules associated with the course
$modules = $pdo->prepare("SELECT * FROM modules WHERE course_id = ?");
$modules->execute([$course_id]);
$uploaded_modules = $modules->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); }
        h2 { color: #333; }
        label { font-weight: bold; }
        input[type="text"], textarea, input[type="file"] { width: 100%; padding: 10px; margin: 5px 0 20px; border: 1px solid #ddd; border-radius: 5px; }
        button { padding: 10px 15px; background-color: #007bff; color: #ffffff; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #0056b3; }
        .close-btn { background-color: #dc3545; }
        .close-btn:hover { background-color: #c82333; }
        .file-upload { margin-top: 20px; }
        .uploaded-files, .enrolled-students { margin-top: 30px; }
        ul { list-style-type: none; padding: 0; }
        li { padding: 10px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; }
        li:last-child { border-bottom: none; }
    </style>
</head>
<body>
<div class="container">
        <button class="close-btn" onclick="window.history.back();">×</button> <!-- Close button -->
        <h2>Edit Course</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
            <label for="course_name">Course Name:</label>
            <input type="text" name="course_name" id="course_name" value="<?php echo htmlspecialchars($course['course_name']); ?>" required>
            <label for="course_description">Course Description:</label>
            <textarea name="course_description" id="course_description" rows="4" required><?php echo htmlspecialchars($course['course_description']); ?></textarea>


            <h3>Upload Modules</h3>
            <div class="file-upload">
                <label>Module Files (PDF only):</label>
                <input type="file" name="module_file[]" accept=".pdf" multiple>
                <label>Module Titles:</label>
                <input type="text" name="module_title[]" placeholder="Enter module title" >
            </div>
            <div class="file-upload">
                <label>Video Files:</label>
                <input type="file" name="video_file[]" accept=".mp4,.avi,.mov" multiple>
                <label>Video Titles:</label>
                <input type="text" name="video_title[]" placeholder="Enter video title" >
            </div>
            <button type="submit" name="update_course">Update Course</button>
        </form>

        <div class="uploaded-files">
    <h3>Uploaded Modules</h3>
    <ul>
        <?php foreach ($uploaded_modules as $module): ?>
            <?php if (!empty($module['module_file'])): ?>
                <li>
                    <a href="<?php echo htmlspecialchars($module['module_file']); ?>" target="_blank">
                        <?php echo htmlspecialchars($module['title']); ?> (PDF)
                    </a>
                    <!-- Include course_id in the delete form action URL -->
                    <form action="edit_course.php?course_id=<?php echo $course_id; ?>&delete_module_id=<?php echo $module['id']; ?>" method="post" style="display:inline;">
                        <button class="delete-btn" type="submit">Delete</button>
                    </form>
                </li>
            <?php endif; ?>
            <?php if (!empty($module['video_file'])): ?>
                <li>
                    <a href="<?php echo htmlspecialchars($module['video_file']); ?>" target="_blank">
                        <?php echo htmlspecialchars($module['title']); ?> (Video)
                    </a>
                    <!-- Include course_id in the delete form action URL -->
                    <form action="edit_course.php?course_id=<?php echo $course_id; ?>&delete_module_id=<?php echo $module['id']; ?>" method="post" style="display:inline;">
                        <button class="delete-btn" type="submit">Delete</button>
                    </form>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</div>


        <div class="enrolled-students">
            <h3>Enrolled Students</h3>
            <ul>
                <?php foreach ($enrolled_students as $student): ?>
                    <li>
                        <?php echo htmlspecialchars($student['student_name']); ?>
                        <a href="edit_course.php?course_id=<?php echo $course_id; ?>&unenroll_student_id=<?php echo $student['student_id']; ?>" class="delete-btn">Unenroll</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</body>
</html>

