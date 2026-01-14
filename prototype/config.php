<?php
// xekinaei session ama den uparxei
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database settings 
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'mydb';
?>