<?php
require_once __DIR__ . '/functions.php';

ensure_logged_in();
require_role('teacher');

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$teacher_id = $_SESSION['user_id']; 

if ($course_id > 0) {
    $conn = get_db();

    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ? AND teacher_id = ?");
    $stmt->bind_param("ii", $course_id, $teacher_id);
    
    if ($stmt->execute()) {
        header("Location: welcome.php?msg=deleted");
    } else {
        die("Error deleting course: " . $conn->error);
    }
    $stmt->close();
} else {
    header("Location: welcome.php");
}
exit;
?>