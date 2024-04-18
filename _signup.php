<?php
include_once(__DIR__ . "/../Littlesun/little-sun-project/classes/Db.php");
	if (!empty($_POST)){
		$conn = Db::getConnection();
		//get the data from post
		$Username = $_POST['Username'];
		$password = $_POST['password'];
		
		//hash password with bcrypt
		$options = [
			'cost' => 12
		];

		$password = password_hash($password, PASSWORD_DEFAULT, $options);

		//send the data to the users table
		$query = "INSERT into account (Username, password) VALUES('$Username','$password')";
		$result = $conn->query($query);
		//echo $result;
		session_start(); //hier krijg je een cookie
		$_SESSION['loggedin'] = true;
		header("Location: index.php");
	};


?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>IMDFlix</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
	<div class="netflixLogin">
		<div class="form form--login">
			<form action="" method="post">
				<h2 form__title>Sign Up</h2>

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
					<input type="submit" value="Sign Up" class="btn btn--primary">	
				</div>
			</form>
		</div>
	</div>
</body>
</html>