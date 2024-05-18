<?php
require_once(__DIR__ . "/classes/Db.php");
require_once(__DIR__ . "/classes/usersession.php");
require_once(__DIR__ . "/classes/workhours.php");

$userSession = new UserSession();
$db = new Db();
$conn = $db->getConnection();
$workHours = new WorkHours($conn);

$currentTime = date("Y-m-d H:i:s");

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === "start") {
        $message = $workHours->startWork($userSession->getUserID(), $currentTime);
    } elseif ($action === "end") {
        $message = $workHours->endWork($userSession->getUserID(), $currentTime);
    }
    if ($message !== true) {
        echo $message;
        exit;
    }
}

$workedTimes = $workHours->getTotalWorkedMinutes($userSession->getUserID());
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

                        if(!$userSession->isAdmin() && !$userSession->isManager()) {
                            $userID = $userSession->getUserID();
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

            <?php if(!$userSession->isAdmin() && !$userSession->isManager()): ?>
                <form class="checkHolder" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
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
