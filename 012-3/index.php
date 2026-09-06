<?php
require_once __DIR__ . '/includes/header.php';
?>

<div class="header-section">
    <h1>ระบบขอเปิดหมู่เรียนพิเศษ</h1>
    <p>ยื่นคำร้องและติดตามสถานะได้ที่นี่</p>
</div>

<div class="cards-container">
    <div class="card">
        <div class="icon-container blue-icon">
            <i class="fa-solid fa-paper-plane"></i>
        </div>
        <h2>ยื่นคำร้อง</h2>
        <p>ขอเปิดหมู่เรียนพิเศษสำหรับรายวิชาที่ไม่มีในตารางปกติ</p>
        <a href="submit_request.php" class="btn btn-blue"><i class="fa-solid fa-plus"></i> ยื่นคำร้องใหม่</a>
    </div>

    <div class="card">
        <div class="icon-container green-icon">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <h2>ติดตามสถานะ</h2>
        <p>ตรวจสอบผลการพิจารณาคำร้องด้วยหมายเลขคำร้อง</p>
        <a href="#" class="btn btn-green"><i class="fa-solid fa-magnifying-glass"></i> ติดตามคำร้อง</a>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
