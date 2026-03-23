# Cinema Website - Local Deployment Guide

## Prerequisites

- WAMP Server installed (at `C:\wamp64`)
- PHP 8.0+ (comes with WAMP)
- MySQL/MariaDB (comes with WAMP)
- Composer (for PHP dependencies)
- Node.js (for frontend dependencies - optional)

## Step-by-Step Deployment

### Step 1: Start WAMP Server

1. Open WAMP from your Start Menu or Desktop
2. Wait for the WAMP icon in your system tray to turn **GREEN**
3. If it doesn't turn green, check for port conflicts (Apache uses port 80, MySQL uses port 3306)

Alternatively, start services via PowerShell (as Administrator):
```powershell
Start-Service wampapache64
Start-Service wampmysqld64
```

### Step 2: Deploy the Project

#### Option A: Symlink Method (Recommended for Development)
```powershell
# Run as Administrator
New-Item -ItemType SymbolicLink -Path "C:\wamp64\www\cinema" -Target "C:\dev-projects\edinburgh-college\hnd\semester1\cinema"
```

#### Option B: Copy Method
```powershell
Copy-Item -Path "C:\dev-projects\edinburgh-college\hnd\semester1\cinema" -Destination "C:\wamp64\www\cinema" -Recurse -Force
```

### Step 3: Install PHP Dependencies (Composer)

```powershell
cd C:\wamp64\www\cinema

# Download composer if not installed
# Or use the composer.phar in the project
php composer.phar install

# If composer is installed globally:
composer install
```

### Step 4: Create Environment Configuration

Create a file named `.env` in the project root (`C:\wamp64\www\cinema\.env`):

```ini
# Database Configuration
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=cinema_db

# Stripe API Keys (Test Mode)
STRIPE_PUBLISHABLE_KEY=pk_test_your_test_key_here
STRIPE_SECRET_KEY=sk_test_your_test_key_here

# OMDB API Key
OMDB_API_KEY=your_omdb_api_key_here

# Security Settings
SESSION_TIMEOUT=1800
MAX_LOGIN_ATTEMPTS=5
```

**Note:** With WAMP default setup:
- Username: `root`
- Password: (empty - leave blank)

### Step 5: Set Up the Database

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create a new database called `cinema_db`
3. Import the SQL schema:
   - Click on `cinema_db`
   - Go to "Import" tab
   - Choose file: `C:\wamp64\www\cinema\sql\movie_listings.php` (if SQL file exists)
   - Or run the SQL setup script

#### Alternative: Command Line Setup

```powershell
# Access MySQL
C:\wamp64\bin\mysql\mysql8.0.31\bin\mysql.exe -u root -p

# Then in MySQL prompt:
CREATE DATABASE cinema_db;
USE cinema_db;

# Create tables (run these SQL commands)
```

### Step 6: Create Required Directories

```powershell
cd C:\wamp64\www\cinema

# Create logs directory
mkdir logs

# Create uploads directory
mkdir uploads

# Create .htaccess in uploads for security
@"
Options -ExecCGI
AddHandler cgi-script .php .pl .py .jsp .asp .sh .cgi
<FilesMatch "\.(?i:php|pl|py|jsp|asp|sh|cgi)$">
Order allow,deny
Deny from all
</FilesMatch>
"@ | Out-File -FilePath "uploads\.htaccess" -Encoding UTF8
```

### Step 7: Set Permissions

```powershell
cd C:\wamp64\www\cinema

# Set folder permissions (Windows)
icacls logs /grant Everyone:F /T
icacls uploads /grant Everyone:F /T
```

### Step 8: Test the Installation

1. Open browser and go to: **http://localhost/cinema/**
2. You should see the home page
3. Test registration: http://localhost/cinema/register.php
4. Test login: http://localhost/cinema/login.php

## Database Schema Setup

Run this SQL in phpMyAdmin to create the required tables:

