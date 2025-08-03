# Construction Management System - Complete Setup Guide

## Phase 1: Environment Setup

### Step 1: Install XAMPP

1. Download XAMPP from https://www.apachefriends.org/
2. Choose the version for your operating system (Windows/Mac/Linux)
3. Run the installer and install to default location (C:\xampp on Windows)
4. During installation, make sure Apache and MySQL are selected

### Step 2: Start XAMPP Services

1. Open XAMPP Control Panel
2. Start Apache (click Start button next to Apache)
3. Start MySQL (click Start button next to MySQL)
4. Both should show "Running" status with green background

### Step 3: Test Installation

1. Open web browser
2. Go to http://localhost
3. You should see XAMPP welcome page
4. Click on phpMyAdmin to test database access

## Phase 2: Project Setup

### Step 4: Create Project Directory

1. Navigate to C:\xampp\htdocs (Windows) or /Applications/XAMPP/htdocs (Mac)
2. Create new folder named "construction_management"
3. This will be your project root directory

### Step 5: Database Setup

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Click "New" to create new database
3. Name it "construction_management_db"
4. Set collation to "utf8mb4_general_ci"
5. Click "Create"

## Phase 3: Database Structure

### Step 6: Create Database Tables

Execute the following SQL commands in phpMyAdmin:

```sql
-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Workshop', 'Construction Office', 'Director', 'Dispatcher') NOT NULL,
    workshop ENUM('Indode', 'Adama', 'Metahra', 'Mieso', 'Bike', 'Diredawa', 'Adigal', 'Dewanle', 'Nagad') NULL,
    status ENUM('pending', 'active', 'inactive') DEFAULT 'pending',
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Work plans table
CREATE TABLE work_plans (
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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Comments table
CREATE TABLE plan_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES work_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT INTO users (name, email, password, role, status) VALUES
('System Admin', 'admin@construction.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Director', 'active');
-- Default password is 'password'
```

## Phase 4: Project Structure

### Step 7: Create Project Files

Create the following folder structure in your project directory:

```
construction_management/
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── functions.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── pages/
│   ├── login.php
│   ├── register.php
│   ├── dashboard.php
│   ├── plans/
│   ├── users/
│   └── profile/
└── index.php
```

## Phase 5: Core Files Development

### Step 8: Database Configuration

Create config/database.php with database connection settings.

### Step 9: Authentication System

Develop login, registration, and session management.

### Step 10: User Interface

Create responsive HTML/CSS interface with Bootstrap.

### Step 11: Plan Management

Implement plan creation, editing, and approval workflow.

### Step 12: User Management

Build admin panel for user approval and management.

## Phase 6: Testing and Deployment

### Step 13: Testing

Test all features thoroughly on localhost.

### Step 14: Security Implementation

Add input validation, SQL injection prevention, and XSS protection.

### Step 15: Final Deployment

Prepare for production deployment.

## Next Steps

Follow the detailed implementation files that will be provided next.
