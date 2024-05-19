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

$assigned_tasks = fetchAssignedTasks($pdo);

// Haal de gebruikersnaam van de ingelogde gebruiker uit de sessie
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

// Haal de agenda-items op voor de ingelogde gebruiker of alle gebruikers als Manager
$agenda_items_by_day_and_hour = fetchAgendaItems($pdo, $username, $isManager);

// Haal alle vakantie-items op
$query_vacation = "SELECT * FROM vacation WHERE accepted = 1";
$stmt_vacation = $pdo->prepare($query_vacation);
$stmt_vacation->execute();
$vacation_items = $stmt_vacation->fetchAll(PDO::FETCH_ASSOC);

$errorMessage = ''; // Initialiseer de foutmelding

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Controleer of een taak geaccepteerd of geweigerd is
    if (isset($_POST['accept_task']) || isset($_POST['decline_task'])) {
        $task_id = $_POST['task_id'];
        $accept = isset($_POST['accept_task']) ? 1 : 0;
        acceptOrDeclineTask($pdo, $task_id, $accept);
        header("Location: ". htmlspecialchars($_SERVER["PHP_SELF"]));
        exit;
    }
}

function acceptOrDeclineTask($pdo, $task_id, $accept) {
    $query = "UPDATE agenda SET accept = :accept WHERE id = :task_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':accept', $accept, PDO::PARAM_INT);
    $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
    $stmt->execute();
}

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
                        Weekly view
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
                    <button class="dropdown-button">Switch view</button>
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

        <!-- Foutmelding weergeven -->
        <?php if(isset($errorMessage) && !empty($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <div class="holder">
            <div class="agenda">
                <?php
                // Haal de geselecteerde datum op uit de URL
                $selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
                $startOfWeek = date('Y-m-d', strtotime('monday this week', strtotime($selectedDate)));

                if (isset($_POST['prev_week'])) {
                    $startOfWeek = date('Y-m-d', strtotime($startOfWeek . ' -1 week'));
                } elseif (isset($_POST['next_week'])) {
                    $startOfWeek = date('Y-m-d', strtotime($startOfWeek . ' +1 week'));
                }

                $endOfWeek = date('Y-m-d', strtotime($startOfWeek . ' +6 days'));

                $currentDate = $startOfWeek;

                // Print table header
                echo "<table class='table table-bordered'>";
                echo "<thead><tr>";
                echo "<th>Time</th>";
                for ($day = 0; $day < 7; $day++) {
                    $date = date('Y-m-d', strtotime("$startOfWeek +$day days"));
                    $class = ($date == date('Y-m-d')) ? 'current-day' : ''; // Controleer of de dag overeenkomt met de huidige datum
                    echo "<th class='$class'>" . date('l', strtotime($date)) . "<br>" . date('F j, Y', strtotime($date)) . "</th>";
                }
                echo "</tr></thead>";
                echo "<tbody>";

                // Print table rows for each hour
                for ($hour = 7; $hour <= 19; $hour++) {
                    echo "<tr>";
                    echo "<td>" . $hour . ":00 - " . ($hour + 1) . ":00</td>";
                
                    for ($day = 0; $day < 7; $day++) {
                        $date = date('Y-m-d', strtotime("$startOfWeek +$day days"));
                        echo "<td>";
                        if (isset($agenda_items_by_day_and_hour[$date]) && isset($agenda_items_by_day_and_hour[$date][$hour])) {
                            $agenda_items_for_hour = $agenda_items_by_day_and_hour[$date][$hour];
                            foreach ($agenda_items_for_hour as $agenda_item) {
                                $starting_hour = intval(substr($agenda_item['startinghour'], 0, 2));
                                $end_hour = intval(substr($agenda_item['endhour'], 0, 2));
                                if ($hour >= $starting_hour && $hour < $end_hour) {
                                    $bg_color = "green"; // Set background color to green for accepted items
                                    echo "<div style='background-color: $bg_color; padding: 10px; margin-bottom: 10px; border-radius: 5px; color: white; font-weight: bold;'>";
                                    echo htmlspecialchars($agenda_item["task"]) . " - " . htmlspecialchars($agenda_item["username"]) . "<br>";
                                    echo "<span style='font-weight: normal;'>Start: " . htmlspecialchars($agenda_item['startinghour']) . "<br>";
                                    echo "End: " . htmlspecialchars($agenda_item['endhour']) . "</span>";
                                    echo "</div>";
                                }
                            }
                        }
                        echo "</td>";
                    }
                    echo "</tr>";
                }
                echo "</tbody>";
                
                echo "</table>";
                ?>
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