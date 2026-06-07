DROP DATABASE IF EXISTS internship_management;
CREATE DATABASE internship_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE internship_management;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS evaluations;
DROP TABLE IF EXISTS internship_registrations;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS internship_periods;
DROP TABLE IF EXISTS companies;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS majors;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================
-- USERS
-- =========================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    role ENUM('student', 'company', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- MAJORS
-- =========================
CREATE TABLE majors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL
);

-- =========================
-- STUDENTS
-- =========================
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    student_code VARCHAR(50) NOT NULL UNIQUE,
    major_id INT NOT NULL,
    class_name VARCHAR(50) DEFAULT NULL,
    gpa DECIMAL(3,2) DEFAULT NULL,
    CONSTRAINT fk_students_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_students_major
        FOREIGN KEY (major_id) REFERENCES majors(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

-- =========================
-- COMPANIES
-- =========================
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    company_name VARCHAR(150) NOT NULL,
    tax_code VARCHAR(50) NOT NULL UNIQUE,
    address VARCHAR(255) DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    industry_type VARCHAR(100) DEFAULT NULL,
    CONSTRAINT fk_companies_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- =========================
-- INTERNSHIP PERIODS
-- =========================
CREATE TABLE internship_periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('open', 'closed') DEFAULT 'open'
);

-- =========================
-- JOBS
-- =========================
CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    period_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT NOT NULL,
    slots INT NOT NULL DEFAULT 1,
    deadline DATE NOT NULL,
    status ENUM('open', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_jobs_company
        FOREIGN KEY (company_id) REFERENCES companies(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_jobs_period
        FOREIGN KEY (period_id) REFERENCES internship_periods(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

-- =========================
-- APPLICATIONS
-- =========================
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    job_id INT NOT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cv_url VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_approved TINYINT(1) DEFAULT 0,
    company_approved TINYINT(1) DEFAULT 0,
    CONSTRAINT uq_student_job UNIQUE (student_id, job_id),
    CONSTRAINT fk_applications_student
        FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_applications_job
        FOREIGN KEY (job_id) REFERENCES jobs(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- =========================
-- MESSAGES
-- =========================
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_messages_sender
        FOREIGN KEY (sender_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_messages_receiver
        FOREIGN KEY (receiver_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- =========================
-- INTERNSHIP REGISTRATIONS
-- =========================
CREATE TABLE internship_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL UNIQUE,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    status ENUM('ongoing', 'completed', 'cancelled') DEFAULT 'ongoing',
    CONSTRAINT fk_registrations_application
        FOREIGN KEY (application_id) REFERENCES applications(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- =========================
-- EVALUATIONS
-- =========================
CREATE TABLE evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,
    evaluator_role ENUM('company', 'admin') DEFAULT 'company',
    score DECIMAL(4,2) NOT NULL,
    feedback TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_evaluations_registration
        FOREIGN KEY (registration_id) REFERENCES internship_registrations(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- =========================
-- REPORTS
-- =========================
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,
    content TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reports_registration
        FOREIGN KEY (registration_id) REFERENCES internship_registrations(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);