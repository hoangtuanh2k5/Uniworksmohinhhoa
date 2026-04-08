USE internship_management;

INSERT INTO majors (name, description) VALUES
('Information Systems', 'Information Systems major'),
('Computer Science', 'Computer Science major'),
('Business Administration', 'Business Administration major');

INSERT INTO internship_periods (name, start_date, end_date, status) VALUES
('Summer 2026', '2026-06-01', '2026-08-31', 'upcoming'),
('Fall 2026', '2026-09-01', '2026-12-31', 'upcoming');

INSERT INTO users (email, password, full_name, phone, role) VALUES
('admin@gmail.com', '$2y$10$jzHSXEU.KH5F64v94yUDLO0AP5uJoIaW0eRpkBGRgD9MYE11Xkor2', 'System Admin', '0123456789', 'admin');