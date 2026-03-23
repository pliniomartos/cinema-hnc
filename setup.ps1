# Cinema Website Deployment Script for WAMP
# Run this as Administrator

param(
    [switch]$SkipServices,
    [switch]$SkipDbSetup
)

$ErrorActionPreference = "Stop"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Cinema Website Deployment Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Configuration
$cinemaPath = "C:\dev-projects\edinburgh-college\hnd\semester1\cinema"
$wwwPath = "C:\wamp64\www\cinema"
$wampPath = "C:\wamp64"

# Check if WAMP exists
if (-not (Test-Path $wampPath)) {
    Write-Host "ERROR: WAMP not found at $wampPath" -ForegroundColor Red
    Write-Host "Please install WAMP from: https://www.wampserver.com/"
    exit 1
}

# Step 1: Start WAMP Services
if (-not $SkipServices) {
    Write-Host "[1/6] Starting WAMP Services..." -ForegroundColor Yellow
    
    try {
        Start-Service wampapache64 -ErrorAction SilentlyContinue
        Write-Host "  ✓ Apache started" -ForegroundColor Green
    } catch {
        Write-Host "  ! Apache may already be running or failed to start" -ForegroundColor Yellow
    }
    
    try {
        Start-Service wampmysqld64 -ErrorAction SilentlyContinue
        Write-Host "  ✓ MySQL started" -ForegroundColor Green
    } catch {
        Write-Host "  ! MySQL may already be running or failed to start" -ForegroundColor Yellow
    }
    
    # Wait a moment for services to initialize
    Start-Sleep -Seconds 2
} else {
    Write-Host "[1/6] Skipping service startup (-SkipServices)" -ForegroundColor Gray
}

# Step 2: Create Symbolic Link
Write-Host "[2/6] Creating project link in WAMP www..." -ForegroundColor Yellow

if (Test-Path $wwwPath) {
    # Check if it's a symlink
    $item = Get-Item $wwwPath -ErrorAction SilentlyContinue
    if ($item.PSIsContainer -and $item.Attributes -match "ReparsePoint") {
        Write-Host "  ✓ Symlink already exists" -ForegroundColor Green
    } else {
        Write-Host "  ! Removing existing directory..." -ForegroundColor Yellow
        Remove-Item $wwwPath -Recurse -Force
        New-Item -ItemType SymbolicLink -Path $wwwPath -Target $cinemaPath | Out-Null
        Write-Host "  ✓ Symlink created" -ForegroundColor Green
    }
} else {
    New-Item -ItemType SymbolicLink -Path $wwwPath -Target $cinemaPath | Out-Null
    Write-Host "  ✓ Symlink created" -ForegroundColor Green
}

# Step 3: Create Required Directories
Write-Host "[3/6] Creating required directories..." -ForegroundColor Yellow

$dirs = @("logs", "uploads")
foreach ($dir in $dirs) {
    $fullPath = Join-Path $cinemaPath $dir
    if (-not (Test-Path $fullPath)) {
        New-Item -ItemType Directory -Path $fullPath | Out-Null
        Write-Host "  ✓ Created $dir/" -ForegroundColor Green
    } else {
        Write-Host "  ✓ $dir/ already exists" -ForegroundColor Green
    }
}

# Step 4: Set Permissions
Write-Host "[4/6] Setting folder permissions..." -ForegroundColor Yellow

try {
    icacls "$cinemaPath\logs" /grant Everyone:F /T 2>$null
    icacls "$cinemaPath\uploads" /grant Everyone:F /T 2>$null
    Write-Host "  ✓ Permissions set" -ForegroundColor Green
} catch {
    Write-Host "  ! Could not set permissions (may need admin rights)" -ForegroundColor Yellow
}

# Step 5: Create .htaccess for uploads
Write-Host "[5/6] Securing uploads directory..." -ForegroundColor Yellow

$htaccessContent = 'Options -ExecCGI
AddHandler cgi-script .php .pl .py .jsp .asp .sh .cgi
<FilesMatch "\.(?i:php|pl|py|jsp|asp|sh|cgi)$">
Order allow,deny
Deny from all
</FilesMatch>'

$htaccessPath = Join-Path "$cinemaPath\uploads" ".htaccess"
if (-not (Test-Path $htaccessPath)) {
    $htaccessContent | Out-File -FilePath $htaccessPath -Encoding UTF8
    Write-Host "  ✓ Uploads secured" -ForegroundColor Green
} else {
    Write-Host "  ✓ Uploads already secured" -ForegroundColor Green
}

# Step 6: Check .env file
Write-Host "[6/6] Checking environment configuration..." -ForegroundColor Yellow

$envPath = Join-Path $cinemaPath ".env"
if (-not (Test-Path $envPath)) {
    Write-Host "  ! WARNING: .env file not found!" -ForegroundColor Red
    Write-Host "  Creating from .env.example..." -ForegroundColor Yellow
    
    $envExamplePath = Join-Path $cinemaPath ".env.example"
    if (Test-Path $envExamplePath) {
        Copy-Item $envExamplePath $envPath
        Write-Host "  ✓ .env created from template" -ForegroundColor Green
        Write-Host "" -ForegroundColor Cyan
        Write-Host "  IMPORTANT: Edit .env file with your database credentials!" -ForegroundColor Red
    } else {
        Write-Host "  ! ERROR: .env.example not found" -ForegroundColor Red
    }
} else {
    Write-Host "  ✓ .env file exists" -ForegroundColor Green
}

# Summary
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Deployment Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Website URL: http://localhost/cinema/" -ForegroundColor Yellow
Write-Host "phpMyAdmin:  http://localhost/phpmyadmin" -ForegroundColor Yellow
Write-Host ""

if (-not $SkipDbSetup) {
    Write-Host "NEXT STEPS:" -ForegroundColor Cyan
    Write-Host "1. Open phpMyAdmin: http://localhost/phpmyadmin" -ForegroundColor White
    Write-Host "2. Create database 'cinema_db'" -ForegroundColor White
    Write-Host "3. Import setup_database.sql" -ForegroundColor White
    Write-Host "4. Edit .env file with your database credentials" -ForegroundColor White
    Write-Host ""
}

Write-Host "Default test credentials:" -ForegroundColor Gray
Write-Host "  Email: admin@eccinema.com" -ForegroundColor Gray
Write-Host "  Password: admin123" -ForegroundColor Gray
Write-Host ""

# Check if services are running
$apacheRunning = (Get-Service wampapache64 -ErrorAction SilentlyContinue).Status -eq "Running"
$mysqlRunning = (Get-Service wampmysqld64 -ErrorAction SilentlyContinue).Status -eq "Running"

if (-not $apacheRunning -or -not $mysqlRunning) {
    Write-Host "WARNING: Some services are not running!" -ForegroundColor Red
    if (-not $apacheRunning) { Write-Host "  - Apache is stopped" -ForegroundColor Red }
    if (-not $mysqlRunning) { Write-Host "  - MySQL is stopped" -ForegroundColor Red }
    Write-Host "  Start WAMP and ensure the icon is GREEN" -ForegroundColor Yellow
}
