<?php
require_once __DIR__ . '/functions.php';

ensure_logged_in();
require_role('teacher');

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$error = "";

$conn = get_db();
$stmt = $conn->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) { die("Course not found."); }
$course = $res->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $deadline    = $_POST['deadline'] ?? '';

    if ($title === '' || $deadline === '') {
        $error = "Title and Deadline are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO assignments (course_id, title, description, deadline) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $course_id, $title, $description, $deadline);
        if ($stmt->execute()) { header("Location: view_course.php?id=" . $course_id); exit; }
        else { $error = "Error: " . $conn->error; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Assignment</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="wraper">
    <header>
        <img src="/prototype/images/logo.png" class="logo" style="width:200px; height:auto;">
        <nav><a href="view_course.php?id=<?php echo $course_id; ?>">Back to Course</a></nav>
        <div class="user-auth">
            <button class="login-btn-modal" onclick="window.location='welcome.php'">Dashboard</button>
        </div>
    </header>

    <div class="container centered-form-container">
        <div class="card mitropolitiko-card box-narrow p-5">
            <h3 class="text-center mb-3 fw-bold text-mitropolitiko">Add New Assignment</h3>
            <h5 class="text-center text-muted mb-4">Course: <?php echo htmlspecialchars($course['title']); ?></h5>

            <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Assignment Title</label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. Final Project">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Description / Instructions</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Write the instructions here"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Deadline</label>
                    <input type="datetime-local" name="deadline" class="form-control" required>
                </div>
                <button type="submit" class="btn w-100 btn-danger fw-bold bg-danger">Create Assignment</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>