<?php
// Include database connection
require_once 'db_connection.php';

// Set header to JSON for an AJAX response
header('Content-Type: application/json');

// Retrieve form data
$student_id = $_POST['student_id'];
$course_id = $_POST['course_id'];
$submission_text = $_POST['submission_text'];

// Fetch all assessment IDs for the given course
$assessmentQuery = $pdo->prepare("
    SELECT id FROM assessments 
    WHERE course_id = ?
");
$assessmentQuery->execute([$course_id]);
$assessments = $assessmentQuery->fetchAll(PDO::FETCH_ASSOC);

if ($assessments) {
    foreach ($assessments as $assessment) {
        $assessment_id = $assessment['id'];

        // Check if the submission already exists for each assessment
        $checkStmt = $pdo->prepare("
            SELECT * FROM assessment_submissions 
            WHERE assessment_id = ? AND student_id = ?
        ");
        $checkStmt->execute([$assessment_id, $student_id]);
        $existingSubmission = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingSubmission) {
            // If a submission exists, skip this assessment
            continue;
        } else {
            // Insert a new submission for this assessment
            $stmt = $pdo->prepare("
                INSERT INTO assessment_submissions (assessment_id, student_id, course_id, submission_text, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$assessment_id, $student_id, $course_id, $submission_text]);
        }
    }

    // Return a success response
    echo json_encode(['status' => 'success', 'message' => 'Submission successful!']);
} else {
    // Return an error response if no assessments are found
    echo json_encode(['status' => 'error', 'message' => 'Assessment not found.']);
}
?>
