<?php
// classes/Database.php
require_once __DIR__ . '/../config/database.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            if (USE_SQLITE) {
                $this->conn = new PDO("sqlite:" . SQLITE_PATH);
            } else {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $this->conn = new PDO($dsn, DB_USER, DB_PASS);
            }
            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            if (USE_SQLITE) {
                $this->initSQLiteTables();
            }
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    // ป้องกันการ clone
    private function __clone() {}

    // ป้องกันการ unserialize
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    private function initSQLiteTables() {
        // สร้างตารางเบื้องต้นจำลองตาม ERD
        $queries = [
            "CREATE TABLE IF NOT EXISTS request_status (
                status_id INTEGER PRIMARY KEY,
                status_code TEXT UNIQUE,
                status_name_th TEXT
            )",
            "CREATE TABLE IF NOT EXISTS special_course_requests (
                request_id INTEGER PRIMARY KEY AUTOINCREMENT,
                request_number TEXT UNIQUE NOT NULL,
                student_id TEXT NOT NULL,
                course_code TEXT NOT NULL,
                reason TEXT NOT NULL,
                status_id INTEGER DEFAULT 1,
                request_date DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS request_logs (
                log_id INTEGER PRIMARY KEY AUTOINCREMENT,
                request_id INTEGER,
                action_type TEXT,
                action_description TEXT,
                performed_by TEXT,
                performed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )"
        ];
        
        foreach ($queries as $query) {
            $this->conn->exec($query);
        }

        // Insert default statuses if empty
        $stmt = $this->conn->query("SELECT COUNT(*) FROM request_status");
        if ($stmt->fetchColumn() == 0) {
            $this->conn->exec("INSERT INTO request_status (status_id, status_code, status_name_th) VALUES 
                (1, 'PENDING', 'รอดำเนินการ'),
                (2, 'REVIEWING', 'กำลังตรวจสอบ'),
                (3, 'APPROVED', 'อนุมัติ'),
                (4, 'REJECTED', 'ไม่อนุมัติ'),
                (5, 'CANCELLED', 'ยกเลิก')
            ");
        }
    }
}
?>
