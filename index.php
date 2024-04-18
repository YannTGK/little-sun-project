<?php 


session_start();
if( !isset($_SESSION['loggedin'])){
  header("Location: login.php");
}else {
  $role = $_SESSION['role'];
  print $role;
};




?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Littlesun</title>
  <link rel="stylesheet" href="style/style.css">
</head>
<body>

<div class="header">
<?php include_once("../Littlesun/classes/nav.php"); ?>
    </div>
</body>
</html>