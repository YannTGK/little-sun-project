<?php

class Manager {
    private string $name;
    private string $email;
    private string $password;
    private string $profilePicture;

    public function setName($name){
        if(empty($name)){
            throw new Exception("Name cannot be empty");
        }  else {
            $this->name = $name;
        }
    }

    public function getName() {
        return $this->name;
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

    public function save(){
        $conn = new PDO ('mysql:host=localhost;dbname=littlesun', "root", "root");
        $statement = $conn->prepare("INSERT INTO account (name, email, password, profilePicture) VALUES (:name, :email, :password, :profilePicture)");
        $statement->bindValue("name", $this->name);
        $statement->bindValue("email", $this->email);
        $statement->bindValue("password", $this->password);
        $statement->bindValue("profilePicture", $this->profilePicture);
        return $statement->execute();
    }

    public static function getAll(){
        $conn = new PDO ('mysql:host=localhost;dbname=littlesun', "root", "root");
        $statement = $conn->prepare("SELECT * FROM account");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}
