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
            $sql = "INSERT INTO workhours (user_id, $column, day) VALUES ($userID, '$currentTime', CURDATE())";
        } else {
            $sql = "UPDATE workhours SET $column = '$currentTime' WHERE user_id = $userID AND day = CURDATE()";
        }

        // Execute the SQL query
        if ($conn->query($sql) === TRUE) {
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
}

// Bereken de totale werktijd voor elke gebruiker
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
  <link rel="stylesheet" href="styles/home.css">
  <style>
    .overworked {
        color: red;
    }
  </style>
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
            } ?> panel
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

                <?php if($isAdmin || $isManager): ?>
                    <div class="article">
                    
                        <h2>Add Personnel</h2>
                        <p>Empower your workforce management effortlessly! With our menu, you can seamlessly add and remove personnel, ensuring your team is optimized wherever the job takes them.</p>
                        <span class="editLinks">
                            <a class="YButton" href="./addpersonal.php">Edit</a>
                        </span>
                    </div>

                    <div class="article">
                    
                        <h2>All Personnel Information</h2>
                        <p>See what all personal are up to. Make sure everybody is working on the tasks they are needed on.  Find a specific user to see his current job.</p>
                        <span class="editLinks">
                            <a class="YButton" href="./workershub.php">Workershub</a>
                        </span>
                    
                        <!-- this has to be add to the calendar page 
                            <a href="vacations.php">Check worker vacations</a>
                        -->
                    </div>
                <?php endif; ?>
                
            </div>

            <div class="workhours">
                <h2>Work Hours</h2>
                <table>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Time Worked</th> <!-- Nieuwe kolom -->
                        <th>Overworked</th> <!-- Nieuwe kolom -->
                    </tr>
                    <?php
                    // Verander de SQL-query om de gebruikersnaam op te halen via een JOIN-clausule
                    $sql = "SELECT workhours.user_id, workhours.start, workhours.end, account.username 
                            FROM workhours 
                            JOIN account ON workhours.user_id = account.id";

                    // Als de rol niet admin of manager is, voeg een voorwaarde toe om alleen gegevens van de huidige gebruiker te selecteren
                    if(!$isAdmin && !$isManager) {
                        $userID = $_SESSION['id'];
                        $sql .= " WHERE workhours.user_id = $userID";
                    }

                    // Voer de SQL-query uit
                    $result = $conn->query($sql);

                    // Controleer of er een fout is opgetreden bij het uitvoeren van de query
                    if (!$result) {
                        echo "Error: " . $conn->error;
                    } else {
                        // Controleer of er resultaten zijn
                        if ($result->num_rows > 0) {
                            // Output data van elke rij
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row["user_id"]. "</td>";
                                echo "<td>" . $row["username"]. "</td>"; // Gebruikersnaam toegevoegd
                                echo "<td>" . $row["start"]. "</td>";
                                echo "<td>" . $row["end"]. "</td>";
                                // Bereken en toon het verschil in uren tussen start en einde
                                $start = new DateTime($row["start"]);
                                $end = new DateTime($row["end"]);
                                $diff = $start->diff($end);
                                echo "<td>" . $diff->format('%h hours %i minutes') . "</td>";

                                // Controleer of de werktijd meer dan 7 uur is om de overwerkwaarde weer te geven
                                if ($diff->h > 7) {
                                    echo "<td class='overworked'>" . ($diff->h - 7) . " hours " . $diff->i . " minutes</td>";
                                } else {
                                    echo "<td>0 hours 0 minutes</td>";
                                }
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>Geen resultaten gevonden</td></tr>";
                        }
                    }

                    // Sluit de databaseverbinding
                    $conn->close();
                    ?>
                </table>
            </div>
            
            <?php if(!$isAdmin && !$isManager): ?>
                <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                    <button type="submit" name="action" value="start">Start</button>
                    <button type="submit" name="action" value="end">Stop</button>
                </form>
            <?php endif; ?>
            <div class="homeImg">
                </div>
        </div>
    </div>
    
</body>
</html>
