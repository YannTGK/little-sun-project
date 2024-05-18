<?php
class UserSession {
    public $userID;
    public $isAdmin;
    public $isManager;

    public function __construct() {
        session_start();
        if(!isset($_SESSION['loggedin'])) {
            header("Location: login.php");
            exit;
        }
        $this->userID = $_SESSION['id'];
        $this->isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        $this->isManager = isset($_SESSION['role']) && $_SESSION['role'] === 'Manager';
    }

    public function getUserID() {
        return $this->userID;
    }

    public function isAdmin() {
        return $this->isAdmin;
    }

    public function isManager() {
        return $this->isManager;
    }
}
