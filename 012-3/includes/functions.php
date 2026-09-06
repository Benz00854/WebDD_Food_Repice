<?php
// includes/functions.php

function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die("CSRF Token Validation Failed.");
    }
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateRequestForm($data) {
    $errors = [];
    if (empty($data['student_id'])) {
        $errors[] = "กรุณากรอกรหัสนักศึกษา";
    }
    if (empty($data['course_code'])) {
        $errors[] = "กรุณากรอกรหัสวิชา";
    }
    if (empty($data['reason'])) {
        $errors[] = "กรุณากรอกเหตุผล";
    }
    return $errors;
}
?>
