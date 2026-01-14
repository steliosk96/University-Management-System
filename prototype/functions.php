<?php
require_once __DIR__ . '/config.php';

/**
 * Shared helpers for database access and auth flows.
 */

// xekinaei to session mono mia fora 
function start_session_once(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function get_db(): mysqli
{
    static $conn;

    if ($conn instanceof mysqli) {
        return $conn;
    }

    $db_host = $GLOBALS['db_host'] ?? 'localhost';
    $db_user = $GLOBALS['db_user'] ?? 'root';
    $db_pass = $GLOBALS['db_pass'] ?? '';
    $db_name = $GLOBALS['db_name'] ?? 'mydb'; 

    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    
    if ($conn->connect_error) {
        throw new RuntimeException('Database connection failed: ' . $conn->connect_error);
    }

    // gia ellinika
    $conn->set_charset('utf8mb4');

    return $conn;
}

function role_codes(): array
{
    return [
        'student' => 'STUD2025',
        'teacher' => 'PROF2025',
    ];
}

// Function gia to register 
function register_user(string $username, string $email, string $password, string $role, string $reg_code): array
{
    $username = trim($username);
    $email    = trim($email);
    $role     = trim($role);
    $reg_code = trim($reg_code);

    if ($username === '' || $email === '' || $password === '' || $role === '' || $reg_code === '') {
        return ['ok' => false, 'message' => 'All fields are required.'];
    }
    
    if (!preg_match('/^[A-Za-z0-9._-]{3,20}$/', $username)) {
        return ['ok' => false, 'message' => 'Username must be 3-20 characters (letters, numbers, dots, underscores or hyphens).'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Please enter a valid email address.'];
    }

    // tsekari an o rolos sou einai simvatos me to kodiko pou mas dinete ana rolo
    $codes = role_codes();
    if (!isset($codes[$role]) || $reg_code !== $codes[$role]) {
        return ['ok' => false, 'message' => 'Invalid role or registration code.'];
    }

    $conn = get_db();

    // check an uparxei idi to email
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        return ['ok' => false, 'message' => 'Email is already registered.'];
    }

    $stmt->close();

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert neo user
    $stmt = $conn->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $username, $email, $password_hash, $role);
    $ok = $stmt->execute();
    $stmt->close();

    if (!$ok) {
        return ['ok' => false, 'message' => 'Registration failed. Please try again.'];
    }

    return ['ok' => true, 'message' => 'Registration successful. You can now log in.'];
}

// Function gia to login
function login_user(string $email, string $password): array
{
    $email = trim($email);

    if ($email === '' || $password === '') {
        return ['ok' => false, 'message' => 'Email and password are required.'];
    }

    $conn = get_db();

    // kanei fetch ton user apo to email tou
    $stmt = $conn->prepare('SELECT id, username, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows !== 1) {
        $stmt->close();
        return ['ok' => false, 'message' => 'Invalid email or password.'];
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        return ['ok' => false, 'message' => 'Invalid email or password.'];
    }

    return ['ok' => true, 'message' => '', 'user' => $user];
}

// kanei store ta data tou user pou exei kanei log in
function set_user_session(array $user): void
{
    start_session_once();
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email']    = $user['email'];
    $_SESSION['role']     = $user['role'];
}

// Force login
function ensure_logged_in(string $redirect = 'login.php'): void
{
    start_session_once();
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . $redirect);
        exit;
    }
}

// Logout user kai redirect
function logout_and_redirect(string $redirect = 'login.php'): void
{
    start_session_once();
    session_unset();
    session_destroy();
    header('Location: ' . $redirect);
    exit;
}

/**checkarei an o xristis exei ton aparetito rolo an oxi ton petaei 
 */
function require_role(string $required_role): void
{
    start_session_once();

    // checkarei tous rolous
    if (empty($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
        http_response_code(403); // kodikos "Forbidden"
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Forbidden Action</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="d-flex justify-content-center align-items-center vh-100 bg-light">
            <div class="text-center">
                <h1 class="display-1 text-danger fw-bold">403</h1>
                <h2 class="text-danger">Forbidden Action</h2>
                <p class="lead">You do not have permission to access this page.</p>
                <a href="welcome.php" class="btn btn-primary mt-3">Back to Dashboard</a>
            </div>
        </body>
        </html>';
        exit; 
    }
}
?>