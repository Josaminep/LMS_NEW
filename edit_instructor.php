<?php
// Database connection
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['instructor_id'])) {
    $instructor_id = $_POST['instructor_id'];
    $instructor_name = $_POST['instructor_name'];
    $instructor_email = $_POST['instructor_email'];
    $course_id = $_POST['course_id'];

    // Update instructor details
    $stmt = $pdo->prepare("UPDATE instructors SET name = ?, email = ? WHERE id = ?");
    if ($stmt->execute([$instructor_name, $instructor_email, $instructor_id])) {
        // Update the course assignment if a course is selected
        if ($course_id) {
            $stmt = $pdo->prepare("UPDATE courses SET instructor_id = ? WHERE id = ?");
            $stmt->execute([$instructor_id, $course_id]);
        }
        echo "<p>Instructor updated successfully!</p>";
    } else {
        echo "<p>Error updating instructor.</p>";
    }
}
?>
