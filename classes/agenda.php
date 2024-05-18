<?php
class Agenda {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function fetchAssignedTasks() {
        $query = "SELECT a.id, a.username, a.email, t.TaskType, at.tasktype_id, at.user_id 
                  FROM account a 
                  JOIN assignedtasks at ON a.id = at.user_id 
                  JOIN tasks t ON at.tasktype_id = t.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchAgendaItems($username, $isManager, $date) {
        if ($isManager) {
            $query = "SELECT * FROM agenda WHERE day = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('s', $date);
            $stmt->execute();
        } else {
            $query = "SELECT * FROM agenda WHERE username = ? AND day = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('ss', $username, $date);
            $stmt->execute();
        }
        $result = $stmt->get_result();
        $agenda_items = $result->fetch_all(MYSQLI_ASSOC);

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

    public function setDefaultAcceptStatus($date) {
        $query = "UPDATE agenda SET accept = 1 WHERE day = ? AND accept IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $date);
        $stmt->execute();
    }
}
?>
