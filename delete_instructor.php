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
            echo "<p>Instructor deleted successfully!</p>";
        } else {
            echo "<p>Error deleting instructor.</p>";
        }
    } else {
        echo "<p>Error unassigning instructor from course.</p>";
    }
}
?>
