USE internship_management;

INSERT INTO majors (id, name, description) VALUES
(1, 'Information Systems', 'Information Systems major'),
(2, 'Computer Science', 'Computer Science major'),
(3, 'Business Administration', 'Business Administration major');

INSERT INTO internship_periods (id, name, start_date, end_date, status) VALUES
(1, 'Summer 2026', '2026-06-01', '2026-08-31', 'upcoming'),
(2, 'Fall 2026', '2026-09-01', '2026-12-31', 'upcoming');

INSERT INTO users (id, email, password, full_name, phone, role) VALUES
(1, 'admin@gmail.com', '$2y$10$jzHSXEU.KH5F64v94yUDLO0AP5uJoIaW0eRpkBGRgD9MYE11Xkor2', 'System Admin', '0123456789', 'admin'),
(2, 'student1@gmail.com', '$2y$10$jzHSXEU.KH5F64v94yUDLO0AP5uJoIaW0eRpkBGRgD9MYE11Xkor2', 'Lê Văn A', '0987654321', 'student'),
(3, 'student2@gmail.com', '$2y$10$jzHSXEU.KH5F64v94yUDLO0AP5uJoIaW0eRpkBGRgD9MYE11Xkor2', 'Nguyễn Thị B', '0912345678', 'student'),
(4, 'student3@gmail.com', '$2y$10$jzHSXEU.KH5F64v94yUDLO0AP5uJoIaW0eRpkBGRgD9MYE11Xkor2', 'Trần Văn C', '0901234567', 'student'),
(5, 'company1@gmail.com', '$2y$10$jzHSXEU.KH5F64v94yUDLO0AP5uJoIaW0eRpkBGRgD9MYE11Xkor2', 'TechCorp Admin', '0900000001', 'company'),
(6, 'company2@gmail.com', '$2y$10$jzHSXEU.KH5F64v94yUDLO0AP5uJoIaW0eRpkBGRgD9MYE11Xkor2', 'FinServe Admin', '0900000002', 'company');

INSERT INTO students (id, user_id, student_code, major_id, class_name, gpa) VALUES
(1, 2, 'SV2026001', 2, 'CS21', 3.60),
(2, 3, 'SV2026002', 1, 'IS21', 3.45),
(3, 4, 'SV2026003', 3, 'BA21', 3.70);

INSERT INTO companies (id, user_id, company_name, tax_code, address, website, industry_type) VALUES
(1, 5, 'TechCorp', 'TC123456', '123 Lê Lợi, Hà Nội', 'https://techcorp.example', 'Technology'),
(2, 6, 'FinServe', 'FS987654', '456 Trần Phú, Hà Nội', 'https://finserve.example', 'Finance');

INSERT INTO jobs (id, company_id, period_id, title, description, requirements, slots, deadline, status) VALUES
(1, 1, 1, 'Frontend Intern', 'Thiết kế giao diện và tương tác web.', 'HTML, CSS, JavaScript', 5, '2026-06-30', 'open'),
(2, 1, 2, 'Backend Intern', 'Xây dựng API và quản lý dữ liệu.', 'PHP, MySQL', 4, '2026-09-15', 'open'),
(3, 2, 2, 'Business Analyst Intern', 'Phân tích yêu cầu và báo cáo.', 'Business modeling, Excel', 3, '2026-09-10', 'open');

INSERT INTO applications (id, student_id, job_id, applied_at, cv_url, status, admin_approved) VALUES
(1, 1, 1, '2026-05-20 10:18:00', 'uploads/cvs/student1.pdf', 'pending', 0),
(2, 2, 1, '2026-05-21 14:05:00', 'uploads/cvs/student2.pdf', 'reviewed', 0),
(3, 3, 2, '2026-05-22 09:25:00', 'uploads/cvs/student3.pdf', 'approved', 1),
(4, 1, 3, '2026-05-23 11:12:00', 'uploads/cvs/student1.pdf', 'approved', 1),
(5, 2, 3, '2026-05-24 16:40:00', 'uploads/cvs/student2.pdf', 'rejected', 0);
