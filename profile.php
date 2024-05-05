<?php 
session_start();
if(!isset($_SESSION['loggedin'])){
    header("Location: login.php");
    exit; 
}

if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $isAdmin = true;
} else {
    $isAdmin = false;
}

if(isset($_SESSION['role']) && $_SESSION['role'] === 'Manager') {
    $isManager = true;
} else {
    $isManager = false;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="styles/normalize.css">
  <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/profile.css">
  <title>Littlesun</title>
</head>
<body>
    <?php include_once(__DIR__ . "/classes/nav.php"); ?>

    <div class="screen">
        <h1> 
            <?php if(isset($_SESSION['role'])){
            $role = $_SESSION['role'];
            echo $role;
            } else {
            echo "Rol niet gevonden in sessie.";
            } ?> Profile
        </h1>
        <div class="holder">
            <div class="articles">
                <div class="article">
                    <h2>Plan Your Vacation</h2>
                    <p>Planning a trip or some freetime? Everybody needs some time of. Plan it in here so your co-workers know when you are away.  Your vacation still needs to be accepted. </p>
                    <span class="editLinks">
                        <a class="YButton" href="./selfVacation.php">Edit</a>
                    </span>
                        
                </div>
            </div>
            
            <div class="homeImg">
                &nbsp;
            </div>
           
       
            
        </div>
        

    </div>

</body>
</html>
