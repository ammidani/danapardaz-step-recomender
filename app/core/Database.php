<?php
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        if (!file_exists(__DIR__ . '/config.php')) {
            // If config doesn't exist, it means setup is not complete.
            // A better check might be needed for public pages.
            if (basename($_SERVER['PHP_SELF']) != 'setup.php') {
                 header('Location: setup.php');
                 exit;
            }
            return;
        }

        require_once __DIR__ . '/config.php';

        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
        $this->connection->set_charset("utf8mb4");
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    // Example of a safe query method
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) {
            die("Prepare failed: (" . $this->connection->errno . ") " . $this->connection->error);
        }

        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // Assuming all params are strings
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt->get_result();
    }
}
