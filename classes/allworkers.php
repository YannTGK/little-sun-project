<?php

include_once(__DIR__ . "/Db.php");

class AllWorkers {
    private $conn;

    public function __construct() {
        $this->conn = new PDO('mysql:host=localhost;dbname=littlesun', "root", "root");
        if (!$this->conn) {
            die("Connection failed: " . $this->conn->errorInfo());
        }
    }

    public function fetchWorkers() {
        $query = "SELECT 
                    a.id AS user_id,
                    a.username,
                    a.email,
                    a.profilepicture,
                    a.password,
                    a.role,
                    a.hubname,
                    t.TaskType
                  FROM 
                    account a
                  LEFT JOIN 
                    assignedtasks at ON a.id = at.user_id
                  LEFT JOIN 
                    tasks t ON at.tasktype_id = t.id";

        $result = $this->conn->query($query);

        if ($result) {
            $workers = array(); 
            foreach($result as $row) {
                $worker = array(
                    "user_id" => $row["user_id"],
                    "username" => $row["username"],
                    "email" => $row["email"],
                    "profilepicture" => $row["profilepicture"],
                    "role" => $row["role"],
                    "hubname" => $row["hubname"],
                    "TaskType" => $row["TaskType"]
                );
                $workers[] = $worker;
            }
            return $workers; 
        } else {
            return array(); 
        }
    }

    public function __destruct() {
        $this->conn = null;
    }
}

?>
