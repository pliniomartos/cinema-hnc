# Quick Start Guide - Deploy Cinema Website

## ⚡ Quick Deployment (5 minutes)

### Step 1: Start WAMP Server
1. Click the WAMP icon in your system tray or Start Menu
2. Wait for the icon to turn **GREEN** (all services running)
3. If it stays orange/red, check for port conflicts

### Step 2: Deploy the Project

**Option A: Using Windows Explorer (Easiest)**
1. Open File Explorer
2. Go to `C:\wamp64\www\`
3. Right-click → New → Folder → Name it `cinema`
4. Copy ALL files from `C:\dev-projects\edinburgh-college\hnd\semester1\cinema` to `C:\wamp64\www\cinema\`

**Option B: Using Command Prompt (as Administrator)**
```cmd
# Open CMD as Administrator, then run:
mklink /D C:\wamp64\www\cinema C:\dev-projects\edinburgh-college\hnd\semester1\cinema
```

### Step 3: Set Up Database
1. Open browser → http://localhost/phpmyadmin
2. Click "New" (left sidebar)
3. Database name: `cinema_db`
4. Click "Create"
5. Click on `cinema_db` database
6. Go to "Import" tab
7. Click "Choose File"
8. Select: `C:\wamp64\www\cinema\setup_database.sql`
9. Click "Go" (bottom right)

### Step 4: Configure Environment
1. Open `C:\wamp64\www\cinema\.env` in Notepad
2. Update with your settings (usually WAMP defaults work):
```
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=cinema_db
STRIPE_PUBLISHABLE_KEY=pk_test_your_key
STRIPE_SECRET_KEY=sk_test_your_key
OMDB_API_KEY=your_omdb_api_key_here
```

### Step 5: Test the Website
1. Open: http://localhost/cinema/home.php
2. You should see the homepage!

## 🔑 Test Credentials
- **Email:** admin@eccinema.com
- **Password:** admin123

## 🧪 Testing Checklist

Open each URL and verify:
- [ ] http://localhost/cinema/home.php (Homepage)
- [ ] http://localhost/cinema/movie_listing.php (Movie listings)
- [ ] http://localhost/cinema/login.php (Login)
- [ ] http://localhost/cinema/register.php (Registration)

**After login:**
- [ ] http://localhost/cinema/account.php (Account)
- [ ] http://localhost/cinema/bookings.php (Bookings)

## 🛠️ Troubleshooting

### "Forbidden" or "403" Error
- Check WAMP is running (green icon)
- Check files are in `C:\wamp64\www\cinema\`

### "Database connection failed"
- Check database `cinema_db` exists in phpMyAdmin
- Check `.env` file has correct credentials
- Default WAMP: user=`root`, password=(leave empty)

### "CSRF token validation failed"
- Clear browser cookies
- Refresh the page
- Make sure `config.php` is present

### CSS/JS not loading
- Check browser console (F12 → Console tab)
- Verify `node_modules` folder exists

### Images not showing
- Check `img/` folder exists
- Verify file permissions

## 📂 Project Structure

```
C:\wamp64\www\cinema\
├── home.php              # Homepage
├── login.php             # Login page
├── register.php          # Registration
├── movie.php             # Movie details
├── checkout.php          # Payment
├── account.php           # User account
├── bookings.php          # Booking history
├── config.php            # NEW - Security config
├── .env                  # Environment variables
├── includes/             # Header, footer, navbar
├── sql/                  # Database files
├── logs/                 # Security logs
├── uploads/              # User uploads
└── vendor/               # PHP dependencies
```

## 🔄 Restart After Changes

If you modify code:
1. No restart needed for PHP files
2. For `.env` changes: refresh browser
3. For database changes: restart WAMP

To restart WAMP:
1. Left-click WAMP tray icon
2. Click "Restart All Services"

## 📝 Logs Location

- **Apache errors:** `C:\wamp64\logs\apache_error.log`
- **PHP errors:** `C:\wamp64\www\cinema\logs\`
- **MySQL errors:** `C:\wamp64\logs\mysql.log`

## 🚀 Next Steps

1. **Test all features:**
   - Register a new user
   - Login/logout
   - Browse movies
   - Make a booking (test mode)
   - Submit a review

2. **Verify security:**
   - Check CSRF tokens in page source
   - Try SQL injection (should be blocked)
   - Test file upload validation

3. **Customize:**
   - Add real Stripe test keys
   - Update OMDB API key
   - Customize styling in `scss/`

## 📞 Need Help?

Check these files for detailed info:
- `DEPLOYMENT_GUIDE.md` - Full deployment documentation
- `SECURITY_UPGRADE_SUMMARY.md` - Security changes made
- `QUICK_START.md` - This file

---

**You're all set! Open http://localhost/cinema/ to see your website! 🎉**
