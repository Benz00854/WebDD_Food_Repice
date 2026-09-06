-- database_schema.sql (MySQL)
-- โครงสร้างฐานข้อมูลสำหรับระบบขอเปิดหมู่เรียนพิเศษตาม ERD

CREATE DATABASE IF NOT EXISTS special_course_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE special_course_db;

CREATE TABLE students (
    student_id VARCHAR(20) PRIMARY KEY,
    student_prefix VARCHAR(50),
    student_firstname VARCHAR(100),
    student_lastname VARCHAR(100),
    student_email VARCHAR(150) UNIQUE,
    student_phone VARCHAR(20),
    faculty VARCHAR(100),
    department VARCHAR(100),
    major VARCHAR(100),
    study_year TINYINT,
    student_type ENUM('REGULAR', 'EVENING', 'WEEKEND'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE instructors (
    instructor_id INT PRIMARY KEY AUTO_INCREMENT,
    instructor_prefix VARCHAR(50),
    instructor_firstname VARCHAR(100),
    instructor_lastname VARCHAR(100),
    instructor_email VARCHAR(150) UNIQUE,
    department VARCHAR(100),
    position VARCHAR(100),
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE'
);

CREATE TABLE courses (
    course_code VARCHAR(20) PRIMARY KEY,
    course_name_th VARCHAR(255),
    course_name_en VARCHAR(255),
    credit_theory DECIMAL(3,1),
    credit_practice DECIMAL(3,1),
    credit_self DECIMAL(3,1),
    department VARCHAR(100),
    faculty VARCHAR(100),
    course_level ENUM('BACHELOR', 'MASTER', 'DOCTOR'),
    status ENUM('ACTIVE', 'INACTIVE') DEFAULT 'ACTIVE'
);

CREATE TABLE request_status (
    status_id TINYINT PRIMARY KEY,
    status_code VARCHAR(50) UNIQUE,
    status_name_th VARCHAR(100),
    status_color VARCHAR(20),
    sort_order TINYINT
);

INSERT INTO request_status (status_id, status_code, status_name_th, status_color, sort_order) VALUES
(1, 'PENDING', 'รอดำเนินการ', '#f9a825', 1),
(2, 'REVIEWING', 'กำลังตรวจสอบ', '#00838f', 2),
(3, 'APPROVED', 'อนุมัติ', '#2e7d32', 3),
(4, 'REJECTED', 'ไม่อนุมัติ', '#c62828', 4),
(5, 'CANCELLED', 'ยกเลิก', '#546e7a', 5);

CREATE TABLE special_course_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    request_number VARCHAR(50) UNIQUE NOT NULL,
    student_id VARCHAR(20),
    course_code VARCHAR(20),
    instructor_id INT NULL,
    status_id TINYINT DEFAULT 1,
    semester TINYINT,
    academic_year SMALLINT,
    reason TEXT,
    expected_students SMALLINT,
    is_in_regular_schedule TINYINT(1) DEFAULT 0,
    request_date DATE DEFAULT (CURRENT_DATE),
    review_date DATETIME NULL,
    reviewed_by VARCHAR(100) NULL,
    review_notes TEXT NULL,
    approval_date DATETIME NULL,
    section_number VARCHAR(20) NULL,
    
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON UPDATE CASCADE,
    FOREIGN KEY (course_code) REFERENCES courses(course_code) ON UPDATE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES instructors(instructor_id) ON DELETE SET NULL,
    FOREIGN KEY (status_id) REFERENCES request_status(status_id)
);

CREATE TABLE request_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    request_id INT,
    action_type VARCHAR(50),
    action_description TEXT,
    performed_by VARCHAR(100),
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    
    FOREIGN KEY (request_id) REFERENCES special_course_requests(request_id) ON DELETE CASCADE
);

CREATE VIEW vw_special_course_requests AS
SELECT 
    r.*,
    s.student_firstname, s.student_lastname, s.faculty, s.major,
    c.course_name_th,
    i.instructor_firstname, i.instructor_lastname,
    st.status_name_th, st.status_color
FROM special_course_requests r
LEFT JOIN students s ON r.student_id = s.student_id
LEFT JOIN courses c ON r.course_code = c.course_code
LEFT JOIN instructors i ON r.instructor_id = i.instructor_id
LEFT JOIN request_status st ON r.status_id = st.status_id;
