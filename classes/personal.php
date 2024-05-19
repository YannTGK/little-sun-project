<?php

class personal {
    private string $username;
    private string $email;
    private string $password;
    private string $profilePicture;
    private string $role;
    private string $hubname; 

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

    public static function getById($id) {
        $conn = new PDO('mysql:host=localhost;dbname=littlesun', "root", "root");
        $statement = $conn->prepare("SELECT * FROM account WHERE id = :id");
        $statement->bindValue(":id", $id);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            throw new Exception("personal not found.");
        }
        $personal = new personal();
        $personal->setName($result['username']);
        $personal->setEmail($result['email']);
        $personal->setPassword($result['password']);
        $personal->setProfilePicture($result['profilePicture']);
        $personal->setRole($result['role']);
        $personal->setHubname($result['hubname']);
        return $personal;
    }

    public static function getByUsername($username) {
        $conn = new PDO('mysql:host=localhost;dbname=littlesun', "root", "root");
        $statement = $conn->prepare("SELECT * FROM account WHERE username = :username");
        $statement->bindValue(":username", $username);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            return null; 
        }
        $personal = new personal();
        $personal->setName($result['username']);
        $personal->setEmail($result['email']);
        $personal->setPassword($result['password']);
        $personal->setProfilePicture($result['profilePicture']);
        $personal->setRole($result['role']);
        $personal->setHubname($result['hubname']);
        return $personal;
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


    public function setHubname($hubname) {
        $this->hubname = $hubname;
    }


    public function getHubname() {
        return $this->hubname;
    }


    public function save(){
        $conn = new PDO ('mysql:host=localhost;dbname=littlesun', "root", "root");

        $existingUser = self::getByUsername($this->username);
        if ($existingUser) {

            $statement = $conn->prepare("UPDATE account SET email = :email, password = :password, profilePicture = :profilePicture, role = :role, hubname = :hubname WHERE username = :username");
        } else {

            $statement = $conn->prepare("INSERT INTO account (username, email, password, profilePicture, role, hubname) VALUES (:username, :email, :password, :profilePicture, :role, :hubname)");
        }
        $statement->bindValue(":username", $this->username);
        $statement->bindValue(":email", $this->email);
        $statement->bindValue(":password", $this->password);
        $statement->bindValue(":profilePicture", $this->profilePicture);
        $statement->bindValue(":role", $this->role);
        $statement->bindValue(":hubname", $this->hubname);
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
        $statement->bindValue(":id", $personalId);
        return $statement->execute();
    }
}