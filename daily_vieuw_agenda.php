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
function fetchAgendaItems($pdo, $username, $isManager, $date) {
    if ($isManager) {
        $query = "SELECT * FROM agenda WHERE day = :day";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':day', $date, PDO::PARAM_STR);
        $stmt->execute();
        $agenda_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $query = "SELECT * FROM agenda WHERE username = :username AND day = :day";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':day', $date, PDO::PARAM_STR);
        $stmt->execute();
        $agenda_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $agenda_items_by_hour = [];

    foreach ($agenda_items as $item) {
        $hour = intval(substr($item['startinghour'], 0, 2)); // Extract hour from starting hour
        if (!isset($agenda_items_by_hour[$hour])) {
            $agenda_items_by_hour[$hour] = [];
        }
        $agenda_items_by_hour[$hour][] = $item;
    }

    return $agenda_items_by_hour;
}

// Zet standaard de accept status op 1 voor alle taken
function setDefaultAcceptStatus($pdo, $date) {
    $query = "UPDATE agenda SET accept = 1 WHERE day = :day AND accept IS NULL";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':day', $date, PDO::PARAM_STR);
    $stmt->execute();
}

$assigned_tasks = fetchAssignedTasks($pdo);

// Haal de gebruikersnaam van de ingelogde gebruiker uit de sessie
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

// Huidige datum voor de dagweergave
$currentDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Zet de standaard accept status voor de huidige datum
setDefaultAcceptStatus($pdo, $currentDate);

// Haal de agenda-items op voor de ingelogde gebruiker of alle gebruikers als Manager
$agenda_items_by_hour = fetchAgendaItems($pdo, $username, $isManager, $currentDate);

$pdo = null;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Daily Agenda</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Oxygen:400,700" rel="stylesheet">
    <link rel="stylesheet" href="styles/normalize.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/agenda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<body>
    <?php include_once(__DIR__ . "/classes/nav.php"); ?>

    <div class="screen">
        <div class="title">
            <div class="titleLeft">
                <div class="dateNav">
                    <a>&#10094;</a>
                    <h3>
                        <?php echo date('l, F j, Y', strtotime($currentDate)); ?>
                    </h3>
                    <a>&#10095;</a>
                </div>
                <div class="date-container">
                    <input type="text" id="datepicker" placeholder="Choose a date">
                    <i class="fa fa-calendar" id="calendarIcon"></i>
                </div>
            </div>

            <div class="titleRight">
                <div class="dropdown">
                    <button class="dropdown-button">View Options</button>
                    <div class="dropdown-content">
                        <a class="formButton" href="daily_vieuw_agenda.php">Daily view</a>
                        <a class="formButton" href="visibleagenda.php">Weekly view</a>
                        <a class="formButton" href="./monthly_view_agenda.php">Monthly view</a>
                        <a class="formButton" href="year_view_agenda.php">Yearly view</a>
                    </div>
                </div>
                <a class="kruis" href="./calendar.php"></a>

            </div>
        </div>

        <?php if($isAdmin || $isManager): ?>
            <div class="agenda-form">
                <a class="formButton" href="filinagenda.php">+ Add task</a>
            </div>
        <?php endif; ?>

        <div class="holder">
            <div class="agenda">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Task</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        for ($hour = 7; $hour < 20; $hour++) {
                            echo "<tr>";
                            echo "<td>" . sprintf('%02d:00 - %02d:00', $hour, $hour + 1) . "</td>";
                            echo "<td>";
                            if (isset($agenda_items_by_hour[$hour])) {
                                foreach ($agenda_items_by_hour[$hour] as $agenda_item) {
                                    $bg_color = $agenda_item["accept"] == 1 ? "green" : ($agenda_item["accept"] == 0 ? "red" : "grey");
                                    echo "<div style='background-color: $bg_color; padding: 5px; margin-bottom: 5px;'>";
                                    echo $agenda_item["task"] . " - " . $agenda_item["username"] . "<br>";
                                    echo "Start: " . $agenda_item['startinghour'] . "<br>";
                                    echo "End: " . $agenda_item['endhour'];
                                    echo "</div>";
                                }
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var datePicker = flatpickr("#datepicker", {
                dateFormat: "Y-m-d",
                onChange: function(selectedDates, dateStr, instance) {
                    window.location.href = "?date=" + dateStr;
                }
            });

            document.getElementById('calendarIcon').addEventListener('click', function() {
                datePicker.open();
            });
        });
    </script>
</body>
</html>