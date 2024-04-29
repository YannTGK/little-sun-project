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


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="styles/normalize.css">
  <link rel="stylesheet" href="styles/style.css">
  <link rel="stylesheet" href="styles/home.css">
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
            } ?> pannel
        </h1>
        
        <div class="holder">
            <div class="articles">
                <?php if($isAdmin): ?>
                <div class="article">
                    <h2>Add Hub Location</h2>
                    <p>Expand your agricultural empire with ease! With this menu, you can effortlessly add and remove locations, allowing farmers to work anywhere the fertile soil calls them. </p>
                    <span class="editLinks">
                        <a class="YButton" href="./addlocation.php">Edit</a>
                    </span>
                    
                </div>
                <?php endif; ?>
                <div class="article">
                    <h2>Add Pesonel</h2>
                    <p>Empower your workforce management effortlessly! With our menu, you can seamlessly add and remove personnel, ensuring your team is optimized wherever the job takes them.</p>
                    <span class="editLinks">
                        <a class="YButton" href="./addpersonal.php">Edit</a>
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
