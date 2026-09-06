<?php
// classes/Logger.php
class Logger {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function log($requestId, $actionType, $description, $performedBy = 'System') {
        $stmt = $this->conn->prepare("
            INSERT INTO request_logs (request_id, action_type, action_description, performed_by) 
            VALUES (:request_id, :action_type, :action_description, :performed_by)
        ");
        $stmt->execute([
            ':request_id' => $requestId,
            ':action_type' => $actionType,
            ':action_description' => $description,
            ':performed_by' => $performedBy
        ]);
    }
}
?>
