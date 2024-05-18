<?php
class TaskManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function fetchAssignedTasks() {
        $query = "SELECT a.id, a.username, a.email, t.TaskType, at.tasktype_id, at.user_id 
                  FROM account a 
                  JOIN assignedtasks at ON a.id = at.user_id 
                  JOIN tasks t ON at.tasktype_id = t.id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertAgendaItem($user_id, $username, $task, $startinghour, $endhour, $day) {
        $query = "INSERT INTO agenda (user_id, username, task, startinghour, endhour, day) VALUES (:user_id, :username, :task, :startinghour, :endhour, :day)";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':task', $task);
        $stmt->bindParam(':startinghour', $startinghour);
        $stmt->bindParam(':endhour', $endhour);
        $stmt->bindParam(':day', $day);
        $stmt->execute();
    }
}

