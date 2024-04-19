
<?php

include_once(__DIR__ . "/../Littlesun/classes/Db.php");


	function canLogin($pUsername, $Ppassword){
		$conn = Db::getConnection();
		$pUsername = $conn->real_escape_string($pUsername);
		$sql = "SELECT password, role from account Where Username = '$pUsername'";
		$result = $conn->query($sql);
		$user = $result->fetch_assoc();
		if(password_verify($Ppassword,$user['password'])){
			return true;
		}else {
			return false;
		}

	}




	if( !empty($_POST)){
		$Username = $_POST['Username'];
		$password = $_POST['password'];
		if( canLogin($Username, $password) ){
			session_start();
			$_SESSION['loggedin'] = true;
			$_SESSION['role'] = $user['role'];
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
						Sorry, we can't log you in with that Username address and password. Can you try again?
					</p>
				</div>
				<?php  endif; ?>
				<div class="form__field">
					<label for="Username">Username</label>
					<input type="text" name="Username">
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