<?php
// test_signup.php - simulate POST to register.php for debugging
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['firstname'] = 'Test';
$_POST['lastname'] = 'User';
$_POST['email'] = 'test.user+' . time() . '@example.com';
$_POST['password'] = 'Password123!';
$_POST['repassword'] = 'Password123!';

// Ensure session works
session_start();
// Include register logic
include 'register.php';

// Show session messages if any
echo "\nSESSION SUCCESS:\n";
var_export(isset($_SESSION['success']) ? $_SESSION['success'] : null);
echo "\nSESSION ERROR:\n";
var_export(isset($_SESSION['error']) ? $_SESSION['error'] : null);
echo "\n";
?>