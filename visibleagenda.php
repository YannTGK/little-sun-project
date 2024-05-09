<?php
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

// Define a function to fetch agenda items
function fetchAgendaItems($pdo) {
    $query = "SELECT * FROM agenda";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $agenda_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

// Define a function to insert agenda item
function insertAgendaItem($pdo, $username, $task, $startinghour, $endhour, $day) {
    $query = "INSERT INTO agenda (username, task, startinghour, endhour, day) VALUES (:username, :task, :startinghour, :endhour, :day)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':task', $task);
    $stmt->bindParam(':startinghour', $startinghour);
    $stmt->bindParam(':endhour', $endhour);
    $stmt->bindParam(':day', $day);
    $stmt->execute();
}

// Define a function to accept or decline a task
function acceptOrDeclineTask($pdo, $task_id, $accept) {
    $query = "UPDATE agenda SET accept = :accept WHERE id = :task_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':accept', $accept, PDO::PARAM_INT);
    $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
    $stmt->execute();
}

// Fetch assigned tasks
$assigned_tasks = fetchAssignedTasks($pdo);

// Fetch agenda items
$agenda_items_by_day_and_hour = fetchAgendaItems($pdo);

// Group assigned tasks by user
$assigned_tasks_by_user = [];
foreach ($assigned_tasks as $task) {
    $username = $task['username'];
    if (!isset($assigned_tasks_by_user[$username])) {
        $assigned_tasks_by_user[$username] = [];
    }
    $assigned_tasks_by_user[$username][] = $task;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["accept_task"])) {
        acceptOrDeclineTask($pdo, $_POST["task_id"], 1);
    } elseif (isset($_POST["decline_task"])) {
        acceptOrDeclineTask($pdo, $_POST["task_id"], 0);
    } else {
        $username = $_POST["username"];
        $task = $_POST["task"];
        $startinghour = $_POST["startinghour"];
        $endhour = $_POST["endhour"];
        $day = $_POST["day"];
        insertAgendaItem($pdo, $username, $task, $startinghour, $endhour, $day);
    }
    header("Location: ". htmlspecialchars($_SERVER["PHP_SELF"]));
    exit;
}

// Close the PDO instance
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
    <link rel="stylesheet" href="styles/agenda.css">
</head>
<body>
    <h1>Weekly View</h1>

    <div class="screen">
    <h1>Hourly Agenda</h1>
    <div class="navigation">
      <form action="" method="post">
        <input type="submit" name="prev_week" value="Previous Week">
        <input type="submit" name="next_week" value="Next Week">
      </form>
    </div>
    <div class="agenda">
      <div class="hour">
        <div class="hour-block">
          <p>6:00 - 7:00</p>
        </div>
        <?php
          for ($hour = 7; $hour <= 19; $hour++) {
            echo "<div class='hour-block'>";
            echo "<p>$hour:00 - " . ($hour + 1) . ":00</p>";
            echo "</div>";
          }
        ?>
      </div>
      <?php
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        
        if (isset($_POST['prev_week'])) {
          $startOfWeek = date('Y-m-d', strtotime($startOfWeek . ' -1 week'));
        } elseif (isset($_POST['next_week'])) {
          $startOfWeek = date('Y-m-d', strtotime($startOfWeek . ' +1 week'));
        }
        
        $endOfWeek = date('Y-m-d', strtotime($startOfWeek . ' +6 days'));
        
        // Initialize current date outside the loop
        $currentDate = $startOfWeek;
        while ($currentDate <= $endOfWeek) {
          echo "<div class='day'>";
          echo "<h2>" . date('l', strtotime($currentDate)) . "</h2>";
          echo "<p>" . date('F j, Y', strtotime($currentDate)) . "</p>";

          // Loop through the hours and display the agenda items for each hour
          for ($hour = 7; $hour <= 19; $hour++) {
            echo "<div class='hour-block'>";
            echo "<p>$hour:00 - " . ($hour + 1) . ":00</p>";
            // Check if there's an agenda item for this hour and day
            if (isset($agenda_items_by_day_and_hour[$currentDate]) && isset($agenda_items_by_day_and_hour[$currentDate][$hour])) {
              $agenda_items_for_hour = $agenda_items_by_day_and_hour[$currentDate][$hour];
              foreach ($agenda_items_for_hour as $agenda_item) {
                // Print the username if available for this hour and day
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
  </div>

    <hr />
    <div class="row">
        <div class="col-xs-6">

        </div>
        <div class="assingned_tasks">
            <h2>Assigned Tasks</h2>
            <?php
                foreach($assigned_tasks_by_user as $user_id => $user_tasks) {
                    echo "<div>";
                    foreach($user_tasks as $task) {
                        echo "<p>User: " . $task["username"] . ", Email: " . $task["email"] . "</p>";
                        echo "<p>TaskType: " . $task["TaskType"] . "</p>";
                    }
                    echo "</div>";
                }
            ?>
        </div>
    </div>
    <hr />

    <!-- Formulier voor het invullen van de agenda -->
    <div class="agenda-form">
        <h2>Vul de agenda in</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label for="username">Gebruikersnaam:</label>
                <select class="form-control" id="username" name="username">
                    <?php foreach($assigned_tasks_by_user as $user_id => $user_tasks): ?>
                        <?php foreach($user_tasks as $task): ?>
                            <option value="<?php echo $task['username']; ?>"><?php echo $task['username']; ?></option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="task">Taak:</label>
                <select class="form-control" id="task" name="task">
                    <?php foreach($assigned_tasks_by_user as $user_id => $user_tasks): ?>
                        <?php foreach($user_tasks as $task): ?>
                            <option value="<?php echo $task['TaskType']; ?>"><?php echo $task['TaskType']; ?></option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="startinghour">Startuur:</label>
                <input type="time" class="form-control" id="startinghour" name="startinghour">
            </div>
            <div class="form-group">
                <label for="endhour">Einduur:</label>
                <input type="time" class="form-control" id="endhour" name="endhour">
            </div>
            <div class="form-group">
                <label for="day">Datum:</label>
                <input type="date" class="form-control" id="day" name="day">
            </div>
            <button type="submit" class="btn btn-primary">Opslaan</button>
        </form>
    </div>


</body>
</html>
