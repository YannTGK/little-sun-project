<?php

class task {
    private string $task;


    public function setTask($pTask){
        if(empty($pTask)){
            throw new Exception("Tasks cannot be empty");
        }  else {
            $this->task = $pTask;
        }
    }

    public function getTask() {
        return $this->task;
    }

    public function save(){
        $conn = new PDO('mysql:host=ID436917_littlesun.db.webhosting.be;dbname=ID436917_littlesun', 'ID436917_littlesun', 'LittleSun5');
        $statement = $conn->prepare("INSERT INTO tasks (TaskType) VALUES (:task)");
        $statement->bindValue("task", $this->task);
        return $statement->execute();
    }

    public static function getAll(){
        $conn = new PDO('mysql:host=ID436917_littlesun.db.webhosting.be;dbname=ID436917_littlesun', 'ID436917_littlesun', 'LittleSun5');
        $statement = $conn->prepare("SELECT * FROM tasks");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public static function deleteById($taskId) {
        $conn = new PDO('mysql:host=ID436917_littlesun.db.webhosting.be;dbname=ID436917_littlesun', 'ID436917_littlesun', 'LittleSun5');
        $statement = $conn->prepare("DELETE FROM tasks WHERE id = :id");
        $statement->bindValue("id", $taskId);
        return $statement->execute();
    }
}
?>