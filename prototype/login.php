<?php
require_once __DIR__ . '/functions.php';

start_session_once();


$login_error = "";

// Handle logout
if (isset($_GET['logout'])) {
    logout_and_redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // checkari ta stixia 
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        // Validate kai login meso helper
        $result = login_user($email, $password);
        if ($result['ok']) {
            set_user_session($result['user']);
            header("Location: welcome.php");
            exit;
        } else {
            $login_error = $result['message'];
        }
    } catch (Throwable $e) {
        $login_error = "Unexpected error. Please try again later.";
    }
}

if (isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link rel="stylesheet" href="style.css">
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>
<body>

<div class="wraper">

    <header>
        <img src="/prototype/images/logo.png" class="logo" style="width:200px;">
        <nav><a href="Home.html">Home</a></nav>
        <div class="user-auth"><button class="login-btn-modal">Login</button></div>
    </header>

    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="card p-4 shadow" style="width: 100%; max-width: 400px; border-top:4px solid #dc3545;">
            <h3 class="text-center mb-3" style="color:#dc3545;">Login</h3>

            <?php if (!empty($login_error)): ?>
                <div class="alert alert-danger mt-2" role="alert">
                    <?php echo htmlspecialchars($login_error); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter email"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter password"
                        required>
                </div>

                <button class="btn w-100 mt-2" type="submit" style="background:#dc3545; color:white;">
                    Login
                </button>

                <p  class="mt-3 text-center" style="font-size: 16px;">
                    Don't have an account?
                    <a href="register.php" style="color:#dc3545;">Register</a>
                </p>
            </form>
        </div>
    </div>

</div>

<script src="script.js"></script>
</body>
</html>
