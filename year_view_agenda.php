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

// Haal de gebruikersnaam van de ingelogde gebruiker uit de sessie
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

// Haal de agenda-items op voor de ingelogde gebruiker of alle gebruikers als Manager
$agenda_items_by_day_and_hour = fetchAgendaItems($pdo, $username, $isManager);

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
    <link rel="stylesheet" href="styles/yearview.css">
    <style>
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-button {
            background-color: #FFDD00; 
            color: white; 
            padding: 10px 20px; 
            font-size: 16px;
            border: none; 
            cursor: pointer; 
        }

        .dropdown-content {
            display: none; 
            position: absolute; 
            min-width: 160px; 
            z-index: 1; 
        }

        .dropdown-content a {
            color: black; 
            padding: 12px 16px;
            text-decoration: none; 
            display: block; 
        }

        .dropdown-content a:hover {
            background-color: whitesmoke;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown:hover .dropdown-button {
            background-color: #FFDD00;
        } 
    </style>
</head>
<body>
    <?php include_once(__DIR__ . "/classes/nav.php"); ?>

    <div class="agenda-container">
        <div class="title">
            <h1>Yearly View</h1>
            <a class="kruis" href="./calendar.php"></a>
        </div>
        <div class="dropdown">
            <button class="dropdown-button">View Options</button>
            <div class="dropdown-content">
                <a class="formButton" href="daily_vieuw_agenda.php">Daily view</a>
                <a class="formButton" href="visibleagenda.php">Weekly view</a>
                <a class="formButton" href="./monthly_view_agenda.php">Monthly view</a>
                <a class="formButton" href="year_view_agenda.php">Yearly view</a>
            </div>
        </div>

        <div class="agenda">
            <?php
            $currentYear = date("Y");
            for ($month = 1; $month <= 12; $month++) {
                echo "<div class='month'>";
                echo "<div class='month-title'>" . date("F", mktime(0, 0, 0, $month, 1, $currentYear)) . "</div>";
                echo "<div class='week'>";
                for ($day = 1; $day <= cal_days_in_month(CAL_GREGORIAN, $month, $currentYear); $day++) {
                    $currentDate = date("Y-m-d", mktime(0, 0, 0, $month, $day, $currentYear));
                    $weekDay = date("N", strtotime($currentDate));
                    if ($weekDay == 1 && $day != 1) {
                        echo "</div><div class='week'>"; // Close previous week and start a new week
                    }
                    echo "<div class='day'>";
                    if (isset($agenda_items_by_day_and_hour[$currentDate])) {
                        $agenda_items_for_day = $agenda_items_by_day_and_hour[$currentDate];
                        $hasScheduledItem = false;
                        foreach ($agenda_items_for_day as $hour => $agenda_items_for_hour) {
                            if (!empty($agenda_items_for_hour)) {
                                $hasScheduledItem = true;
                                break;
                            }
                        }
                        $bg_color = $hasScheduledItem ? "#e6f7ff" : "";
                        echo "<div class='day-content' style='background-color: $bg_color;'>";
                    }
                    // Voeg een hyperlink toe naar de dagelijkse weergave en stuur de datum als parameter
                    echo "<p><a href='daily_vieuw_agenda.php?date=$currentDate'>" . $day . "</a></p>"; 
                    if (isset($agenda_items_by_day_and_hour[$currentDate])) {
                        echo "</div>"; // Close .day-content
                    }
                    echo "</div>"; // Close .day
                }
                echo  "</div>"; // Close .week
                echo "</div>"; // Close .month
            }
            ?>
        </div> <!-- Close .agenda -->
    </div>

    <script>
        document.getElementById('username').addEventListener('change', function() {
            var userId = this.options[this.selectedIndex].getAttribute('data-user-id');
            document.getElementById('user_id').value = userId;
        });
    </script>
</body>
</html>
