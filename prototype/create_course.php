<?php
require_once __DIR__ . '/functions.php';
ensure_logged_in();
require_role('teacher');

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $teacher_id = $_SESSION['user_id'];

    if ($title === '') {
        $error = "Please enter a course title.";
    } else {
        $conn = get_db();
        $stmt = $conn->prepare("INSERT INTO courses (title, description, teacher_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $description, $teacher_id);
        if ($stmt->execute()) { header("Location: welcome.php"); exit; }
        else { $error = "Error: " . $conn->error; }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Course</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="wraper">
    <header>
        <img src="/prototype/images/logo.png" class="logo" style="width:200px; height:auto;">
        <nav><a href="welcome.php">Dashboard</a></nav>
        <div class="user-auth">
            <button class="login-btn-modal" onclick="window.location='welcome.php'">Cancel</button>
        </div>
    </header>

    <div class="container centered-form-container">
        <div class="card mitropolitiko-card box-narrow p-5">
            <h3 class="text-center mb-4 fw-bold text-mitropolitiko">Create New Course</h3>
            <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Course Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Web Development" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <textarea name="description" class="form-control" rows="5" placeholder="Course details..."></textarea>
                </div>
                <button class="btn w-100 btn-danger fw-bold bg-danger">Create</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>