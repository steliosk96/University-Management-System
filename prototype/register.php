<?php
require_once __DIR__ . '/functions.php';


$register_error   = "";
$register_success = "";


$form_username = "";
$form_email    = "";
$form_role     = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // checkaroume ta pedia 
    $form_username = trim($_POST['username'] ?? "");
    $form_email    = trim($_POST['email'] ?? "");
    $password      = $_POST['password'] ?? "";
    $form_role     = $_POST['role'] ?? "";
    $reg_code      = trim($_POST['reg_code'] ?? "");

    try {
        // Validate kai register meso helper
        $result = register_user($form_username, $form_email, $password, $form_role, $reg_code);
        if ($result['ok']) {
            $register_success = $result['message'];
            $form_username = "";
            $form_email    = "";
            $form_role     = "";
        } else {
            $register_error = $result['message'];
        }
    } catch (Throwable $e) {
       
        $register_error = "Unexpected error. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <link rel="stylesheet" href="style.css">
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>
<body>

<div class="wraper">

    <header>
        <img src="/prototype/images/logo.png" class="logo" style="width:200px;">
        <nav><a href="/prototype/Home.html">Home</a></nav>
        <div class="user-auth"><button class="login-btn-modal">Login</button></div>
    </header>

    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="card p-4 shadow" style="width: 100%; max-width: 400px; border-top:4px solid #dc3545;">
            <h3 class="text-center mb-3" style="color:#dc3545;">Register</h3>

            <?php if (!empty($register_error)): ?>
                <div class="alert alert-danger mt-2" role="alert">
                    <?php echo htmlspecialchars($register_error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($register_success)): ?>
                <div class="alert alert-success mt-2" role="alert">
                    <?php echo htmlspecialchars($register_success); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="mb-3">
                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        placeholder="Username"
                        pattern="^[A-Za-z0-9._-]{3,20}$"
                        title="3-20 characters: letters, numbers, dots, underscores or hyphens"
                        required
                        value="<?php echo htmlspecialchars($form_username); ?>">
                </div>

                <div class="mb-3">
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Email"
                        required
                        value="<?php echo htmlspecialchars($form_email); ?>">
                </div>

                <div class="mb-3">
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Password"
                        required>
                </div>

                <div class="mb-3">
                    <select
                        name="role"
                        class="form-select"
                        style="border-radius:40px;">
                        <option value="">Select Role</option>
                        <option value="student" <?php if ($form_role === 'student') echo 'selected'; ?>>Student</option>
                        <option value="teacher" <?php if ($form_role === 'teacher') echo 'selected'; ?>>Teacher</option>
                    </select>
                </div>

                <div class="mb-3">
                    <input
                        type="text"
                        name="reg_code"
                        class="form-control"
                        placeholder="Registration Code"
                        required>
                </div>

                <button class="btn w-100" style="background:#dc3545; color:white;">Register</button>

                <p class="mt-3 text-center" style="font-size: 16px;">
                    Already have an account?
                    <a href="/prototype/login.php" style="color:#dc3545;">Login</a>
                </p>
            </form>
        </div>
    </div>

</div>

<script src="script.js"></script>
</body>
</html>
