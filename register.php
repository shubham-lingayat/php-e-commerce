<?php
// Enable verbose error reporting for debugging (temporary)
@ini_set('display_errors', 1);
@ini_set('display_startup_errors', 1);
@error_reporting(E_ALL);
@ini_set('log_errors', 1);
@ini_set('error_log', '/tmp/php_error.log');
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

	include 'includes/session.php';

	// Debug logging: record incoming request method and POST keys
	@file_put_contents('/tmp/register_debug.log', date('c') . " REQUEST_METHOD=" . ($_SERVER['REQUEST_METHOD'] ?? 'NULL') . " POST_KEYS=" . implode(',', array_keys($_POST ?? [])) . "\n", FILE_APPEND);

	if($_SERVER['REQUEST_METHOD'] === 'POST'){
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$email = $_POST['email'];
		$password = $_POST['password'];
		$repassword = $_POST['repassword'];

		$_SESSION['firstname'] = $firstname;
		$_SESSION['lastname'] = $lastname;
		$_SESSION['email'] = $email;

		// Captcha removed — registration proceeds without recaptcha.

		if($password != $repassword){
			$_SESSION['error'] = 'Passwords did not match';
			header('location: signup.php');
		}
		else{
			$conn = $pdo->open();

			$stmt = $conn->prepare("SELECT COUNT(*) AS numrows FROM users WHERE email=:email");
			$stmt->execute(['email'=>$email]);
			$row = $stmt->fetch();
			if($row['numrows'] > 0){
				$_SESSION['error'] = 'Email already taken';
				header('location: signup.php');
			}
			else{
				$now = date('Y-m-d');
				$password = password_hash($password, PASSWORD_DEFAULT);

				//generate code
				$set='123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				$code=substr(str_shuffle($set), 0, 12);

				try{
					// Provide values for NOT NULL columns (type, address, contact_info, photo, status, reset_code)
					$stmt = $conn->prepare("INSERT INTO users (email, password, firstname, lastname, type, address, contact_info, photo, status, activate_code, reset_code, created_on) VALUES (:email, :password, :firstname, :lastname, :type, :address, :contact_info, :photo, :status, :code, :reset_code, :now)");
					$stmt->execute([
						'email'=>$email,
						'password'=>$password,
						'firstname'=>$firstname,
						'lastname'=>$lastname,
						'type'=>0,
						'address'=>'',
						'contact_info'=>'',
						'photo'=>'',
						// Auto-activate new accounts (no email activation required)
						'status'=>1,
						'code'=>$code,
						'reset_code'=>'',
						'now'=>$now
					]);
					$userid = $conn->lastInsertId();

					$message = "
						<h2>Thank you for Registering.</h2>
						<p>Your Account:</p>
						<p>Email: ".$email."</p>
						<p>Password: ".$_POST['password']."</p>
						<p>Your account has been created and is active. You can now log in.</p>
					";

					// Skip sending email in this simplified setup.
					unset($_SESSION['firstname']);
					unset($_SESSION['lastname']);
					unset($_SESSION['email']);

					$_SESSION['success'] = 'Account created. You can now log in.';
					header('location: login.php');


				}
				catch(PDOException $e){
					// Log DB error for debugging and redirect back to signup
					@file_put_contents('/tmp/register_debug.log', date('c') . " DB_ERROR=" . ($e->getMessage() ?? 'unknown') . "\n", FILE_APPEND);
					$_SESSION['error'] = 'There was a problem creating your account.';
					header('location: signup.php');
				}

				$pdo->close();

			}

		}

	}
	else{
		// Non-POST access — redirect back to signup without setting an error message.
		header('location: signup.php');
		exit();
	}

?>