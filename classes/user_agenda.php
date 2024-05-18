<?php
class User {
    private $isAdmin;
    private $isManager;
    private $username;

    public function __construct() {
        $this->isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        $this->isManager = isset($_SESSION['role']) && $_SESSION['role'] === 'Manager';
        $this->username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
    }

    public function isAdmin() {
        return $this->isAdmin;
    }

    public function isManager() {
        return $this->isManager;
    }

    public function getUsername() {
        return $this->username;
    }
}
?>
