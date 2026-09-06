<?php
// classes/Course.php
require_once __DIR__ . '/Database.php';

class Course {
    private $conn;
    private $TABLE = 'courses';

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function findByCode($courseCode) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->TABLE} WHERE course_code = ?");
        $stmt->execute([$courseCode]);
        return $stmt->fetch();
    }

    public function searchByName($keyword) {
        $searchTerm = "%{$keyword}%";
        $stmt = $this->conn->prepare("SELECT * FROM {$this->TABLE} WHERE course_name_th LIKE ? OR course_name_en LIKE ?");
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    public function isInRegularSchedule($studentId, $courseCode, $semester, $year) {
        // ในระบบจริงจะไปเช็คกับตารางเรียนปกติ (enrollments)
        // สำหรับการจำลอง ถือว่ายังไม่มีในตารางปกติ
        return false; 
    }

    public function getAll() {
        $stmt = $this->conn->query("SELECT * FROM {$this->TABLE}");
        return $stmt->fetchAll();
    }
}
?>
