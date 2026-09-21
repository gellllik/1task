<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'bd-task';
    private $username = 'root';
    private $password = 'введите_свой_пароль';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8",
                $this->username, $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Ошибка подключения: ' . $e->getMessage());
        }
        return $this->conn;
    }
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $database = new Database();
        $pdo = $database->getConnection();
    }
    return $pdo;
}