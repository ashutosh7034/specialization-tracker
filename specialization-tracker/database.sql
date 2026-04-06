-- Specialization Tracker System SQL
-- Import this file in phpMyAdmin

CREATE DATABASE IF NOT EXISTS specialization_tracker;
USE specialization_tracker;

-- 1. departments
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- 2. users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'coordinator', 'mentor', 'student') NOT NULL,
    department_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

-- 3. students
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    cgpa DECIMAL(3,2) DEFAULT 0.00,
    kt_status ENUM('yes', 'no') DEFAULT 'no',
    semester INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- 4. mentors
CREATE TABLE IF NOT EXISTS mentors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- mentor_student_map (for coordinator assignment)
CREATE TABLE IF NOT EXISTS mentor_student_map (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mentor_id INT NOT NULL,
    student_id INT NOT NULL,
    UNIQUE KEY unique_map (mentor_id, student_id),
    FOREIGN KEY (mentor_id) REFERENCES mentors(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- 5. specializations
CREATE TABLE IF NOT EXISTS specializations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    type ENUM('honours', 'minor', 'honours_with_research') NOT NULL
);

-- 6. student_specializations
CREATE TABLE IF NOT EXISTS student_specializations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    specialization_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'offline_exam_required') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (specialization_id) REFERENCES specializations(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- 7. certificates
CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- 8. courses
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    type ENUM('honours', 'minor', 'honours_with_research') NOT NULL,
    credits INT NOT NULL,
    semester INT NOT NULL
);

-- Dynamic specialization rules table for super admin
CREATE TABLE IF NOT EXISTS specialization_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rule_key VARCHAR(100) NOT NULL UNIQUE,
    rule_value VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT ''
);

-- Seed data
INSERT INTO departments (name) VALUES
('Computer Engineering'),
('Information Technology'),
('Electronics')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Password used for all seeded users below: 123456
-- Uses MD5 for demo seeds; login.php supports both MD5 and password_hash values.
SET @pass_hash = MD5('123456');

INSERT INTO users (name, email, password, role, department_id) VALUES
('Super Admin', 'superadmin@example.com', @pass_hash, 'super_admin', 1),
('Main Admin', 'admin@example.com', @pass_hash, 'admin', 1),
('Dept Coordinator', 'coordinator@example.com', @pass_hash, 'coordinator', 1),
('Mentor One', 'mentor@example.com', @pass_hash, 'mentor', 1),
('Student One', 'student@example.com', @pass_hash, 'student', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO mentors (user_id)
SELECT id FROM users WHERE role = 'mentor'
ON DUPLICATE KEY UPDATE user_id = user_id;

INSERT INTO students (user_id, cgpa, kt_status, semester)
SELECT id, 8.10, 'no', 7 FROM users WHERE role = 'student'
ON DUPLICATE KEY UPDATE cgpa = VALUES(cgpa), kt_status = VALUES(kt_status), semester = VALUES(semester);

INSERT INTO specializations (name, type) VALUES
('Honours in AI & ML', 'honours'),
('Minor in Data Science', 'minor'),
('Honours with Research in IoT', 'honours_with_research');

INSERT INTO courses (name, type, credits, semester) VALUES
('Advanced Machine Learning', 'honours', 4, 5),
('Deep Learning', 'honours', 4, 6),
('Data Analytics Foundation', 'minor', 3, 4),
('Applied Statistics', 'minor', 3, 5),
('Research Methodology', 'honours_with_research', 4, 7),
('Publication and Thesis', 'honours_with_research', 4, 8);

INSERT INTO specialization_rules (rule_key, rule_value, description) VALUES
('honours_min_semester', '4', 'Minimum semester for Honours'),
('honours_max_semester', '8', 'Maximum semester for Honours'),
('honours_min_cgpa', '7.0', 'Minimum CGPA for Honours'),
('honours_research_min_semester', '7', 'Minimum semester for Honours with Research'),
('honours_research_max_semester', '8', 'Maximum semester for Honours with Research'),
('honours_research_min_cgpa', '7.5', 'Minimum CGPA for Honours with Research');