<?php

include_once(__DIR__ . "/Db.php");

class AllWorkers {
    private $conn;

    public function __construct() {
        $this->conn = new PDO('mysql:host=ID436917_littlesun.db.webhosting.be;dbname=ID436917_littlesun', 'ID436917_littlesun', 'LittleSun5');
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
                    t.TaskType,
                    w.start,
                    w.end,
                    w.day
                  FROM 
                    account a
                  LEFT JOIN 
                    assignedtasks at ON a.id = at.user_id
                  LEFT JOIN 
                    tasks t ON at.tasktype_id = t.id
                  LEFT JOIN
                    workhours w ON a.id = w.user_id"; // assuming the user_id in workhours table matches with the account id
                
        
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
                    "TaskType" => $row["TaskType"],
                    "start" => $row["start"],
                    "end" => $row["end"],
                    "day" => $row["day"]
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



