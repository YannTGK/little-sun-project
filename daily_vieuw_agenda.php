<?php
include_once('classes/Db.php');
include_once('classes/user_agenda.php');
include_once('classes/agenda.php');

$pdo = Db::getConnection();
$user = new User();
$agenda = new Agenda($pdo);

$assigned_tasks = $agenda->fetchAssignedTasks();

$currentDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$agenda->setDefaultAcceptStatus($currentDate);

$agenda_items_by_hour = $agenda->fetchAgendaItems($user->getUsername(), $user->isManager(), $currentDate);

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
    <style>
        .date-container {
            display: flex;
            align-items: center;
            margin-right: 65%;
        }
        .date-container input {
            display: none; 
        }
        .date-container i {
            font-size: 24px;
            cursor: pointer;
            margin-left: 10px;
        }
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

    <div class="screen">
        <div class="title">
            <h1>Daily View - <?php echo date('l, F j, Y', strtotime($currentDate)); ?></h1>
            <div class="date-container">
                <input type="text" id="datepicker" placeholder="Choose a date">
                <i class="fa fa-calendar" id="calendarIcon"></i>
            </div>
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
            <?php if ($user->isAdmin() || $user->isManager()): ?>
                <div class="agenda-form">
                    <h2>Fill in agenda</h2>
                    <a class="formButton" href="filinagenda.php">Go to form</a>
                </div>
            <?php endif; ?>
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
