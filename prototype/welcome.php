<?php
require_once __DIR__ . '/functions.php';
ensure_logged_in('login.php');

$user_role = $_SESSION['role'] ?? '';
$conn = get_db();
$sql = "SELECT c.*, u.username as teacher_name FROM courses c JOIN users u ON c.teacher_id = u.id ORDER BY c.created_at DESC";
$result = $conn->query($sql);

$roleClass = ($user_role === 'teacher') ? 'role-teacher' : 'role-student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"/>
</head>
<body>
<div class="wraper">
    <header>
      <img src="/prototype/images/logo.png" class="logo" style="width: 200px; height: auto" />
      <nav class="d-flex align-items-center gap-3">
        <a href="/prototype/Home.html">Home</a>
        <span class="welcome-user <?php echo $roleClass; ?>">
          Welcome <span class="welcome-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </span>
      </nav>
      <div class="user-auth">
          <button class="login-btn-modal" onclick="window.location='login.php?logout=1'">Log out</button>
      </div>
    </header>

    <div class="container main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-white text-shadow">Available Courses</h1>
            <?php if ($user_role === 'teacher'): ?>
                <a href="create_course.php" class="btn btn-danger fw-bold shadow">
                    <i class="bi bi-plus-circle"></i> Create Course
                </a>
            <?php endif; ?>
        </div>

        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while($course = $result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card mitropolitiko-card h-100">
                            <div class="card-body">
                                <h5 class="card-title fw-bold text-dark"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <h6 class="card-subtitle mb-3 text-muted">
                                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($course['teacher_name']); ?>
                                </h6>
                                <p class="card-text text-secondary">
                                    <?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?>
                                </p>
                            </div>
                            <div class="card-footer bg-white border-0 pb-3 d-flex gap-2">
                            <a href="view_course.php?id=<?php echo $course['id']; ?>" class="btn flex-grow-1 btn-outline-danger">
                                Enter Course
                            </a>

                            <?php if ($user_role === 'teacher' && $course['teacher_id'] == $_SESSION['user_id']): ?>
                                <a href="delete_course.php?id=<?php echo $course['id']; ?>" 
                                class="btn btn-danger"
                                onclick="return confirm('Ειστε σιγουρος για την διαγραφη του μαθηματος');"
                                title="Delete Course">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-light text-center shadow border-start border-danger border-5">
                        No courses available yet.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>