```sql
-- Users table
CREATE TABLE IF NOT EXISTS new_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(64) NOT NULL,
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Movie listings table
CREATE TABLE IF NOT EXISTS movie_listings (
    movie_id VARCHAR(20) NOT NULL PRIMARY KEY,
    movie_title VARCHAR(100) NOT NULL,
    genre VARCHAR(50),
    age_rating VARCHAR(10),
    show1 VARCHAR(10),
    show2 VARCHAR(10),
    show3 VARCHAR(10),
    theatre VARCHAR(50),
    further_info TEXT,
    release_date DATE,
    img VARCHAR(255),
    preview VARCHAR(300),
    mov_price DECIMAL(5,2)
);

-- Bookings table
CREATE TABLE IF NOT EXISTS movie_booking (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    id INT NOT NULL,
    total DECIMAL(10,2),
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id) REFERENCES new_users(id)
);

-- Booking content table
CREATE TABLE IF NOT EXISTS booking_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    movie_id VARCHAR(20) NOT NULL,
    quantity INT DEFAULT 1,
    price DECIMAL(10,2),
    FOREIGN KEY (booking_id) REFERENCES movie_booking(booking_id)
);

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    movie_id VARCHAR(20) NOT NULL,
    review_text TEXT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    booking_id INT,
    likes BOOLEAN DEFAULT FALSE,
    dislikes BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES new_users(id)
);
```

## Testing Checklist

### Basic Functionality
- [ ] Homepage loads without errors
- [ ] Movie listings display correctly
- [ ] Registration form works
- [ ] Login works
- [ ] Logout works
- [ ] User account page loads

### Security Features
- [ ] CSRF tokens are present in all forms
- [ ] SQL injection attempts are blocked
- [ ] XSS attempts are escaped
- [ ] File upload validation works

### Booking Flow
- [ ] Add movie to cart
- [ ] View cart/showtimes
- [ ] Checkout process
- [ ] Payment processing (test mode)
- [ ] Booking confirmation with QR code
- [ ] View booking history

### Reviews
- [ ] Submit review
- [ ] Edit review
- [ ] Delete review

## Troubleshooting

### Issue: "Could not connect to MySQL"
**Solution:** 
- Check WAMP MySQL service is running
- Verify database credentials in `.env`
- Default WAMP credentials: user=`root`, password=`(empty)`

### Issue: "CSRF token validation failed"
**Solution:**
- Clear browser cookies for localhost
- Check that `config.php` is included at the top of the file
- Verify session is started properly

### Issue: "Class not found" (Stripe/QR Code)
**Solution:**
- Run `composer install` to install dependencies
- Check `vendor/` directory exists

### Issue: Permission denied on uploads
**Solution:**
- Check Windows folder permissions
- Ensure `uploads/` directory exists
- Verify `.htaccess` in uploads directory

### Issue: CSS/JS not loading
**Solution:**
- Check browser console for 404 errors
- Verify paths in `includes/header.php`
- Ensure `node_modules` exists or use CDN fallbacks

## Useful URLs

| URL | Description |
|-----|-------------|
| http://localhost/cinema/ | Home page |
| http://localhost/cinema/login.php | Login page |
| http://localhost/cinema/register.php | Registration page |
| http://localhost/phpmyadmin | Database management |
| http://localhost/cinema/account.php | User account (requires login) |
| http://localhost/cinema/bookings.php | Booking history (requires login) |

## Development Tips

1. **Enable Error Display** (for debugging only):
   Edit `config.php` and change:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

2. **View Logs**:
   - Apache: `C:\wamp64\logs\apache_error.log`
   - PHP: `C:\wamp64\www\cinema\logs\`
   - MySQL: `C:\wamp64\logs\mysql.log`

3. **Restart Services** (after code changes):
   ```powershell
   Restart-Service wampapache64
   Restart-Service wampmysqld64
   ```

## Production Deployment Notes

Before deploying to production:

1. **Change all default passwords**
2. **Use HTTPS only**
3. **Set strong DB password**
4. **Use production Stripe keys**
5. **Disable error display**
6. **Enable all security headers**
7. **Set up regular backups**
8. **Configure firewall rules**

## Quick Start Script

Save this as `setup.ps1` and run as Administrator:

```powershell
# WAMP Cinema Setup Script
$cinemaPath = "C:\dev-projects\edinburgh-college\hnd\semester1\cinema"
$wwwPath = "C:\wamp64\www\cinema"

# Start WAMP services
Start-Service wampapache64
Start-Service wampmysqld64

# Create symlink
New-Item -ItemType SymbolicLink -Path $wwwPath -Target $cinemaPath -Force

# Create directories
mkdir "$cinemaPath\logs" -ErrorAction SilentlyContinue
mkdir "$cinemaPath\uploads" -ErrorAction SilentlyContinue

# Set permissions
icacls "$cinemaPath\logs" /grant Everyone:F /T
icacls "$cinemaPath\uploads" /grant Everyone:F /T

Write-Host "Setup complete! Visit http://localhost/cinema/"
```
