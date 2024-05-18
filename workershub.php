<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

include_once(__DIR__ . "/classes/personal.php"); // Include the Personal class
include_once(__DIR__ . "/classes/allworkers.php"); // Include the AllWorkers class

include_once(__DIR__ . "/classes/createTaskType.php");

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $newPassword = $_POST['new_password'];

    try {
        // Get the user by username
        $user = Personal::getByUsername($username);

        if ($user) {
            // Update the password in the database
            $user->setPassword(password_hash($newPassword, PASSWORD_DEFAULT));
            $user->save(); // Save the updated user

            // Show a success message to the user
            $message = "Het wachtwoord van gebruiker $username is succesvol gewijzigd.";
            echo "<script>alert('$message');</script>";
        } else {
            throw new Exception("Gebruiker niet gevonden.");
        }
    } catch (Exception $e) {
        // Handle any exceptions that occur during the password update process
        echo "<script>alert('Er is een fout opgetreden bij het wijzigen van het wachtwoord: " . $e->getMessage() . "');</script>";
    }
}

$allTasks = Task::getAll();

// Toevoegen van een taak aan de gebruiker
if (isset($_POST['add_task'])) {
    $username = $_POST['username'];
    $taskType = $_POST['task_type'];

    // Voeg de taak toe aan de gebruiker in de database
    try {
        // Voeg hier je logica toe om de taak aan de gebruiker toe te voegen
        $message = "Taak '$taskType' is succesvol toegevoegd aan gebruiker '$username'.";
        echo "<script>alert('$message');</script>";
    } catch (Exception $e) {
        // Handle any exceptions that occur during the task addition process
        echo "<script>alert('Er is een fout opgetreden bij het toevoegen van de taak: " . $e->getMessage() . "');</script>";
    }
}

// Verwijderen van een taak van de gebruiker
if (isset($_POST['delete_task'])) {
    $username = $_POST['username'];
    $taskType = $_POST['task_type'];

    // Verwijder de taak van de gebruiker in de database
    try {
        // Voeg hier je logica toe om de taak van de gebruiker te verwijderen
        $message = "Taak '$taskType' is succesvol verwijderd van gebruiker '$username'.";
        echo "<script>alert('$message');</script>";
    } catch (Exception $e) {
        // Handle any exceptions that occur during the task deletion process
        echo "<script>alert('Er is een fout opgetreden bij het verwijderen van de taak: " . $e->getMessage() . "');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>workershub</title>
    <link rel="stylesheet" href="styles/normalize.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/workershub.css">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            text-align: left;
            padding: 8px;
            border: none;
            /* Removing borders */
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <?php include_once(__DIR__ . "/classes/nav.php"); ?>

    <div class="screen">
        <div class="title">
            <h1>
                <?php if (isset($_SESSION['role'])) {
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
                        <th>Profile picture</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Tasktype</th>
                        <th>Change Password</th>
                    </tr>
                </thead>
                <tbody id="workersTableBody">
                    <?php
                    $allWorkers = new AllWorkers();
                    $workersData = $allWorkers->fetchWorkers();

                    // Array to store unique task types
                    $taskTypes = [];

                    foreach ($workersData as $worker) {
                        echo "<tr>";
                        echo "<td><img class='employeeImg' src='" . $worker['profilepicture'] . "' alt='Profielfoto'></td>";
                        echo "<td>" . $worker['username'] . "</td>";
                        echo "<td>" . $worker['email'] . "</td>";
                        echo "<td>" . $worker['role'] . "</td>";
                        echo "<td>" . $worker['TaskType'] . "</td>";
                        echo "<td>
                                <form method='post' onsubmit=\"return confirm('Are you sure you want to change the password?')\">
                                    <input type='hidden' name='username' value='" . $worker['username'] . "'>
                                    <input type='password' name='new_password' placeholder='Enter new password'>
                                    <input type='submit' name='submit' value='Change'>
                                </form>
                              </td>";
                        echo "</tr>";

                        // Add task type to the array if not already present
                        if (!in_array($worker['TaskType'], $taskTypes)) {
                            $taskTypes[] = $worker['TaskType'];
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Toevoegen van een taak aan de gebruiker -->
        <div>
            <h2>add a task to a user</h2>
            <form method="post">
                <label for="username">Username            :</label>
            <input type="text" id="username" name="username" required>
            <label for="task_type">TastkType:</label>
            <select id="task_type" name="task_type" required>
                <?php foreach ($allTasks as $task) : ?>
                    <option value="<?php echo htmlspecialchars($task['TaskType']); ?>"><?php echo htmlspecialchars($task['TaskType']); ?></option>
                <?php endforeach ?>
            </select>
            <input type="submit" name="add_task" value="add">
        </form>
    </div>

    <!-- Verwijderen van een taak van de gebruiker -->
    <div>
        <h2>Delete a user task</h2>
        <form method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="task_type">TaskType:</label>
            <select id="task_type" name="task_type" required>
                <?php foreach ($allTasks as $task) : ?>
                    <option value="<?php echo htmlspecialchars($task['TaskType']); ?>"><?php echo htmlspecialchars($task['TaskType']); ?></option>
                <?php endforeach ?>
            </select>
            <input type="submit" name="delete_task" value="Delete">
        </form>
    </div>
</div>
<script src="js/workershub.js"></script>

</body>
</html>