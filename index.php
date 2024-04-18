<?php 

session_start();
if( !isset($_SESSION['loggedin'])){
  header("Location: login.php");
}


?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="style/style.css">
  <title>Littlesun</title>
  <a style= "color:red" href="./addlocation.php">add location of hub</a>
  <a style= "color:red" href="./addmanager.php">add a manager</a>
</head>
<body>
<div class="header">
<?php include_once("../Littlesun/classes/nav.php"); ?>
    </div>
</body>
</html>