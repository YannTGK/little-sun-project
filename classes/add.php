<?php

class Hub {
    private string $hubname;
    private string $hublocation;

    public function setHubname($pHubname){
        if(empty($pHubname)){
            throw new Exception("hubname cannot be empty");
        }  else {
            $this->hubname = $pHubname;
        }
    }

    public function getHubname() {
        return $this->hubname;
    }

    public function setHublocation($pHublocation){
        if(empty($pHublocation)){
            throw new Exception("Location cannot be empty");
        }  else {
            $this->hublocation = $pHublocation;
        }
    }

    public function getHublocation() {
        return $this->hublocation;
    }

    public function save(){
        $conn = new PDO('mysql:host=ID436917_littlesun.db.webhosting.be;dbname=ID436917_littlesun', 'ID436917_littlesun', 'LittleSun5');
        $statement = $conn->prepare("INSERT INTO hub (hubname, hublocation) VALUES (:hubname, :hublocation)");
        $statement->bindValue("hubname", $this->hubname);
        $statement->bindValue("hublocation", $this->hublocation);
        return $statement->execute();
    }

    public static function getAll(){
        $conn = new PDO('mysql:host=ID436917_littlesun.db.webhosting.be;dbname=ID436917_littlesun', 'ID436917_littlesun', 'LittleSun5');
        $statement = $conn->prepare("SELECT * FROM hub");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public static function deleteById($hubId) {
        $conn = new PDO('mysql:host=ID436917_littlesun.db.webhosting.be;dbname=ID436917_littlesun', 'ID436917_littlesun', 'LittleSun5');
        $statement = $conn->prepare("DELETE FROM hub WHERE id = :id");
        $statement->bindValue("id", $hubId);
        return $statement->execute();
    }
}
?>