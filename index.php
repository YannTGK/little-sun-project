<?php 

session_start();
if( !isset($_SESSION['loggedin'])){
  header("Location: login.php");
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
        <a href="index.html"><div class="headerItems headerItem1"></div></a>
        <a href="index.html"><div class="headerItems headerItem2 active"></div></a>
        <a href="profile/index.html"><div class="headerItems headerItem3"></div></a>
        <a href="calendar/index.html"><div class="headerItems headerItem4"></div></a>
        <a href="settings/index.html"><div class="headerItems headerItem5"></div></a>
        <a href="login/index.html"><div class="headerItems headerItem6">Logoutlogout</div></a>
    </div>

</body>
</html>