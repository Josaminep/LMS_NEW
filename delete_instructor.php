<?php
// Database connection
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['instructor_id'])) {
    $instructor_id = $_POST['instructor_id'];

    // Check if the instructor is assigned to any course
    $stmt = $pdo->prepare("UPDATE courses SET instructor_id = NULL WHERE instructor_id = ?");
    if ($stmt->execute([$instructor_id])) {
        // Now delete the instructor from the instructors table
        $stmt = $pdo->prepare("DELETE FROM instructors WHERE id = ?");
        if ($stmt->execute([$instructor_id])) {
            echo "<script>alert('Instructor deleted successfully!'); window.location.href = window.location.href;</script>";
        } else {
            echo "<script>alert('Error deleting instructor.'); window.location.href = window.location.href;</script>";
        }
    } else {
        echo "<script>alert('Error unassigning instructor from course.'); window.location.href = window.location.href;</script>";
    }
}
?>
