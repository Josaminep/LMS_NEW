<?php
// Database connection
include 'db_connection.php';

// Handle course update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_course'])) {
    $course_id = $_POST['course_id'];
    $course_name = $_POST['course_name'];
    $course_description = $_POST['course_description'];
    $instructor_id = $_POST['instructor_id'];

    // Handle file upload for course image
    $course_image = null;
    if (isset($_FILES['course_image']) && $_FILES['course_image']['error'] == UPLOAD_ERR_OK) {
        $uploads_dir = 'uploads/';
        $tmp_name = $_FILES['course_image']['tmp_name'];
        $name = basename($_FILES['course_image']['name']);
        $course_image = $uploads_dir . $name;

        move_uploaded_file($tmp_name, $course_image);
    }

    // Update course details along with the assigned instructor
    $stmt = $pdo->prepare("UPDATE courses SET course_name = ?, course_description = ?, course_image = ?, instructor_id = ? WHERE id = ?");
    if ($stmt->execute([$course_name, $course_description, $course_image, $instructor_id, $course_id])) {
        echo "<p>Course updated successfully!</p>";
    } else {
        echo "<p>Error updating course.</p>";
    }
}

// Handle course creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_course'])) {
    $course_name = $_POST['new_course_name'];
    $course_description = $_POST['new_course_description'];
    $instructor_id = $_POST['instructor_id'];

    // Handle file upload for course image
    $course_image = null;
    if (isset($_FILES['new_course_image']) && $_FILES['new_course_image']['error'] == UPLOAD_ERR_OK) {
        $uploads_dir = 'uploads/';
        $tmp_name = $_FILES['new_course_image']['tmp_name'];
        $name = basename($_FILES['new_course_image']['name']);
        $course_image = $uploads_dir . $name;

        move_uploaded_file($tmp_name, $course_image);
    }

    // Insert course with assigned instructor
    $stmt = $pdo->prepare("INSERT INTO courses (course_name, course_description, course_image, instructor_id) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$course_name, $course_description, $course_image, $instructor_id])) {
        echo "<p>Course created successfully!</p>";
    } else {
        echo "<p>Error creating course.</p>";
    }
}

// Handle course deletion
if (isset($_GET['delete_course_id'])) {
    $course_id = $_GET['delete_course_id'];

    // Check for existing enrollments
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $enrollment_count = $stmt->fetchColumn();

    if ($enrollment_count > 0) {
        echo "<p>Cannot delete this course because there are existing enrollments.</p>";
    } else {
        // Now, delete the course
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        if ($stmt->execute([$course_id])) {
            echo "<p>Course deleted successfully!</p>";
        } else {
            echo "<p>Error deleting course.</p>";
        }
    }
}

// Fetch all courses and student count for each course
$courses = $pdo->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS student_count
    FROM courses c
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all instructors
$instructors = $pdo->query("SELECT * FROM instructors")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all students
$students = $pdo->query("SELECT * FROM students")->fetchAll(PDO::FETCH_ASSOC);

