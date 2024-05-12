<?php 
session_start();
if(!isset($_SESSION['loggedin'])){
    header("Location: login.php");
    exit; 
}

// Assuming you have a user ID in your session
$userID = $_SESSION['id'];

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

// Database connection
require_once(__DIR__ . "/classes/Db.php");
$db = new Db();
$conn = $db->getConnection();

// Verwerk startwerk en eindwerk acties
if(isset($_POST['action'])) {
    $action = $_POST['action'];
    $currentTime = date("Y-m-d H:i:s");

    // Voeg de start- of eindwerktijd toe aan de database
    if ($action === "start" || $action === "end") {
        $column = ($action === "start") ? "start" : "end";

        if ($action === "start") {
            // Controleer of het eindveld NULL is voordat je het startuur toevoegt
            $checkNullQuery = "SELECT COUNT(*) AS count FROM workhours WHERE user_id = $userID AND end IS NULL";
            $nullResult = $conn->query($checkNullQuery);
            $isNull = false;
            if ($nullResult && $nullResult->num_rows > 0) {
                $row = $nullResult->fetch_assoc();
                $isNull = ($row['count'] > 0);
            }
            
            if (!$isNull) {
                $sql = "INSERT INTO workhours (user_id, $column, day) VALUES ($userID, '$currentTime', CURDATE())";
            } else {
                echo "You can't start new work hour, end time is not recorded yet.";
                exit;
            }
        } else {
            $sql = "UPDATE workhours SET $column = '$currentTime' WHERE user_id = $userID AND day = CURDATE() AND end IS NULL";
        }

        if ($conn->query($sql) === TRUE) {

        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
}

$sql = "SELECT user_id, SUM(TIMESTAMPDIFF(MINUTE, start, end)) AS total_minutes
        FROM workhours
        WHERE DATE(day) = CURDATE()
        GROUP BY user_id";
$result = $conn->query($sql);
$workedTimes = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $workedTimes[$row['user_id']] = $row['total_minutes'];
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="styles/normalize.css">
  <link rel="stylesheet" href="styles/style.css">
  <link rel="stylesheet" href="styles/checkin.css">
  <style>
    
  </style>
  <title>Littlesun</title>
</head>
<body>
    <?php include_once(__DIR__ . "/classes/nav.php"); ?>

    <div class="screen">
        <div class="title">
            <h1>Check-in & out</h1>
            <a class="kruis" href="./index.php"></a>
        </div>
    
        <div class="holder">
            
            <div class="workhours">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Time Worked</th>
                            <th>Overworked</th> 
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT workhours.user_id, workhours.start, workhours.end, account.username 
                                FROM workhours 
                                JOIN account ON workhours.user_id = account.id";

                        if(!$isAdmin && !$isManager) {
                            $userID = $_SESSION['id'];
                            $sql .= " WHERE workhours.user_id = $userID";
                        }

                        $result = $conn->query($sql);

                        if (!$result) {
                            echo "Error: " . $conn->error;
                        } else {
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                        echo "<td>" . $row["username"]. "</td>";
                                        echo "<td>" . $row["start"]. "</td>";
                                        echo "<td>" . $row["end"]. "</td>";
                                        $start = new DateTime($row["start"]);
                                        $end = new DateTime($row["end"]);
                                        $diff = $start->diff($end);
                                        echo "<td>" . $diff->format('%h hours %i minutes') . "</td>";

                                        if ($diff->h > 7) {
                                            echo "<td class='overworked'>" . ($diff->h - 7) . " hours " . $diff->i . " minutes</td>";
                                        } else {
                                            echo "<td>0 hours 0 minutes</td>";
                                        }
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>no results</td></tr>";
                            }
                        }

                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
           
            <?php if(!$isAdmin && !$isManager): ?>
                <form class="checkHolder"  method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                    
                    <span class="editLink">
                        <button class="formButton" type="submit" name="action" value="start">Start your shift here</button>
                    </span>

                    <span class="editLink">
                        <button class="formButton2" type="submit" name="action" value="end">Stop your shift here</button>
                    </span>
                    
                </form>
            <?php endif; ?>
          
        </div>
    </div>
</body>
</html>
