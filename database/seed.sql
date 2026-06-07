USE internship_management;

-- =========================
-- USERS
-- Password tất cả: 123456
-- Hash này là hash bcrypt của 123456
-- =========================
INSERT INTO users (email, password, full_name, phone, role) VALUES
('admin@uniworks.com', '$2y$10$wH7nN9y1d3hZlM0J8l2Qx.Mu9QO4QyqM4I0mQw5L7tQ0mLqg7mD3K', 'System Admin', '0900000000', 'admin'),
('student1@gmail.com', '$2y$10$wH7nN9y1d3hZlM0J8l2Qx.Mu9QO4QyqM4I0mQw5L7tQ0mLqg7mD3K', 'Tuấn', '0911111111', 'student'),
('company1@gmail.com', '$2y$10$wH7nN9y1d3hZlM0J8l2Qx.Mu9QO4QyqM4I0mQw5L7tQ0mLqg7mD3K', 'Tú Anh', '0922222222', 'company');

-- =========================
-- MAJORS
-- =========================
INSERT INTO majors (name, description) VALUES
('Information Systems', 'Information Systems Major'),
('Computer Science', 'Computer Science Major'),
('Business Administration', 'Business Administration Major');

-- =========================
-- STUDENTS
-- user_id = 2 (student1@gmail.com)
-- =========================
INSERT INTO students (user_id, student_code, major_id, class_name, gpa) VALUES
(2, 'SV001', 1, 'IS01', 3.50);

-- =========================
-- COMPANIES
-- user_id = 3 (company1@gmail.com)
-- =========================
INSERT INTO companies (user_id, company_name, tax_code, address, website, industry_type) VALUES
(3, 'UniWorks Vietnam', '0312345678', '123 Nguyen Hue, District 1, Ho Chi Minh City', 'https://uniworks.vn', 'Technology & Software');

-- =========================
-- INTERNSHIP PERIODS
-- =========================
INSERT INTO internship_periods (name, start_date, end_date, status) VALUES
('Summer 2026', '2026-06-01', '2026-08-31', 'open'),
('Fall 2026', '2026-09-01', '2026-12-31', 'open');

-- =========================
-- JOBS
-- company_id = 1
-- period_id = 1,2
-- =========================
INSERT INTO jobs (company_id, period_id, title, description, requirements, slots, deadline, status) VALUES
(1, 1, 'Frontend Intern', 'Support frontend development for student recruitment platform.', 'HTML, CSS, JavaScript, teamwork', 3, '2026-06-30', 'open'),
(1, 1, 'Backend Intern', 'Work with PHP and MySQL in internship management system.', 'PHP, MySQL, basic MVC thinking', 2, '2026-06-28', 'open'),
(1, 2, 'Business Analyst Intern', 'Support requirement gathering and process documentation.', 'Communication, analysis, documentation', 2, '2026-09-15', 'open');

-- =========================
-- APPLICATIONS
-- student_id = 1
-- job_id = 1,2
-- =========================
INSERT INTO applications (student_id, job_id, cv_url, status, admin_approved, company_approved, applied_at) VALUES
(1, 1, 'uploads/cvs/sample_cv_1.pdf', 'pending', 0, 0, NOW()),
(1, 2, 'uploads/cvs/sample_cv_2.pdf', 'approved', 1, 1, NOW());

-- =========================
-- INTERNSHIP REGISTRATIONS
-- application_id = 2
-- =========================
INSERT INTO internship_registrations (application_id, start_date, end_date, status) VALUES
(2, '2026-06-10', '2026-08-10', 'ongoing');

-- =========================
-- EVALUATIONS
-- registration_id = 1
-- =========================
INSERT INTO evaluations (registration_id, evaluator_role, score, feedback, created_at) VALUES
(1, 'company', 8.50, 'Good performance and positive working attitude during internship.', NOW());

-- =========================
-- REPORTS
-- registration_id = 1
-- =========================
INSERT INTO reports (registration_id, content, submitted_at) VALUES
(1, 'This is the final internship report submitted by the student after completing the internship period.', NOW());

-- =========================
-- MESSAGES
-- admin id = 1, student id = 2, company id = 3
-- =========================
INSERT INTO messages (sender_id, receiver_id, content, created_at) VALUES
(2, 3, 'Hello company, I have submitted my application.', NOW()),
(3, 2, 'We have received your application. Thank you.', NOW());