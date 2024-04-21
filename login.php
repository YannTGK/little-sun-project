<?php

include_once(__DIR__ . "/../Littlesun/classes/Db.php");

function canLogin($pUsername, $Ppassword){
    $conn = Db::getConnection();
    $pUsername = $conn->real_escape_string($pUsername);
    $sql = "SELECT password, role FROM account WHERE Username = '$pUsername'";
    $result = $conn->query($sql);
    $user = $result->fetch_assoc();
    if(password_verify($Ppassword, $user['password'])){
        return $user; 
    } else {
        return false;
    }
}

if(!empty($_POST)){
    $Username = $_POST['Username'];
    $password = $_POST['password'];
    $user = canLogin($Username, $password);
    if($user){
        session_start();
        $_SESSION['loggedin'] = true;
        $_SESSION['role'] = $user['role']; 
        header("Location:index.php");
        exit; 
    } else {
        $error = true;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LittleSun</title>
    <link rel="stylesheet" href="normalize.css">
    <link rel="stylesheet" href="styles/loginscreen.css">
</head>
<body>
    <div class="welcome">
        <h1>welcome to Little Sun</h1>
        <p>where African agriculture is given structure </p>
        <img src="../Littlesun/images/logo.png" alt="">
    </div>
<div class="login">
    <div class="form form--login">
        <form action="" method="post">
            <h2 form__title>Account Login</h2>
            <p>If you are already a member you can login with your email address and password.</p>

            <?php if(isset($error)):?>
                <div class="form__error">
                    <p style="color: red;">
                        Sorry, we can't log you in with that Username address and password. Can you try again?
                    </p>
                </div>
            <?php endif; ?>
            <div class="form__field">
                <label for="Username">Username</label>
                <input type="text" name="Username">
            </div>
            <div class="form__field">
                <label for="Password">Password</label>
                <input type="password" name="password">
            </div>

            <div class="btn">
                <input type="submit" value="Login" class="btn btn--primary">
            </div>
        </form>
    </div>
</div>
</body>
</html>
