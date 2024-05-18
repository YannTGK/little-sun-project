<?php

include_once(__DIR__ . "/../Littlesun/classes/Db.php");

function canLogin($pUsername, $Ppassword){
    $conn = Db::getConnection();
    $pUsername = $conn->real_escape_string($pUsername);
    $sql = "SELECT password, role, username, id, hubname FROM account WHERE Username = '$pUsername'";
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
        $_SESSION['username'] = $user['username'];
        $_SESSION['id'] = $user['id'];
        $_SESSION['hubname'] = $user['hubname'];
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
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/loginscreen.css">
</head>
<body>
    <div class="welcome">
        <div class="Big">
            <h1 >welcome to Little Sun</h1>
            <p>Where African agriculture is given structure </p>
        </div>
        
        <img class="BigImg" src="../Littlesun/images/logo.png" alt="">
    </div>

    <div class="login">
        <div class="loginHolder">
            <div class="loginInfo">
                <h2>Account Login</h2>
                <p>If you are already a member you can login with your email address and password.</p>

                <!--
                <?php if(isset($error)):?>
                    <div class="form__error">
                        <p style="color: red;">
                            Sorry, we can't log you in with that Username address and password. Can you try again?
                        </p>
                    </div>
                <?php endif; ?> 
                -->
            </div>
                
            <form class="introLogin" method="post">
                <div class="form">
                    <label for="Username">Username</label>
                    <input type="text" name="Username">
                </div>
                <div class="form">
                    <label for="Password">Password</label>
                    <input type="password" name="password">
                </div>

                <div class="btn">
                    <input type="submit" value="Login" class="formButton">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
