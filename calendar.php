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
  <link rel="stylesheet" href="styles/calendar.css">
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
            } ?> Calendar
        </h1>
        <div class="holder">
            <div class="articles">
                <div class="article">
                    
                    <h2>Organize planning</h2>
                    <p>Your central hub for managing workdays and hours. With our menu, you effortlessly submit your workdays and track your hours. Simple and efficient. </p>
                    <span class="editLinks">
                        <a class="YButton" href="visibleagenda.php">Organize</a>
                    </span>   
                </div>
             
                <?php if($isAdmin || $isManager): ?>
                    <div class="article">
                        
                        <h2>Manage Tasks</h2>
                        <p>Add and delete possible tasks for your employees. You can streemline what has to be done by everybody. Make sure no tasks are left out. After this, add the task to the specific employee</p>
                        <span class="editLinks">
                            <a class="YButton" href="./tasks.php">Manage</a>
                        </span>
                    </div>

                    <div class="article">
                        
                        <h2>Vacation Requests</h2>
                        <p>Comfirm or reject vacations. Everybody has a right to take a vacation. Here you can view, accept or deny all the vacation requests. </p>
                        <span class="editLinks">
                            <a class="YButton" href="./vacations.php">Watch requests</a>
                        </span>
                        
                            
                    </div>
                <?php endif; ?>

               

            </div>
                
            <div class="calendarImg">
                &nbsp;
            </div>
                
                
                
        </div>
            
            
    </div>
        
    
</body>
</html>
