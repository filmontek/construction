-- Construction Management System Database Setup
-- Execute this SQL in phpMyAdmin after creating the database

-- Create database (if not already created)
-- CREATE DATABASE construction_management_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
-- USE construction_management_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Workshop', 'Construction Office', 'Director', 'Dispatcher') NOT NULL,
    workshop ENUM('Indode', 'Adama', 'Metahra', 'Mieso', 'Bike', 'Diredawa', 'Adigal', 'Dewanle', 'Nagad') NULL,
    status ENUM('pending', 'active', 'inactive') DEFAULT 'pending',
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_role (role)
);

-- Work plans table
CREATE TABLE IF NOT EXISTS work_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    monthly_plan_number VARCHAR(50),
    plan_type VARCHAR(50),
    construction_item VARCHAR(200),
    work_time VARCHAR(100),
    section VARCHAR(100),
    up_down_line VARCHAR(50),
    starting_ending_mileage VARCHAR(100),
    work_train VARCHAR(100),
    starting_station VARCHAR(100),
    ending_station VARCHAR(100),
    work_content_requirements TEXT,
    affected_operation_area VARCHAR(200),
    power_on_off VARCHAR(10),
    power_outage_range VARCHAR(100),
    speed_limit_change VARCHAR(100),
    equipment_changes TEXT,
    main_unit_person_charge VARCHAR(100),
    phone_number VARCHAR(20),
    unit_of_suit VARCHAR(100),
    remarks TEXT,
    lister VARCHAR(100),
    workshop_head VARCHAR(100),
    area_manager VARCHAR(100),
    application_time DATE,
    status ENUM('draft', 'submitted', 'office_approved', 'director_approved', 'scheduled', 'rejected') DEFAULT 'draft',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_created_at (created_at)
);

-- Comments table for plan reviews
CREATE TABLE IF NOT EXISTS plan_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES work_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_plan_id (plan_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- Plan attachments table (for future use)
CREATE TABLE IF NOT EXISTS plan_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES work_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_plan_id (plan_id)
);

-- System logs table (for audit trail)
CREATE TABLE IF NOT EXISTS system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Insert default admin user
INSERT INTO users (name, email, password, role, status) VALUES 
('System Administrator', 'admin@construction.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Director', 'active')
ON DUPLICATE KEY UPDATE name = name;

-- Insert sample workshop users for testing
INSERT INTO users (name, email, password, role, workshop, status) VALUES 
('Indode Workshop Manager', 'indode@construction.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Workshop', 'Indode', 'active'),
('Adama Workshop Manager', 'adama@construction.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Workshop', 'Adama', 'active'),
('Construction Office Manager', 'office@construction.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Construction Office', NULL, 'active')
ON DUPLICATE KEY UPDATE name = name;

-- Insert sample work plan for testing
INSERT INTO work_plans (
    user_id, monthly_plan_number, plan_type, construction_item, work_time,
    section, up_down_line, starting_ending_mileage, work_train, starting_station,
    ending_station, work_content_requirements, affected_operation_area,
    power_on_off, power_outage_range, speed_limit_change, equipment_changes,
    main_unit_person_charge, phone_number, unit_of_suit, remarks,
    lister, workshop_head, area_manager, application_time, status, priority
) VALUES (
    2, 'III', 'CT14 Worker deliver', '60 minutes', 'Indode-Bishoftu', 'down',
    '59km+200m-61km+600m', 'One Rail car+N1', 'Indode', 'Bishoftu',
    'Worker deliver', '/', 'ON', '/', '/', '/',
    'Abdi Bekele', '09404073 08', 'Indode Civil Workshop', '/',
    'Evunetu K', 'Indode maintenance workshop head', 'Indode Infrastructure maintenance Workshop area Manager',
    '2025-07-25', 'submitted', 'medium'
);

-- Create views for reporting
CREATE OR REPLACE VIEW plan_summary AS
SELECT 
    wp.id,
    wp.monthly_plan_number,
    wp.construction_item,
    wp.status,
    wp.priority,
    wp.created_at,
    u.name as user_name,
    u.workshop,
    u.role as user_role
FROM work_plans wp
JOIN users u ON wp.user_id = u.id;

CREATE OR REPLACE VIEW user_summary AS
SELECT 
    u.id,
    u.name,
    u.email,
    u.role,
    u.workshop,
    u.status,
    u.created_at,
    COUNT(wp.id) as total_plans,
    COUNT(CASE WHEN wp.status = 'submitted' THEN 1 END) as pending_plans,
    COUNT(CASE WHEN wp.status = 'director_approved' THEN 1 END) as approved_plans
FROM users u
LEFT JOIN work_plans wp ON u.id = wp.user_id
GROUP BY u.id;

-- Default password for all sample users is 'password'
-- Remember to change these passwords in production!