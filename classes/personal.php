<?php

class Personal {
    private string $username;
    private string $email;
    private string $password;
    private string $profilePicture;
    private string $role;
    private string $hubname; // Voeg het attribuut hubname toe

    public function setName($username){
        if(empty($username)){
            throw new Exception("Name cannot be empty");
        }  else {
            $this->username = $username;
        }
    }

    public function getName() {
        return $this->username;
    }

    public function setEmail($email){
        if(empty($email)){
            throw new Exception("Email cannot be empty");
        }  else {
            $this->email = $email;
        }
    }

    public function getEmail() {
        return $this->email;
    }

    public static function updatePasswordById($id, $hashedPassword) {
        $conn = new PDO ('mysql:host=localhost;dbname=littlesun', "root", "root");
        $statement = $conn->prepare("UPDATE account SET password = :password WHERE id = :id");
        $statement->bindValue(":password", $hashedPassword);
        $statement->bindValue(":id", $id);
        return $statement->execute();
    }

    public function setPassword($password){
        if(empty($password)){
            throw new Exception("Password cannot be empty");
        }  else {
            $this->password = $password;
        }
    }

    public function getPassword() {
        return $this->password;
    }

    public function setProfilePicture($profilePicture){
        $this->profilePicture = $profilePicture;
    }

    public function getProfilePicture() {
        return $this->profilePicture;
    }

    public function setRole($role) {
        $this->role = $role;
    }

    public function getRole() {
        return $this->role;
    }

    // Methode om de hubnaam in te stellen
    public function setHubname($hubname) {
        $this->hubname = $hubname;
    }

    // Methode om de hubnaam op te halen
    public function getHubname() {
        return $this->hubname;
    }

    public function save(){
        $conn = new PDO ('mysql:host=localhost;dbname=littlesun', "root", "root");
        $statement = $conn->prepare("INSERT INTO account (username, email, password, profilePicture, role, hubname) VALUES (:name, :email, :password, :profilePicture, :role, :hubname)");
        $statement->bindValue("name", $this->username);
        $statement->bindValue("email", $this->email);
        $statement->bindValue("password", $this->password);
        $statement->bindValue("profilePicture", $this->profilePicture);
        $statement->bindValue("role", $this->role);
        $statement->bindValue("hubname", $this->hubname); // Voeg hubname toe aan de query
        return $statement->execute();
    }

    public static function getAll(){
        $conn = new PDO ('mysql:host=localhost;dbname=littlesun', "root", "root");
        $statement = $conn->prepare("SELECT * FROM account WHERE role != 'admin'");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public static function deleteById($personalId) {
        $conn = new PDO ('mysql:host=localhost;dbname=littlesun', "root", "root");
        $statement = $conn->prepare("DELETE FROM account WHERE id = :id");
        $statement->bindValue("id", $personalId);
        return $statement->execute();
    }
}

?>
