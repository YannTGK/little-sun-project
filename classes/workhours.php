<?php
class WorkHours {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function startWork($userID, $currentTime) {
        $checkNullQuery = "SELECT COUNT(*) AS count FROM workhours WHERE user_id = $userID AND end IS NULL";
        $nullResult = $this->conn->query($checkNullQuery);
        if ($nullResult && $nullResult->num_rows > 0) {
            $row = $nullResult->fetch_assoc();
            if ($row['count'] > 0) {
                return "You can't start new work hour, end time is not recorded yet.";
            }
        }

        $sql = "INSERT INTO workhours (user_id, start, day) VALUES ($userID, '$currentTime', CURDATE())";
        if ($this->conn->query($sql) !== TRUE) {
            return "Error: " . $this->conn->error;
        }
        return true;
    }

    public function endWork($userID, $currentTime) {
        $sql = "UPDATE workhours SET end = '$currentTime' WHERE user_id = $userID AND day = CURDATE() AND end IS NULL";
        if ($this->conn->query($sql) !== TRUE) {
            return "Error: " . $this->conn->error;
        }
        return true;
    }

    public function getTotalWorkedMinutes($userID) {
        $sql = "SELECT user_id, SUM(TIMESTAMPDIFF(MINUTE, start, end)) AS total_minutes
                FROM workhours
                WHERE DATE(day) = CURDATE()
                GROUP BY user_id";
        $result = $this->conn->query($sql);
        $workedTimes = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $workedTimes[$row['user_id']] = $row['total_minutes'];
            }
        }
        return $workedTimes;
    }
}
