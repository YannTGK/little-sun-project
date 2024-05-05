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

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>workershub</title>
    <link rel="stylesheet" href="styles/normalize.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/workershub.css">
</head>
<body>
    <?php include_once(__DIR__ . "/classes/nav.php"); ?>
    
    <div class="screen">
        <div class="title">
            <h1>
                <?php if(isset($_SESSION['role'])){
                $role = $_SESSION['role'];
                echo $role;
                } else {
                echo "Rol niet gevonden in sessie.";
                } ?> employee information
            </h1>
            <a class="kruis" href="./index.php"></a>

        </div>
        <input type="text" id="usernameInput" placeholder="Filter on username...">
        <input type="text" id="hubnameInput" placeholder="Filter on hub-name...">
        <input type="text" id="taskTypeInput" placeholder="Filter on task...">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="hidden">User ID</th>
                        <th>Username</th>
                        <th>E-mail</th>
                        <th>Profile picture</th>
                        <th>Role</th>
                        <th>Hubname</th>
                        <th>Tasktype</th>
                    </tr>
                </thead>
                <tbody id="workersTableBody">
                    <?php
                    include_once(__DIR__ . "/classes/allworkers.php"); 
        
                    $allWorkers = new AllWorkers(); 
                    $workersData = $allWorkers->fetchWorkers(); 
        
                    foreach ($workersData as $worker) {
                        echo "<tr>";
                        echo "<td class='hidden'>" . $worker['user_id'] . "</td>";
                        echo "<td>" . $worker['username'] . "</td>";
                        echo "<td>" . $worker['email'] . "</td>";
                        echo "<td><img class='employeeImg' src='" . $worker['profilepicture'] . "' alt='Profielfoto'></td>";
                        echo "<td>" . $worker['role'] . "</td>";
                        echo "<td>" . $worker['hubname'] . "</td>";
                        echo "<td>" . $worker['TaskType'] . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>
    <script src="js/workershub.js"></script>
</body>
</html>
