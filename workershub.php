<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

include_once(__DIR__ . "/classes/personal.php");
include_once(__DIR__ . "/classes/allworkers.php");
include_once(__DIR__ . "/classes/createTaskType.php");
include_once(__DIR__ . "/classes/Db.php");

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $new_password = $_POST['new_password'];
    
    if (!empty($username) && !empty($new_password)) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update the password in the database
        $db = new mysqli('localhost', 'root', 'root', 'littlesun');
        if ($db->connect_error) {
            die("Connection failed: " . $db->connect_error);
        }

        $stmt = $db->prepare("UPDATE account SET password = ? WHERE username = ?");
        $stmt->bind_param('ss', $hashed_password, $username);
        
        if ($stmt->execute()) {
        } else {
            echo "Error updating password: " . $stmt->error;
        }

        $stmt->close();
        $db->close();
    } else {
        echo "Username and new password must not be empty.";
    }
}

$allTasks = Task::getAll();

if (isset($_POST['add_task'])) {
    // Your existing add task logic
}

if (isset($_POST['delete_task'])) {
    // Your existing delete task logic
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
        th, td {
            text-align: left;
            padding: 8px;
            border: none;
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
            <h1><?php echo $_SESSION['role'] ?? "Rol niet gevonden in sessie."; ?> employee information</h1>
            <a class="kruis" href="./index.php"></a>
        </div>
        <input type="text" id="usernameInput" placeholder="Filter on username...">
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
                    $workersData = $allWorkers->fetchWorkers(); // Fetch all workers

                    foreach ($workersData as $worker) {
                        echo "<tr>";
                        echo "<td><img class='employeeImg' src='" . htmlspecialchars($worker['profilepicture']) . "' alt='Profielfoto'></td>";
                        echo "<td>" . htmlspecialchars($worker['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($worker['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($worker['role']) . "</td>";
                        echo "<td>" . htmlspecialchars($worker['TaskType']) . "</td>";
                        echo "<td>
                                <form method='post' onsubmit=\"return confirm('Are you sure you want to change the password?')\">
                                    <input type='hidden' name='username' value='" . htmlspecialchars($worker['username']) . "'>
                                    <input type='password' name='new_password' placeholder='Enter new password' required>
                                    <input type='submit' name='submit' value='Change'>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div>
            <h2>Add a task to a user</h2>
            <form method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                <label for="task_type">TaskType:</label>
                <select id="task_type" name="task_type" required>
                    <?php foreach ($allTasks as $task) : ?>
                        <option value="<?php echo htmlspecialchars($task['TaskType']); ?>"><?php echo htmlspecialchars($task['TaskType']); ?></option>
                    <?php endforeach ?>
                </select>
                <input type="submit" name="add_task" value="Add">
            </form>
        </div>

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
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const usernameInput = document.getElementById('usernameInput');
            const tableBody = document.getElementById('workersTableBody');
            const tableRows = tableBody.getElementsByTagName('tr');

            usernameInput.addEventListener('input', function() {
                const filter = this.value.toLowerCase();

                for (let i = 0; i < tableRows.length; i++) {
                    const usernameCell = tableRows[i].getElementsByTagName('td')[1];
                    if (usernameCell) {
                        const username = usernameCell.textContent || usernameCell.innerText;
                        if (username.toLowerCase().indexOf(filter) > -1) {
                            tableRows[i].style.display = "";
                        } else {
                            tableRows[i].style.display = "none";
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
