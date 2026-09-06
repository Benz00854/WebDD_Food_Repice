<?php
// api/check_course.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Course.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$courseCode = $_GET['code'] ?? '';
if (empty($courseCode)) {
    echo json_encode(['success' => false, 'message' => 'Missing course code']);
    exit;
}

$courseModel = new Course();
$course = $courseModel->findByCode($courseCode);

if ($course) {
    echo json_encode(['success' => true, 'data' => $course]);
} else {
    echo json_encode(['success' => false, 'message' => 'Course not found']);
}
?>
