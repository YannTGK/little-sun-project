<?php
session_start();

// Controleer de rol van de ingelogde gebruiker
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$isManager = isset($_SESSION['role']) && $_SESSION['role'] === 'Manager';

// Configuration
$db_host = 'localhost';
$db_username = 'root';
$db_password = 'root';
$db_name = 'littlesun';

// Create a PDO instance
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_username, $db_password);

// Define a function to fetch assigned tasks
function fetchAssignedTasks($pdo) {
    $query = "SELECT a.id, a.username, a.email, t.TaskType, at.tasktype_id, at.user_id 
               FROM account a 
               JOIN assignedtasks at ON a.id = at.user_id 
               JOIN tasks t ON at.tasktype_id = t.id";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Define a function to fetch agenda items for the logged-in user or all users if Manager
function fetchAgendaItems($pdo, $username, $isManager) {
    if ($isManager) {
        $query = "SELECT * FROM agenda";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $agenda_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $query = "SELECT * FROM agenda WHERE username = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $agenda_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $agenda_items_by_day_and_hour = [];

    foreach ($agenda_items as $item) {
        $day = $item['day'];
        $hour = intval(substr($item['startinghour'], 0, 2)); // Extract hour from starting hour

        if (!isset($agenda_items_by_day_and_hour[$day])) {
            $agenda_items_by_day_and_hour[$day] = [];
        }

        if (!isset($agenda_items_by_day_and_hour[$day][$hour])) {
            $agenda_items_by_day_and_hour[$day][$hour] = [];
        }

        $agenda_items_by_day_and_hour[$day][$hour][] = $item;
    }

    return $agenda_items_by_day_and_hour;
}

function acceptOrDeclineTask($pdo, $task_id, $accept) {
    $query = "UPDATE agenda SET accept = :accept WHERE id = :task_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':accept', $accept, PDO::PARAM_INT);
    $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
    $stmt->execute();
}

$assigned_tasks = fetchAssignedTasks($pdo);

// Haal de gebruikersnaam van de ingelogde gebruiker uit de sessie
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

// Haal de agenda-items op voor de ingelogde gebruiker of alle gebruikers als Manager
$agenda_items_by_day_and_hour = fetchAgendaItems($pdo, $username, $isManager);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["accept_task"])) {
        acceptOrDeclineTask($pdo, $_POST["task_id"], 1);
    } elseif (isset($_POST["decline_task"])) {
        acceptOrDeclineTask($pdo, $_POST["task_id"], 0);
    }
    header("Location: ". htmlspecialchars($_SERVER["PHP_SELF"]));
    exit;
}

$pdo = null;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Agenda</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Oxygen:400,700" rel="stylesheet">
    <link rel="stylesheet" href="styles/normalize.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/agenda.css">
</head>
<body>
    <?php include_once(__DIR__ . "/classes/nav.php"); ?>

    <div class="screen">
        <div class="title">
            <h1>Daily View</h1>
            <a class="kruis" href="./calendar.php"></a>
        </div>
        <div class="nav2holder">
            <div class="nav2">
                <div class="editLink">
                    <a class="formButton" href="visibleagenda.php">Weekly view</a>
                </div>
                <div class="editLink">
                    <a class="formButton" href="./monthly_view_agenda.php">Monthly view</a>
                </div>
                <div class="editLink">
                    <a class="formButton" href="year_view_agenda.php">Yearly view</a>
                </div>
            </div>
            <form class="nav2" action="" method="post">
                <div class="editLink">
                    <input class="formButton2" type="submit" name="prev_week" value="Day before">
                </div>
                <div class="editLink">
                    <input class="formButton2" type="submit" name="next_week" value="Day after">
                </div>
            </form>
        </div>

        <div class="holder">
           
            <div class="agenda">
                
                <?php
                $startOfWeek = date('Y-m-d', strtotime('monday this week'));
    
                if (isset($_POST['prev_week'])) {
                    $startOfWeek = date('Y-m-d', strtotime($startOfWeek . ' -1 week'));
                } elseif (isset($_POST['next_week'])) {
                    $startOfWeek = date('Y-m-d', strtotime($startOfWeek . ' +1 week'));
                }
    
                $endOfWeek = date('Y-m-d', strtotime($startOfWeek . ' +6 days'));
    
                $currentDate = date('Y-m-d'); // Set current date
                while ($currentDate <= $endOfWeek) {
                    echo "<div class='day'>";
                    echo "<h3>" . date('l', strtotime($currentDate)) . "</h3>";
                    echo "<p>" . date('F j, Y', strtotime($currentDate)) . "</p>";
    
                    for ($hour = 7; $hour <= 19; $hour++) {
                        echo "<div class='hour-block'>";
                        echo "<p>$hour:00 - " . ($hour + 1) . ":00</p>";
                        if (isset($agenda_items_by_day_and_hour[$currentDate]) && isset($agenda_items_by_day_and_hour[$currentDate][$hour])) {
                            $agenda_items_for_hour = $agenda_items_by_day_and_hour[$currentDate][$hour];
                            foreach ($agenda_items_for_hour as $agenda_item) {
                                if (isset($agenda_item["username"])) {
                                    $starting_hour = intval(substr($agenda_item['startinghour'], 0, 2));
                                    $end_hour = intval(substr($agenda_item['endhour'], 0, 2));
                                    if ($hour >= $starting_hour && $hour < $end_hour) {
                                        $bg_color = "red";
                                    } else {
                                        $bg_color = "";
                                    }
                                    if ($agenda_item["accept"] === null) {
                                        $bg_color = "grey";
                                    } elseif ($agenda_item["accept"] == 1) {
                                        $bg_color = "green";
                                    } elseif ($agenda_item["accept"] == 0) {
                                        $bg_color = "red";
                                    }
                                    echo "<p style='background-color: $bg_color;'>";
                                    echo $agenda_item["task"] . " - " . $agenda_item["username"] . "</p>";
                                    echo "<p style='background-color: $bg_color;'>Start hour: " . $agenda_item['startinghour'] . "</br>". "End hour: " . $agenda_item['endhour'] . "</p>";
                                    if ($agenda_item["accept"] === null) {
                                        echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
                                        echo "<input type='hidden' name='task_id' value='" . $agenda_item["id"] . "'>";
                                        echo "<input type='submit' name='accept_task' value='Accept'>";
                                        echo "<input type='submit' name='decline_task' value='Decline'>";
                                        echo "</form>";
                                    }
                                    
                                } else {
                                    echo "<p>" . $agenda_item["task"] . "</p>";
                                }
                            }
                        }
                        echo "</div>";
                    }
    
                    echo "</div>";
                    $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                }
                ?>
            </div>
            <?php if($isAdmin || $isManager): ?>
            
            <div class="agenda-form">
                <h2>Fill in agenda</h2>
                <a href="filinagenda.php" class="formButton">Fill in agenda</a>
            </div>
        <?php endif; ?>
        </div>
    </div>
</body>
</html>
