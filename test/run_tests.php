<?php

$GLOBALS['db_host'] = 'localhost';
$GLOBALS['db_user'] = 'root';
$GLOBALS['db_pass'] = '';
$GLOBALS['db_name'] = 'mydb_test'; 


if (file_exists(__DIR__ . '/../functions.php')) {
    require_once __DIR__ . '/../functions.php';
} else {
    die("<h3 style='color:red'>Error: Δεν βρέθηκε το functions.php! Ελέγξτε αν το αρχείο run_tests.php είναι μέσα στον φάκελο /test/</h3>");
}


function run_test($test_name, $condition) {
    $style = $condition 
        ? "background-color: #d4edda; color: #155724; border-color: #c3e6cb;" // Πράσινο
        : "background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;"; // Κόκκινο
    
    echo "<div style='margin-bottom: 10px; padding: 15px; border: 1px solid; border-radius: 5px; $style'>";
    echo "<strong>" . htmlspecialchars($test_name) . "</strong>";
    echo "<span style='float:right; font-weight:bold;'>" . ($condition ? "✅ PASS" : "❌ FAIL") . "</span>";
    echo "</div>";
}


echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>Unit Tests</title></head>";
echo "<body style='font-family: sans-serif; max-width: 800px; margin: 20px auto; background-color: #f8f9fa;'>";
echo "<div style='background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>";
echo "<h1 style='border-bottom: 2px solid #dc3545; padding-bottom: 10px; color: #333;'>Running Unit Tests</h1>";
echo "<p>Testing functions from: <code>../functions.php</code></p>";

// ---------------------------------------------------------
// TEST 1: Έλεγχος των κωδικών ρόλων 
// ---------------------------------------------------------
$codes = role_codes(); //
run_test("Function role_codes() returns an array", is_array($codes));
run_test("Check Student Code (STUD2025)", isset($codes['student']) && $codes['student'] === 'STUD2025');

// ---------------------------------------------------------
// TEST 2: Register User - Validations 
// ---------------------------------------------------------


$res = register_user('', '', '', '', ''); //
run_test("Register: Empty fields returns error", $res['ok'] === false && $res['message'] === 'All fields are required.');


$res = register_user('testuser', 'bad-email', '123456', 'student', 'STUD2025');
run_test("Register: Invalid email returns error", $res['ok'] === false);


$res = register_user('testuser', 'ok@email.com', '123456', 'student', 'WRONG_CODE');
run_test("Register: Wrong registration code returns error", $res['ok'] === false && $res['message'] === 'Invalid role or registration code.');

// ---------------------------------------------------------
// TEST 3: Register User - Success 
// ---------------------------------------------------------


$unique_email = 'test_' . rand(1000, 9999) . '_' . time() . '@example.com'; 
$unique_user  = 'user_' . rand(1000, 9999);
$password     = 'mypass123';

$res = register_user($unique_user, $unique_email, $password, 'student', 'STUD2025');
run_test("Register: Valid registration works", $res['ok'] === true);

// ---------------------------------------------------------
// TEST 4: Login User
// ---------------------------------------------------------


$login_res = login_user($unique_email, $password); //
run_test("Login: Can login with new user", $login_res['ok'] === true && $login_res['user']['email'] === $unique_email);


$login_fail = login_user($unique_email, 'wrongpass');
run_test("Login: Wrong password is rejected", $login_fail['ok'] === false);


$login_fail_email = login_user('ghost@example.com', '123');
run_test("Login: Non-existent email is rejected", $login_fail_email['ok'] === false);

// ---------------------------------------------------------
// TEST 5: Set Session
// ---------------------------------------------------------

if ($login_res['ok']) {
    set_user_session($login_res['user']); //
    run_test("Session: User ID is set in session", isset($_SESSION['user_id']) && $_SESSION['user_id'] == $login_res['user']['id']);
    run_test("Session: Username is set in session", isset($_SESSION['username']) && $_SESSION['username'] == $unique_user);
} else {
    run_test("Session: Skipped because login failed", false);
}

echo "<div style='margin-top: 20px; text-align: center; color: #666; font-size: 0.9em;'>Tests Completed at " . date("H:i:s") . "</div>";
echo "</div></body></html>";
?>