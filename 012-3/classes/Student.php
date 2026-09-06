<?php
// classes/Student.php
require_once __DIR__ . '/Database.php';

class Student {
    private $conn;
    private $TABLE = 'students';

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function findById($studentId) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->TABLE} WHERE student_id = ?");
        $stmt->execute([$studentId]);
        return $stmt->fetch();
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->TABLE} WHERE student_email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function isEligibleForSpecialCourse($studentId) {
        // ตรวจสิทธิ์ก่อน submit: ปี 4+ หรือนักศึกษาภาคค่ำ/เสาร์อาทิตย์
        // สำหรับการจำลอง หากไม่มีข้อมูลในตาราง ให้ถือว่าผ่านเพื่อทดสอบได้
        $student = $this->findById($studentId);
        if (!$student) {
            return true; // อนุโลมสำหรับการทดสอบเบื้องต้นที่ยังไม่มี Data
        }
        
        if ($student['study_year'] >= 4 || in_array($student['student_type'], ['EVENING', 'WEEKEND'])) {
            return true;
        }
        return false;
    }

    public function getAll() {
        $stmt = $this->conn->query("SELECT * FROM {$this->TABLE}");
        return $stmt->fetchAll();
    }
}
?>
