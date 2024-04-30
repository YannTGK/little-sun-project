<?php
class AssignedTask {
    private $username; 
    private $taskType;

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getTaskType() {
        return $this->taskType;
    }

    public function setTaskType($taskType) {
        $this->taskType = $taskType;
    }

    public function save() {
        try {
            $conn = new PDO('mysql:host=localhost;dbname=littlesun', "root", "root");

        
            $findUserStatement = $conn->prepare("SELECT id FROM account WHERE username = :username");
            $findUserStatement->bindParam(':username', $this->username);
            $findUserStatement->execute();
            $userId = $findUserStatement->fetchColumn();

   
            $statement = $conn->prepare("INSERT INTO assignedtasks (user_id, tasktype_id) VALUES (:user_id, :tasktype_id)");
            $statement->bindParam(':user_id', $userId);
            $statement->bindParam(':tasktype_id', $this->taskType);
            $statement->execute();
        } catch (PDOException $e) {
            throw new Exception("Error saving assigned task: " . $e->getMessage());
        }
    }
}
?>
