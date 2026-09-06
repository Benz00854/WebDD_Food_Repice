<?php
// classes/Request.php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Logger.php';

class Request {
    private $conn;
    private $logger;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
        $this->logger = new Logger($this->conn);
    }

    private function generateRequestNumber() {
        $year = date('y') + 43; // ปี พ.ศ. 2 หลัก
        $term = '1'; // สมมติภาคเรียน
        
        $stmt = $this->conn->query("SELECT COUNT(*) FROM special_course_requests");
        $count = $stmt->fetchColumn() + 1;
        
        return sprintf("SCR-%s%s-%04d", $year, $term, $count);
    }

    public function create($data) {
        try {
            $this->conn->beginTransaction();

            $requestNumber = $this->generateRequestNumber();

            $stmt = $this->conn->prepare("
                INSERT INTO special_course_requests (request_number, student_id, course_code, reason, status_id) 
                VALUES (:req_no, :stu_id, :course_code, :reason, 1)
            ");
            
            $stmt->execute([
                ':req_no' => $requestNumber,
                ':stu_id' => $data['student_id'],
                ':course_code' => $data['course_code'],
                ':reason' => $data['reason']
            ]);

            $requestId = $this->conn->lastInsertId();

            // บันทึก Audit trail
            $this->logger->log($requestId, 'CREATE', "สร้างคำร้องรหัส " . $requestNumber, $data['student_id']);

            $this->conn->commit();
            
            return ['success' => true, 'request_number' => $requestNumber];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>
