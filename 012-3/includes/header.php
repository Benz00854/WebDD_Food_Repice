<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบขอเปิดหมู่เรียนพิเศษ</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="index.php" style="color:white; text-decoration:none;"><i class="fa-solid fa-graduation-cap"></i> ขอเปิดหมู่เรียนพิเศษ</a>
            </div>
            <div class="nav-links">
                <a href="submit_request.php"><i class="fa-solid fa-plus"></i> ยื่นคำร้อง</a>
                <a href="#"><i class="fa-solid fa-magnifying-glass"></i> ติดตามสถานะ</a>
            </div>
        </div>
    </nav>
    <main class="main-content">
