<?php
// config/database.php
// สำหรับใช้งานจริงเปลี่ยนเป็นค่าของ MySQL ของคุณ
define('DB_HOST', 'localhost');
define('DB_NAME', 'special_course_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// สำหรับทดสอบบนเครื่อง Local ด้วย SQLite ก่อน (ตามที่คุยกันไว้)
define('USE_SQLITE', true);
define('SQLITE_PATH', __DIR__ . '/../database.sqlite');
?>
