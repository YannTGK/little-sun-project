
<?php

include_once(__DIR__ . "/../Littlesun/classes/Db.php");


	function canLogin($pEmail, $Ppassword){
		$conn = Db::getConnection();
		$pEmail = $conn->real_escape_string($pEmail);
		$sql = "SELECT password from account Where email = '$pEmail'";
		$result = $conn->query($sql);
		$user = $result->fetch_assoc();
		if(password_verify($Ppassword,$user['password'])){
			return true;
		}else {
			return false;
		}

	}




	if( !empty($_POST)){
		$email = $_POST['email'];
		$password = $_POST['password'];
		if( canLogin($email, $password) ){
			session_start();
			$_SESSION['loggedin'] = true;
			header("Location:index.php");
		}else {
			$error = true;
		};
	};


?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>LittleSun</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
	<div class="login">
		<div class="form form--login">
			<form action="" method="post">
				<h2 form__title>Sign In</h2>

				<?php  if(isset($error)):?>
				<div class="form__error">
					<p>
						Sorry, we can't log you in with that email address and password. Can you try again?
					</p>
				</div>
				<?php  endif; ?>
				<div class="form__field">
					<label for="Email">Email</label>
					<input type="text" name="email">
				</div>
				<div class="form__field">
					<label for="Password">Password</label>
					<input type="password" name="password">
				</div>

				<div class="form__field">
					<input type="submit" value="Sign in" class="btn btn--primary">	
				</div>
			</form>
		</div>
	</div>
</body>
</html>