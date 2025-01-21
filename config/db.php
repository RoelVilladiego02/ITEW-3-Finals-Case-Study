<?php

class Database 
{
    private $host = 'localhost';
    private $db = 'ecommerce_db';
    private $user = 'root';
    private $pass = '';
    private $pdo;

    // Constructor automatically initializes the connection
    public function __construct() 
    {
        $this->connect();
    }

    // Method to establish a database connection
    private function connect() 
    {
        try {
            $this->pdo = new PDO("mysql:host={$this->host};dbname={$this->db}", $this->user, $this->pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    // Method to get the PDO instance
    public function getConnection() 
    {
        return $this->pdo;
    }
}

?>
