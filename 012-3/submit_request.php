<?php
require_once __DIR__ . '/classes/Request.php';
require_once __DIR__ . '/classes/Student.php';
require_once __DIR__ . '/classes/Course.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/includes/functions.php';
    verifyCsrfToken($_POST['csrf_token'] ?? '');

    $data = [
        'student_id' => sanitizeInput($_POST['student_id'] ?? ''),
        'course_code' => sanitizeInput($_POST['course_code'] ?? ''),
        'reason' => sanitizeInput($_POST['reason'] ?? '')
    ];

    $errors = validateRequestForm($data);

    if (!empty($errors)) {
        $error_msg = implode('<br>', $errors);
    } else {
        $studentModel = new Student();
        if (!$studentModel->isEligibleForSpecialCourse($data['student_id'])) {
            $error_msg = "นักศึกษาไม่มีสิทธิ์ยื่นคำร้อง (ต้องเป็นนักศึกษาปี 4 หรือภาคค่ำ/เสาร์อาทิตย์)";
        } else {
            $courseModel = new Course();
            // สมมติเทอม 1 ปีการศึกษา 2567
            if ($courseModel->isInRegularSchedule($data['student_id'], $data['course_code'], 1, 2567)) {
                $error_msg = "รายวิชานี้มีเปิดสอนในตารางปกติแล้ว ไม่สามารถยื่นคำร้องได้";
            } else {
                $requestModel = new Request();
                $result = $requestModel->create($data);

                if ($result['success']) {
                    $success_msg = 'ยื่นคำร้องสำเร็จ! หมายเลขคำร้องของคุณคือ: <strong>' . $result['request_number'] . '</strong>';
                } else {
                    $error_msg = 'เกิดข้อผิดพลาด: ' . $result['error'];
                }
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="header-section">
    <h1>ยื่นคำร้องขอเปิดหมู่เรียนพิเศษ</h1>
    <p>กรุณากรอกข้อมูลให้ครบถ้วนเพื่อประกอบการพิจารณา</p>
</div>

<div class="form-container">
    <?php if ($success_msg): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-check-circle"></i> <?php echo $success_msg; ?>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" class="btn btn-blue">กลับหน้าหลัก</a>
        </div>
    <?php else: ?>
        
        <?php if ($error_msg): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form action="submit_request.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <div class="form-group">
                <label for="student_id">รหัสนักศึกษา</label>
                <input type="text" id="student_id" name="student_id" class="form-control" placeholder="เช่น 64xxxxxx" required>
            </div>
            
            <div class="form-group">
                <label for="course_code">รหัสวิชาที่ต้องการขอเปิด</label>
                <input type="text" id="course_code" name="course_code" class="form-control" placeholder="เช่น INT101" required>
            </div>
            
            <div class="form-group">
                <label for="reason">เหตุผลที่ขอเปิดหมู่เรียนพิเศษ</label>
                <textarea id="reason" name="reason" class="form-control" rows="4" placeholder="โปรดระบุเหตุผลความจำเป็น" required></textarea>
            </div>
            
            <button type="submit" class="btn btn-blue" style="width: 100%; justify-content: center;">
                <i class="fa-solid fa-paper-plane"></i> ยื่นคำร้อง
            </button>
        </form>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
