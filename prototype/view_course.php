<?php
require_once __DIR__ . '/functions.php';
ensure_logged_in();

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($course_id === 0) { header("Location: welcome.php"); exit; }

$conn = get_db();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
if (!$course) die("Course not found.");

$stmt = $conn->prepare("SELECT * FROM assignments WHERE course_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$assignments_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($course['title']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"/>
</head>
<body>
<div class="wraper">
    <header>
      <img src="/prototype/images/logo.png" class="logo" style="width: 200px; height: auto" />
      <nav><a href="welcome.php">Dashboard</a></nav>
      <div class="user-auth">
          <button class="login-btn-modal" onclick="window.location='welcome.php'">Back</button>
      </div>
    </header>

    <div class="container main-content d-flex justify-content-center">
        <div class="card mitropolitiko-card p-4 w-100">
            <div class="border-bottom pb-3 mb-4">
                <h2 class="text-mitropolitiko fw-bold mb-2"><?php echo htmlspecialchars($course['title']); ?></h2>
                <p class="text-secondary fs-5 mb-0"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold text-dark">Assignments</h4>
                <?php if ($user_role === 'teacher'): ?>
                    <a href="add_assignment.php?course_id=<?php echo $course_id; ?>" class="btn btn-outline-danger btn-sm fw-bold">
                        <i class="bi bi-plus-lg"></i> Add Assignment
                    </a>
                <?php endif; ?>
            </div>

            <div class="list-group">
                <?php if ($assignments_result->num_rows > 0): ?>
                    <?php while($assign = $assignments_result->fetch_assoc()): ?>
                        <?php 
                            $submission = null;
                            if ($user_role === 'student') {
                                $stmt_sub = $conn->prepare("SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?");
                                $stmt_sub->bind_param("ii", $assign['id'], $user_id);
                                $stmt_sub->execute();
                                $submission = $stmt_sub->get_result()->fetch_assoc();
                            }
                        ?>
                        <div class="list-group-item mb-3 border rounded shadow-sm p-3">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1 fw-bold text-dark"><?php echo htmlspecialchars($assign['title']); ?></h5>
                                <small class="text-danger fw-bold">Due: <?php echo date("d M, H:i", strtotime($assign['deadline'])); ?></small>
                            </div>
                            <p class="mb-2 text-muted"><?php echo htmlspecialchars($assign['description']); ?></p>
                            <div class="mt-2 pt-2 border-top">
                                <?php if ($user_role === 'student'): ?>
                                    <?php if ($submission): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Submitted</span>
                                        <?php if ($submission['grade'] !== null): ?>
                                            <span class="badge bg-primary ms-2">Grade: <?php echo $submission['grade']; ?>/100</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark ms-2">Pending Grade</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="submit_assignment.php?id=<?php echo $assign['id']; ?>" class="btn btn-sm btn-danger">Upload Work</a>
                                    <?php endif; ?>
                                <?php elseif ($user_role === 'teacher'): ?>
                                    <a href="view_submissions.php?assignment_id=<?php echo $assign['id']; ?>" class="btn btn-sm btn-warning text-dark fw-bold">View Submissions</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-light text-center border">No assignments yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>