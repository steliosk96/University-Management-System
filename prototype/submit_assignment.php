<?php
require_once __DIR__ . '/functions.php';
ensure_logged_in();
require_role('student');

$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$conn = get_db();
$stmt = $conn->prepare("SELECT a.*, c.title as course_title, c.id as course_id FROM assignments a JOIN courses c ON a.course_id = c.id WHERE a.id = ?");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assign = $stmt->get_result()->fetch_assoc();
if (!$assign) die("Assignment not found.");

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['work_file'])) {
    $file = $_FILES['work_file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "File upload error.";
    } else {
        if (!is_dir(__DIR__ . '/uploads')) { mkdir(__DIR__ . '/uploads', 0777, true); }
        $filename = time() . '_' . basename($file['name']);
        $target_file = __DIR__ . '/uploads/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $student_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("INSERT INTO submissions (assignment_id, student_id, file_path) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $assignment_id, $student_id, $filename);
            if ($stmt->execute()) { $success = "Assignment submitted successfully!"; }
            else { $error = "Database error: " . $conn->error; }
        } else { $error = "Failed to save file."; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Assignment</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="wraper">
    <header>
        <img src="/prototype/images/logo.png" class="logo" style="width:200px; height:auto;">
        <nav><a href="view_course.php?id=<?php echo $assign['course_id']; ?>">Back to Course</a></nav>
        <div class="user-auth">
            <button class="login-btn-modal" onclick="window.location='welcome.php'">Dashboard</button>
        </div>
    </header>

    <div class="container centered-form-container">
        <div class="card mitropolitiko-card box-narrow p-5">
            <h3 class="text-center mb-3 fw-bold text-mitropolitiko">Submit Assignment</h3>
            <h5 class="text-center text-muted mb-4"><?php echo htmlspecialchars($assign['title']); ?></h5>

            <div class="alert alert-light border mb-4">
                <strong>Instructions:</strong><br> 
                <?php echo nl2br(htmlspecialchars($assign['description'])); ?>
            </div>

            <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success text-center">
                    <?php echo $success; ?> <br>
                    <a href="view_course.php?id=<?php echo $assign['course_id']; ?>" class="btn btn-sm btn-outline-success mt-2">Return to Course</a>
                </div>
            <?php else: ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Upload File</label>
                        <input type="file" name="work_file" class="form-control" required>
                    </div>
                    <button type="submit" class="btn w-100 btn-danger fw-bold bg-danger">Submit Work</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>