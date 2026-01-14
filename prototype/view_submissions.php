<?php
require_once __DIR__ . '/functions.php';
ensure_logged_in();
require_role('teacher');

$assignment_id = isset($_GET['assignment_id']) ? (int)$_GET['assignment_id'] : 0;
$conn = get_db();
$stmt = $conn->prepare("SELECT a.*, c.title as course_title, c.id as course_id FROM assignments a JOIN courses c ON a.course_id = c.id WHERE a.id = ?");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assign = $stmt->get_result()->fetch_assoc();
if (!$assign) die("Assignment not found.");

$sql = "SELECT s.*, u.username, u.email FROM submissions s JOIN users u ON s.student_id = u.id WHERE s.assignment_id = ? ORDER BY s.submitted_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$submissions = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submissions</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"/>
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

    <div class="container main-content d-flex justify-content-center">
        <div class="card mitropolitiko-card box-wide p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <h3 class="text-mitropolitiko fw-bold m-0">Submissions</h3>
                <h5 class="text-muted m-0"><?php echo htmlspecialchars($assign['title']); ?></h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Date</th>
                            <th>File</th>
                            <th>Grade</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($submissions->num_rows > 0): ?>
                            <?php while($sub = $submissions->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($sub['username']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($sub['email']); ?></div>
                                    </td>
                                    <td><?php echo date("d/m/Y H:i", strtotime($sub['submitted_at'])); ?></td>
                                    <td>
                                        <a href="uploads/<?php echo htmlspecialchars($sub['file_path']); ?>" class="btn btn-sm btn-outline-primary" download>
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($sub['grade'] !== null): ?>
                                            <span class="badge bg-success fs-6"><?php echo $sub['grade']; ?>/100</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="grade_submission.php?id=<?php echo $sub['id']; ?>" class="btn btn-sm btn-danger">Grade</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No submissions yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>