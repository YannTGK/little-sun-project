<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

$conn = new PDO('mysql:host=localhost;dbname=littlesun', "root", "root");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['reason']) && isset($_POST['date']) && isset($_POST['enddate'])) {
        $reason = $_POST['reason'];
        $date = $_POST['date'];
        $enddate = $_POST['enddate'];

        try {
            $user_id = $_SESSION['id'];
            $username = $_SESSION['username'];
            $role = $_SESSION['role']; 
    
            $accepted = ($_POST['reason'] === 'sick') ? 1 : ($role === 'Manager' ? 1 : 0);


            $stmt = $conn->prepare("INSERT INTO vacation (user_id, username, reason, date, enddate, accepted) VALUES (:user_id, :username, :reason, :date, :enddate, :accepted)");

            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':enddate', $enddate);
            $stmt->bindParam(':accepted', $accepted);

            $stmt->execute();

        } catch (PDOException $e) {
            // Handle any potential errors here
        }
    } else {
        // Handle any potential errors here
    }
}

$query = "SELECT *, COALESCE(rejectreason, '') AS rejectreason FROM vacation WHERE user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['id']);
$stmt->execute();
$vacations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome <?php echo $_SESSION['username']; ?></title>
    <link rel="stylesheet" href="styles/normalize.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/selfVacation.css">
</head>
<body>
<?php include_once(__DIR__ . "/classes/nav.php"); ?>

<div class="screen">
    <div class="title">
        <h1>Plan your vacation <?php echo $_SESSION['username']; ?></h1>
        <a class="kruis" href="./profile.php"></a>
    </div>

    <div class="holder">
        <div class="left">
            <form class="formHolder" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <div class="form">
                    <label for="reason">Reason</label>
                    <input type="text" id="reason" name="reason">
                </div>

                <div class="form">
                    <label for="date">Start Date and Time</label>
                    <input type="datetime-local" id="date" name="date">
                </div>

                <div class="form">
                    <label for="enddate">End Date and Time</label>
                    <input type="datetime-local" id="enddate" name="enddate">
                </div>

                <div class="editLink">
                    <input class="formButton" type="submit" value="Save">
                </div>

            </form>
        </div>

        <div class="right">
            <h2>Your vacations</h2>
            <div class="allVacations">
                <ul class="grouping">
                    <?php foreach ($vacations as $vacation): ?>
                        <?php
                        $accepted = $vacation['accepted'];
                        $status = ($accepted === null) ? "in treatment" : ($accepted ? "accepted" : "rejected");
                        $class = ($accepted === null) ? "in-treatment" : ($accepted ? "accepted" : "rejected");
                        ?>
                        <li class="<?php echo $class; ?>">
                            <?php echo $vacation['reason']; ?> from <?php echo $vacation['date']; ?> to <?php echo $vacation['enddate']; ?> - <?php echo $status; ?>
                            <?php if ($status === "rejected"): ?>
                                - Reason: <?php echo $vacation['rejectreason']; ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        </div>

    </div>
</div>
</body>
</html>
