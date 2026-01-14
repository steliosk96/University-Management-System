<?php
require_once __DIR__ . '/functions.php';
ensure_logged_in();
require_role('teacher');

$submission_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$conn = get_db();
$error = "";

$sql = "SELECT s.*, u.username, a.title as assign_title, a.id as assign_id FROM submissions s JOIN users u ON s.student_id = u.id JOIN assignments a ON s.assignment_id = a.id WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $submission_id);
$stmt->execute();
$sub = $stmt->get_result()->fetch_assoc();
if (!$sub) die("Submission not found.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grade = (int)$_POST['grade'];
    $comments = trim($_POST['comments']);
    $stmt = $conn->prepare("UPDATE submissions SET grade = ?, comments = ? WHERE id = ?");
    $stmt->bind_param("isi", $grade, $comments, $submission_id);
    if ($stmt->execute()) { header("Location: view_submissions.php?assignment_id=" . $sub['assign_id']); exit; }
    else { $error = "Error: " . $conn->error; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grade Submission</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="wraper">
    <header>
        <img src="/prototype/images/logo.png" class="logo" style="width:200px; height:auto;">
        <nav><a href="view_submissions.php?assignment_id=<?php echo $sub['assign_id']; ?>">Back to List</a></nav>
        <div class="user-auth">
            <button class="login-btn-modal" onclick="window.location='welcome.php'">Dashboard</button>
        </div>
    </header>

    <div class="container centered-form-container">
        <div class="card mitropolitiko-card box-narrow p-5">
            <h3 class="text-center mb-3 fw-bold text-mitropolitiko">Grade Student</h3>
            <h5 class="text-center fw-bold mb-1"><?php echo htmlspecialchars($sub['username']); ?></h5>
            <p class="text-center text-muted mb-4">Assignment: <?php echo htmlspecialchars($sub['assign_title']); ?></p>

            <div class="text-center mb-4">
                <a href="uploads/<?php echo htmlspecialchars($sub['file_path']); ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                    Open Submitted File
                </a>
            </div>

            <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Grade (0-100)</label>
                    <input type="number" name="grade" class="form-control" min="0" max="100" required value="<?php echo $sub['grade'] ?? ''; ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Comments / Feedback</label>
                    <textarea name="comments" class="form-control" rows="5" placeholder="Write your feedback here..."><?php echo htmlspecialchars($sub['comments'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn w-100 btn-danger fw-bold bg-danger">Save Grade</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>