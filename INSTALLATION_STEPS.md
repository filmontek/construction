# Construction Management System - Installation Steps

## Prerequisites

- Computer with Windows, Mac, or Linux
- Internet connection for downloading software
- Basic understanding of file management

## Step 1: Download and Install XAMPP

### For Windows:

1. Go to https://www.apachefriends.org/
2. Click "Download" and select "XAMPP for Windows"
3. Download the latest version (PHP 8.x recommended)
4. Run the downloaded installer (.exe file)
5. During installation:
   - Choose installation directory (default: C:\xampp)
   - Select components: Apache, MySQL, PHP, phpMyAdmin
   - Complete the installation

### For Mac:

1. Download "XAMPP for OS X" from the same website
2. Open the downloaded .dmg file
3. Drag XAMPP to Applications folder
4. Open Terminal and run: `sudo /Applications/XAMPP/xamppfiles/xampp start`

### For Linux:

1. Download "XAMPP for Linux"
2. Make it executable: `chmod +x xampp-linux-x64-8.x.x-installer.run`
3. Run: `sudo ./xampp-linux-x64-8.x.x-installer.run`

## Step 2: Start XAMPP Services

1. Open XAMPP Control Panel
2. Start Apache (click "Start" button next to Apache)
3. Start MySQL (click "Start" button next to MySQL)
4. Both should show "Running" status with green background

**Troubleshooting:**

- If Apache won't start, check if port 80 is being used by another program
- If MySQL won't start, check if port 3306 is being used
- On Windows, you might need to run XAMPP as Administrator

## Step 3: Test XAMPP Installation

1. Open web browser
2. Go to: http://localhost
3. You should see XAMPP welcome page
4. Click "phpMyAdmin" to test database access

## Step 4: Create Project Directory

1. Navigate to XAMPP's htdocs folder:

   - Windows: C:\xampp\htdocs
   - Mac: /Applications/XAMPP/htdocs
   - Linux: /opt/lampp/htdocs

2. Create new folder named "construction_management"
3. This will be your project root directory

## Step 5: Download Project Files

1. Copy all the project files provided into the "construction_management" folder
2. Maintain the folder structure as shown in the guide
3. Ensure all files are in their correct directories

## Step 6: Create Database

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click "New" to create a new database
3. Name it: "construction_management_db"
4. Set collation to: "utf8mb4_general_ci"
5. Click "Create"

## Step 7: Import Database Structure

1. In phpMyAdmin, select your database "construction_management_db"
2. Click "SQL" tab
3. Copy and paste the contents of "DATABASE_SETUP.sql" file
4. Click "Go" to execute the SQL commands
5. You should see tables created successfully

## Step 8: Configure Database Connection

1. Open "config/database.php" in a text editor
2. Verify the database settings:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Usually empty for XAMPP
   define('DB_NAME', 'construction_management_db');
   ```
3. Save the file

## Step 9: Test the Application

1. Open web browser
2. Go to: http://localhost/construction_management
3. You should be redirected to the login page
4. Test with default credentials:
   - Email: admin@construction.com
   - Password: password

## Step 10: Create Additional Users (Optional)

1. Login as admin
2. Go to Users > Create User
3. Create users for different roles:
   - Workshop managers
   - Construction office staff
   - Directors
   - Dispatchers

## Common Issues and Solutions

### Issue: "Database connection failed"

**Solution:**

- Check if MySQL is running in XAMPP
- Verify database name and credentials in config/database.php
- Ensure database was created successfully

### Issue: "Page not found" or 404 errors

**Solution:**

- Check if Apache is running
- Verify project files are in correct htdocs directory
- Check file permissions (especially on Linux/Mac)

### Issue: PHP errors displayed

**Solution:**

- Check PHP error logs in XAMPP
- Ensure all required PHP extensions are enabled
- Verify file paths are correct

### Issue: Styles not loading

**Solution:**

- Check if CSS files exist in assets/css/ directory
- Verify file paths in HTML
- Clear browser cache

## Security Considerations for Production

1. **Change default passwords:**

   - Update admin password
   - Change database passwords

2. **Update configuration:**

   - Disable error display
   - Enable HTTPS
   - Set proper file permissions

3. **Database security:**
   - Create dedicated database user
   - Limit database privileges
   - Regular backups

## File Permissions (Linux/Mac)

```bash
# Set proper permissions
chmod 755 construction_management/
chmod 644 construction_management/*.php
chmod 755 construction_management/assets/
chmod 644 construction_management/assets/css/*
chmod 644 construction_management/assets/js/*
```

## Next Steps

1. Test all functionality:

   - User registration and approval
   - Plan creation and submission
   - Review and approval workflow
   - Comment system

2. Customize as needed:

   - Add your organization's branding
   - Modify form fields if required
   - Add additional workshops or roles

3. Train users:
   - Create user manuals
   - Conduct training sessions
   - Set up support procedures

## Support

If you encounter issues:

1. Check the error logs in XAMPP
2. Verify all installation steps were followed
3. Test with different browsers
4. Check file permissions and paths

Remember to backup your database regularly once you start using the system in production!