// Count totals
$total_courses = count($courses);
$total_instructors = count($instructors);
$total_students = count($students);
// Initialize variables for filtered results
$filtered_instructors = $instructors; // Default to all instructors
$filtered_students = $students; // Default to all students

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_instructor'])) {
    $instructor_name = $_POST['instructor_name'];
    $instructor_email = $_POST['instructor_email'];
    $instructor_password = $_POST['instructor_password'];
    $instructor_gender = $_POST['instructor_gender']; // Get the gender value from the form

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM instructors WHERE email = ?");
    $stmt->execute([$instructor_email]);
    $email_exists = $stmt->fetchColumn();

    if ($email_exists) {
        // Email already exists, show alert
        echo "<script type='text/javascript'>
                alert('The email is already registered. Please use a different email.');
                window.location.href = 'admin.php';  // Redirect to the registration page to correct the email
              </script>";
    } else {
        // Hash the password before storing it
        $hashed_password = password_hash($instructor_password, PASSWORD_DEFAULT);

        // Insert new instructor into the database (including gender)
        $stmt = $pdo->prepare("INSERT INTO instructors (name, email, password, gender) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$instructor_name, $instructor_email, $hashed_password, $instructor_gender])) {
            // Alert message on successful registration
            echo "<script type='text/javascript'>
                    alert('Instructor registered successfully!');
                    window.location.href = 'admin.php';  // Optional: Redirect after success
                  </script>";
        } else {
            echo "<p>Error registering instructor.</p>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        /* Basic reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            padding: 20px;
            background-color: #f5f5f5;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        header img {
            height: 50px; /* Adjust height as needed */
            margin-right: 20px;
        }

        header div {
            text-align: right;
        }

        header span {
            display: block;
            color: #555;
            font-weight: bold;
        }

        #dashboard-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .stat-box {
            background-color: white;
            padding: 20px;
            text-align: center;
            width: 30%;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .stat-box img {
            width: 50px;
            height: 50px;
            margin-bottom: 10px;
        }

        .stat-box div:first-child {
            font-size: 16px;
            color: #333;
        }

        .stat-box div:last-child {
            font-size: 30px;
            color: #222;
            font-weight: bold;
        }

        nav {
            margin-bottom: 20px;
        }

        nav ul {
            list-style: none;
            display: flex;
            justify-content: start;
        }

        nav ul li {
            margin-right: 10px;
        }

        nav ul li a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #0056d9;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            cursor: pointer;
        }

        nav ul li a:hover {
            background-color: #003f99;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 22px;
            color: #333;
        }

        select {
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        button {
            padding: 10px 20px;
            background-color: #ccc;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #999;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f1f1f1;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h2 {
            color: #333;
        }

        .course {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .course img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }

        .course-info {
            flex: 1;
        }

        .course-info h3 {
            margin: 0 0 5px;
            font-size: 16px;
        }

        .course-info p {
            margin: 0;
            font-size: 14px;
        }

        .instructor-list, .student-list {
            margin-top: 20px;
        }

        .form-container {
            margin-top: 20px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-container input {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .logout-btn {
    font-size: 1rem;
    color: #e74c3c; /* Red color */
    text-decoration: none;
    margin-left: 20px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s ease;
}

.logout-btn:hover {
    color: #c0392b; /* Darker red on hover */
}

    </style>
</head>
<body>
<header>
    <img src="./images/logo.png" alt="Logo" />
    <div>
        <span>Welcome, Admin <img src="./images/admin_logo.jpg" alt="Profile Logo" class="profile-logo" /></span>
        <span>Last Login: <?php echo date('Y-m-d H:i:s'); ?></span>
        <a href="index.php" class="logout-btn">Logout</a> <!-- Logout button -->
    </div>
</header>
<div class="container">
    <div id="dashboard-stats">
        <div class="stat-box">
            <img src="./images/admin_user.png" alt="Courses Icon" /> <!-- Replace with your icon path -->
            <div>Total Courses</div>
            <div><?php echo $total_courses; ?></div>
        </div>
        <div class="stat-box">
            <img src="./images/admin_courses.png" alt="Instructors Icon" /> <!-- Replace with your icon path -->
            <div>Total Instructors</div>
            <div><?php echo $total_instructors; ?></div>
        </div>
        <div class="stat-box">
            <img src="./images/admin_instrutors.png" alt="Students Icon" /> <!-- Replace with your icon path -->
            <div>Total Students</div>
            <div><?php echo $total_students; ?></div>
        </div>
    </div>
</div>


        <nav>
            <ul>
                <li><a href="#" class="tab-link" data-tab="manage-courses">Manage Courses</a></li>
                <li><a href="#" class="tab-link" data-tab="create-course">Create Course</a></li>
                <li><a href="#" class="tab-link" data-tab="instructors">Instructors</a></li>
                <li><a href="#" class="tab-link" data-tab="students">Students</a></li>
                <li><a href="#" class="tab-link" data-tab="register-instructor">Register New Instructor</a></li> <!-- New tab for registering instructors -->
            </ul>
        </nav>

        <section id="manage-courses" class="tab-content active">
            <h2>Manage Courses</h2>
            <div class="course-list">
                <?php foreach ($courses as $course): ?>
                    <div class="course">
                        <img src="<?php echo $course['course_image']; ?>" alt="<?php echo $course['course_name']; ?>" />
                        <div class="course-info">
                            <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                            <p><?php echo htmlspecialchars($course['course_description']); ?></p>
                            <p>Enrolled Students: <?php echo $course['student_count']; ?></p>
                        </div>
                        <a class="edit-btn" href="edit_course.php?course_id=<?php echo $course['id']; ?>">Edit</a>
                        <a class="delete-btn" href="?delete_course_id=<?php echo $course['id']; ?>" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="create-course" class="tab-content">
            <h2>Create Course</h2>
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <input type="text" name="new_course_name" placeholder="Course Name" required>
                    <input type="text" name="new_course_description" placeholder="Course Description" required>
                    <input type="file" name="new_course_image" accept="image/*">
                    <select name="instructor_id" required>
                        <option value="">Select Instructor</option>
                        <?php foreach ($instructors as $instructor): ?>
                            <option value="<?php echo $instructor['id']; ?>"><?php echo htmlspecialchars($instructor['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="create_course">Create Course</button>
                </form>
            </div>
        </section>

        <section id="instructors" class="tab-content">
    <div class="header">
        <h2>Instructors</h2>
    </div>

                                    <!-- Refresh Button with Class for Smaller Size -->
        <button onclick="window.location.reload();" class="btn-refresh">🔄 Refresh</button>

    <div class="instructor-list">
        <table>
            <thead>
                <tr>
                    <th>Instructor Name</th>
                    <th>Gender</th>
                    <th>Email</th>
                    <th>Assigned Course</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($instructors as $instructor): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($instructor['name']); ?></td>
                        <td><?php echo htmlspecialchars($instructor['gender']); ?></td>
                        <td><?php echo htmlspecialchars($instructor['email']); ?></td>
                        <td>
                            <?php
                                $stmt = $pdo->prepare("SELECT course_name FROM courses WHERE instructor_id = ?");
                                $stmt->execute([$instructor['id']]);
                                $course = $stmt->fetch(PDO::FETCH_ASSOC);
                                echo $course ? htmlspecialchars($course['course_name']) : 'Not Assigned';
                            ?>
                        </td>
                        <td>
                            <!-- Edit Instructor Button to open modal -->
                            <button onclick="openEditModal('<?php echo $instructor['id']; ?>', '<?php echo htmlspecialchars($instructor['name']); ?>', '<?php echo htmlspecialchars($instructor['email']); ?>', '<?php echo htmlspecialchars($instructor['gender']); ?>')" class="btn-edit">Edit</button>
                            
                            <!-- Delete Instructor Button -->
                            <form method="POST" action="delete_instructor.php" style="display:inline;">
                                <input type="hidden" name="instructor_id" value="<?php echo $instructor['id']; ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this instructor?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<style>
    /* Styling for the header containing the title and refresh button */
    /* Styling for the refresh button */
    .btn-refresh {
        font-size: 14px;
        padding: 6px 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #002193;
        cursor: pointer;
        transition: background-color 0.3s ease;
        width: 10%;
        margin-right: 10px;
    }

    .btn-refresh:hover {
        background-color: #e0e0e0;
    }
</style>


<!-- Modal for editing an instructor -->
<div id="editInstructorModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Edit Instructor</h2>
        
        <!-- Form content -->
        <form method="POST" action="edit_instructor.php" enctype="multipart/form-data">
            <input type="hidden" name="instructor_id" id="edit_instructor_id">
            
            <label for="edit_instructor_name">Instructor Name</label>
            <input type="text" id="edit_instructor_name" name="instructor_name" required>
            
            <label for="edit_instructor_email">Email</label>
            <input type="email" id="edit_instructor_email" name="instructor_email" required>
            
            <label for="edit_instructor_gender">Gender</label>
            <select id="edit_instructor_gender" name="instructor_gender" required>
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
            
            <label for="course_assignment">Assign Course</label>
            <select id="course_assignment" name="course_id">
                <option value="">Select a course</option>
                <?php
                    // Assuming you have a $courses array containing courses
                    foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                <?php endforeach; ?>
            </select>
            
            <label for="instructor_profile_picture">Profile Picture</label>
            <input type="file" id="instructor_profile_picture" name="instructor_profile_picture">
            
            <button type="submit">Update Instructor</button>
        </form>
    </div>
</div>



<!-- JavaScript for Modal -->
<script>
    // Open the modal and populate it with the instructor's data
    function openEditModal(id, name, email, gender) {
        document.getElementById('edit_instructor_id').value = id;
        document.getElementById('edit_instructor_name').value = name;
        document.getElementById('edit_instructor_email').value = email;
        document.getElementById('edit_instructor_gender').value = gender;
        document.getElementById('editInstructorModal').style.display = 'block';
    }

    // Close the modal
    function closeModal() {
        document.getElementById('editInstructorModal').style.display = 'none';
    }

    // Close the modal if user clicks outside of it
    window.onclick = function(event) {
        if (event.target === document.getElementById('editInstructorModal')) {
            closeModal();
        }
    };
    // Get the modal and header for dragging
var modal = document.getElementById("editInstructorModal");
var header = document.querySelector(".modal-header");

// Variables to store the position of the modal
var offsetX, offsetY, isDragging = false;

// When the user presses down on the modal header, start dragging
header.onmousedown = function(e) {
    isDragging = true;
    offsetX = e.clientX - modal.offsetLeft;
    offsetY = e.clientY - modal.offsetTop;
    
    // Prevent selection while dragging
    document.onselectstart = function() { return false; };
}

// When the user moves the mouse, move the modal if dragging
document.onmousemove = function(e) {
    if (isDragging) {
        modal.style.left = e.clientX - offsetX + "px";
        modal.style.top = e.clientY - offsetY + "px";
    }
}

// When the user releases the mouse, stop dragging
document.onmouseup = function() {
    isDragging = false;
    document.onselectstart = null;
}

// Close Modal Function
function closeModal() {
    modal.style.display = "none";
}

</script>

<!-- CSS for styling the modal -->
<style>
/* Modal container */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 10%;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
}

/* Modal content box */
.modal-content {
    background-color: #fefefe;
    margin: 0 auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    border-radius: 8px;
    position: relative; /* Needed for absolute positioning of draggable area */
}

/* Draggable title bar */
.modal-header {
    cursor: move; /* Show a move cursor to indicate it's draggable */
    background-color: #f1f1f1;
    padding: 10px;
    border-radius: 8px 8px 0 0;
    text-align: center;
    font-size: 20px;
}

/* Close button style */
.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
}

/* Heading inside the modal */
h2 {
    text-align: center;
    margin-bottom: 20px;
}

/* Label style */
label {
    font-weight: bold;
    margin-top: 10px;
    display: block;
}

/* Input, select, and button style */
input, select, button {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
}

/* Button style */
button {
    background-color: #4CAF50;
    color: white;
    font-size: 16px;
    cursor: pointer;
    width: 40%;
    margin: 10px auto;
}

/* Button hover effect */
button:hover {
    background-color: #45a049;
}
</style>



<?php
// admin.php

// Check if there is a success or error message
if (isset($_GET['status'])) {
    $status = $_GET['status'];
    $message = '';

    // Set the message based on the status
    if ($status == 'success') {
        $action = $_GET['action'];
        $message = $action == 'approve' ? 'Student has been approved successfully!' : 'Student has been denied successfully!';
    } elseif ($status == 'error') {
        $message = 'Failed to update the approval status. Please try again.';
    }
}
?>

<script type="text/javascript">
    <?php if (!empty($message)): ?>
        alert("<?php echo $message; ?>");
    <?php endif; ?>
</script>

<section id="students" class="tab-content">
    <h2>Students</h2>
    <div class="student-list">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Access Code</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo $student['id']; ?></td>
                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo htmlspecialchars($student['code']); ?></td>
                        <td>
                            <?php echo ($student['approved'] == 1 ? 'Approved' : 'Pending'); ?>
                        </td>
                        <td>
                            <?php if ($student['approved'] == 0): ?>
                            <!-- Approve button link -->
                            <a href="approve.php?id=<?php echo $student['id']; ?>&action=approve" class="approve-btn" style="display: inline-block; padding: 10px 20px; margin: 5px; border-radius: 5px; background-color: #4CAF50; color: white; border: 1px solid #4CAF50; font-weight: bold; text-decoration: none; transition: background-color 0.3s ease, transform 0.3s ease;" onmouseover="this.style.backgroundColor='#45a049'; this.style.transform='scale(1.05)';" onmouseout="this.style.backgroundColor='#4CAF50'; this.style.transform='scale(1)';">Approve</a>
                            <?php else: ?>
                                <!-- Deny button link -->
                                <a href="approve.php?id=<?php echo $student['id']; ?>&action=deny" class="deny-btn" style="display: inline-block; padding: 10px 20px; margin: 5px; border-radius: 5px; background-color: #f44336; color: white; border: 1px solid #f44336; font-weight: bold; text-decoration: none; transition: background-color 0.3s ease, transform 0.3s ease;" onmouseover="this.style.backgroundColor='#e53935'; this.style.transform='scale(1.05)';" onmouseout="this.style.backgroundColor='#f44336'; this.style.transform='scale(1)';">Deny</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>


<section id="register-instructor" class="tab-content">
    <h2>Register New Instructor</h2>
    <div class="form-container">
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="instructor_name" placeholder="Instructor Name" required>
            <input type="email" name="instructor_email" placeholder="Instructor Email" required>
            <input type="password" name="instructor_password" placeholder="Instructor Password" required>
            
            <!-- Gender dropdown -->
            <select name="instructor_gender" required>
                <option value="" disabled selected>Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <!-- Add other options if your enum allows more values -->
            </select>

            <button type="submit" name="register_instructor">Register Instructor</button>
        </form>
    </div>
</section>


    <script>
        // Tab switching logic
        const tabs = document.querySelectorAll('.tab-link');
        const tabContents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();

                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.remove('active');
                });

                // Remove active class from all tabs
                tabs.forEach(tab => {
                    tab.classList.remove('active');
                });

                // Show the clicked tab content
                const tabId = e.target.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');

                // Add active class to clicked tab
                e.target.classList.add('active');
            });
        });
    </script>
</body>
</html>
