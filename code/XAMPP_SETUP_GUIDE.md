# Complete XAMPP Setup Guide for SkillyChat AI Chatbot System

## 📋 Prerequisites

Before you begin, ensure you have:

- ✅ Windows PC (7/8/10/11)
- ✅ XAMPP installed (PHP 8.1 or higher)
- ✅ Composer installed
- ✅ Git installed (optional, if you want to pull from repository)
- ✅ At least 2GB free disk space

---

## 🔧 Part 1: Installing Required Software

### Step 1.1: Install XAMPP

1. **Download XAMPP**
   - Go to: https://www.apachefriends.org/
   - Download XAMPP with **PHP 8.1** or higher
   - File size: ~150MB

2. **Install XAMPP**
   - Run the downloaded installer
   - Install to: `C:\xampp` (default location)
   - Select components:
     - ☑️ Apache
     - ☑️ MySQL
     - ☑️ PHP
     - ☑️ phpMyAdmin
     - ☑️ Fake Sendmail (optional)

3. **Start XAMPP**
   - Open XAMPP Control Panel
   - Click "Start" for **Apache**
   - Click "Start" for **MySQL**
   - Both should show green "Running" status

### Step 1.2: Install Composer

1. **Download Composer**
   - Go to: https://getcomposer.org/download/
   - Download **Composer-Setup.exe** for Windows

2. **Install Composer**
   - Run the installer
   - When asked for PHP location, browse to:
     ```
     C:\xampp\php\php.exe
     ```
   - Complete the installation

3. **Verify Installation**
   - Open Command Prompt (cmd)
   - Type:
     ```cmd
     composer --version
     ```
   - You should see: `Composer version 2.x.x`

---

## 📂 Part 2: Setting Up the Project

### Step 2.1: Place Project Files

**Option A: If you have the project folder already**

1. Copy your `skillychat` folder to:
   ```
   C:\xampp\htdocs\skillychat
   ```

2. The structure should look like:
   ```
   C:\xampp\htdocs\skillychat\
   ├── code\
   │   ├── app\
   │   ├── database\
   │   ├── public\
   │   ├── resources\
   │   ├── routes\
   │   ├── composer.json
   │   └── .env.example
   └── other folders...
   ```

**Option B: If pulling from Git repository**

1. Open Command Prompt
2. Navigate to htdocs:
   ```cmd
   cd C:\xampp\htdocs
   ```

3. Clone the repository:
   ```cmd
   git clone https://github.com/Dev-Bulama/skillychat.git
   cd skillychat
   git checkout claude/skillychat-setup-tQGsx
   ```

### Step 2.2: Navigate to Project Directory

Open Command Prompt and navigate to the code folder:

```cmd
cd C:\xampp\htdocs\skillychat\code
```

All following commands should be run from this directory.

---

## 🔐 Part 3: Configure Environment

### Step 3.1: Create .env File

1. **Copy the example file**
   ```cmd
   copy .env.example .env
   ```

2. **Open .env file** with Notepad or any text editor:
   ```cmd
   notepad .env
   ```

3. **Configure Database Settings**

   Find these lines and update:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=skillychat
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   **Important:** Leave `DB_PASSWORD` empty (blank) for default XAMPP installation.

4. **Configure Application Settings**

   Find and update:
   ```env
   APP_NAME=SkillyChat
   APP_ENV=local
   APP_KEY=
   APP_DEBUG=true
   APP_URL=http://localhost/skillychat/code/public
   ```

5. **Add AI Provider Keys (Optional - for fallback)**

   Add at the end of the file:
   ```env
   # AI Provider API Keys (Optional - Users can add their own)
   OPENAI_API_KEY=
   GEMINI_API_KEY=
   CLAUDE_API_KEY=
   ```

   **Note:** Leave these blank. Users will add their own keys through the UI.

6. **Save and close** the .env file

---

## 🗄️ Part 4: Create Database

### Step 4.1: Open phpMyAdmin

1. Open your web browser
2. Go to: http://localhost/phpmyadmin
3. You should see the phpMyAdmin interface

### Step 4.2: Create New Database

1. Click on **"New"** in the left sidebar
2. Enter database name: `skillychat`
3. Select **"Collation"**: `utf8mb4_unicode_ci`
4. Click **"Create"**

![Database created successfully]

---

## 📦 Part 5: Install Dependencies

### Step 5.1: Install PHP Dependencies

From Command Prompt in the project directory:

```cmd
cd C:\xampp\htdocs\skillychat\code
composer install
```

This will take 2-5 minutes. You'll see:
```
Loading composer repositories with package information
Installing dependencies from lock file
...
Generating optimized autoload files
```

**If you see errors:**

- Make sure you're in the correct directory
- Verify Composer is installed: `composer --version`
- Check PHP version: `php -v` (should be 8.1+)

### Step 5.2: Generate Application Key

```cmd
php artisan key:generate
```

You should see:
```
Application key set successfully.
```

This updates the `APP_KEY` in your .env file.

---

## 🚀 Part 6: Run Database Migrations

### Step 6.1: Run All Migrations

This creates all necessary database tables:

```cmd
php artisan migrate
```

You'll see output like:
```
Migration table created successfully.
Migrating: 2014_10_12_000000_create_users_table
Migrated:  2014_10_12_000000_create_users_table (45.67ms)
Migrating: 2023_06_11_101656_create_admins_table
Migrated:  2023_06_11_101656_create_admins_table (23.45ms)
...
Migrating: 2026_01_08_152423_create_chatbots_table
Migrated:  2026_01_08_152423_create_chatbots_table (67.89ms)
...
[All migrations completed]
```

**If you see "Access denied" error:**
- Go back to .env and verify DB_USERNAME=root and DB_PASSWORD is empty
- Make sure MySQL is running in XAMPP

**If you see "Database does not exist" error:**
- Go back to Part 4 and create the database in phpMyAdmin

### Step 6.2: Verify Tables Created

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click on `skillychat` database in left sidebar
3. You should see 50+ tables including:
   - ✅ `users`
   - ✅ `admins`
   - ✅ `packages`
   - ✅ `subscriptions`
   - ✅ `chatbots`
   - ✅ `chatbot_conversations`
   - ✅ `chatbot_messages`
   - ✅ `chatbot_agents`
   - ✅ And many more...

---

## 👥 Part 7: Create Test Accounts

### Step 7.1: Run Test Account Seeder

```cmd
php artisan db:seed --class=TestAccountsSeeder
```

You should see beautiful output:
```
✓ Test Admin Created
  Email: admin@test.com
  Password: password123

✓ Test User Created
  Email: user@test.com
  Password: password123

✓ Test Package Created/Updated
  Name: Chatbot Test Package
  Features: All chatbot features enabled

✓ Test Subscription Created
  Status: Active (Running)
  Expires: 2027-01-08

═══════════════════════════════════════════════════
TEST ACCOUNTS READY!
═══════════════════════════════════════════════════

ADMIN LOGIN:
  URL: /admin/login
  Email: admin@test.com
  Password: password123

USER LOGIN:
  URL: /login
  Email: user@test.com
  Password: password123

USER HAS ACTIVE SUBSCRIPTION WITH:
  ✓ 10 Chatbots allowed
  ✓ 50,000 messages/month
  ✓ 5 agents per chatbot
  ✓ 100MB training data
  ✓ Voice, Image, Human Takeover enabled
  ✓ Analytics enabled

TO TEST CHATBOT SYSTEM:
  1. Login as user@test.com
  2. Go to /user/chatbot/list
  3. Add your API keys at /user/api-keys
  4. Create a chatbot and start testing!
```

---

## 🌐 Part 8: Configure Virtual Host (Optional but Recommended)

This makes your site accessible at `http://skillychat.test` instead of the long localhost URL.

### Step 8.1: Edit httpd-vhosts.conf

1. Open file:
   ```
   C:\xampp\apache\conf\extra\httpd-vhosts.conf
   ```

2. Add at the end:
   ```apache
   <VirtualHost *:80>
       ServerName skillychat.test
       DocumentRoot "C:/xampp/htdocs/skillychat/code/public"
       <Directory "C:/xampp/htdocs/skillychat/code/public">
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

3. Save the file

### Step 8.2: Edit hosts File

1. **Open Notepad as Administrator**
   - Right-click Notepad → Run as administrator

2. **Open hosts file**
   - File → Open
   - Navigate to: `C:\Windows\System32\drivers\etc\`
   - Change filter to "All Files"
   - Open `hosts` file

3. **Add entry at the end**
   ```
   127.0.0.1    skillychat.test
   ```

4. **Save the file**

### Step 8.3: Restart Apache

1. Open XAMPP Control Panel
2. Click "Stop" for Apache
3. Click "Start" for Apache

### Step 8.4: Update .env

Open your .env file and update:
```env
APP_URL=http://skillychat.test
```

Now you can access your site at: **http://skillychat.test**

---

## ✅ Part 9: Verify Installation

### Step 9.1: Check Frontend

Open browser and go to:
```
http://localhost/skillychat/code/public
```

Or if you set up virtual host:
```
http://skillychat.test
```

You should see the SkillyChat homepage.

### Step 9.2: Test User Login

1. Go to: http://localhost/skillychat/code/public/login
2. Enter credentials:
   - Email: `user@test.com`
   - Password: `password123`
3. Click "Login"
4. You should be redirected to user dashboard

### Step 9.3: Test Admin Login

1. Go to: http://localhost/skillychat/code/public/admin/login
2. Enter credentials:
   - Email: `admin@test.com`
   - Password: `password123`
3. Click "Login"
4. You should see the admin panel

---

## 🤖 Part 10: Test Chatbot System

### Step 10.1: Get AI Provider API Key

Before creating a chatbot, you need an API key from one of these providers:

**Option 1: OpenAI (Recommended for testing)**
1. Go to: https://platform.openai.com/api-keys
2. Sign up or log in
3. Click "Create new secret key"
4. Copy the key (starts with `sk-...`)
5. **Cost:** ~$5 credit free for new accounts, then pay-as-you-go

**Option 2: Google Gemini (Free tier available)**
1. Go to: https://aistudio.google.com/app/apikey
2. Sign in with Google
3. Click "Get API key"
4. Copy the key
5. **Cost:** Free tier with limits, then ~$0.075 per 1M tokens

**Option 3: Anthropic Claude**
1. Go to: https://console.anthropic.com
2. Sign up
3. Go to API Keys
4. Create new key
5. **Cost:** ~$0.80 per 1M tokens

### Step 10.2: Add Your API Key

1. **Login as test user**
   - Email: `user@test.com`
   - Password: `password123`

2. **Navigate to API Keys**
   - Click "Manage API Keys" button
   - Or go directly to: `/user/api-keys`

3. **Add your API key**
   - Select Provider: OpenAI (or your choice)
   - Key Name: "My Testing Key" (optional)
   - API Key: Paste your key (e.g., `sk-...`)
   - ☑️ Check "Set as Default"
   - Click "Add Key"

4. **Verify key added**
   - You should see "API key added successfully"
   - Key appears in the table with status "Active"

### Step 10.3: Create Your First Chatbot

1. **Go to Chatbot Management**
   - Navigate to: `/user/chatbot/list`
   - Click "Create Chatbot"

2. **Fill in the form**
   ```
   Name: My Test Bot
   Description: Testing the chatbot system
   Domain: (leave empty for testing)
   Language: English
   Tone: Friendly
   Welcome Message: Hello! How can I help you today?
   Offline Message: We're currently offline. Please leave a message.
   Primary Color: #0084ff (default blue)
   Widget Position: bottom-right
   ```

3. **Enable Features**
   - ☑️ Emoji Support
   - ☑️ Image Support
   - ☑️ Human Takeover

4. **Select AI Provider**
   - Choose: OpenAI (or whichever you added a key for)

5. **Click "Create Chatbot"**

### Step 10.4: Add Training Data

1. **Click "Training" button** on your chatbot

2. **Add some test content**
   - Type: Text
   - Title: "Company Info"
   - Content:
     ```
     We are SkillyChat, a social media marketing platform.
     We help businesses manage their social media presence.
     We offer AI-powered chatbots to improve customer support.
     Our platform supports multiple languages.
     Contact us at support@skillychat.com
     ```
   - Click "Add Training Data"

3. **Add more training items** (optional but recommended)
   - Type: FAQ
   - Title: "Pricing"
   - Content: "Our basic plan starts at $29/month and includes 5 chatbots."

### Step 10.5: Test the Widget

1. **Get Embed Code**
   - Click "Embed Code" button on your chatbot
   - Copy the script tag

2. **Create a Test HTML File**
   - Create: `C:\xampp\htdocs\test-widget.html`
   - Add this content:
   ```html
   <!DOCTYPE html>
   <html>
   <head>
       <title>Chatbot Test</title>
   </head>
   <body>
       <h1>Testing My Chatbot</h1>
       <p>The chatbot should appear in the bottom-right corner.</p>

       <!-- Paste your embed code here -->
       <script src="http://skillychat.test/widget.js"
               data-chatbot-id="YOUR-CHATBOT-UID">
       </script>
   </body>
   </html>
   ```

3. **Open the test page**
   - Go to: http://localhost/test-widget.html
   - You should see the chat widget appear!

4. **Test the chat**
   - Click the widget to open
   - Send a message: "What services do you offer?"
   - AI should respond based on your training data

### Step 10.6: Test Human Takeover

1. **Open Live Agent Dashboard**
   - In a new browser tab, go to: `/user/live-agent/dashboard`
   - Set your status to "Online"

2. **Test in the widget**
   - In your test page, send message: "I need to talk to a human"
   - The conversation should appear in your agent dashboard
   - Click "Claim" to take over
   - Send a message as agent
   - Visitor sees: "You're now chatting with a live agent"

---

## 🎨 Part 11: Optional Configurations

### Clear Application Cache

If you make changes to .env or routes:

```cmd
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Enable Storage Symlink

For file uploads to work:

```cmd
php artisan storage:link
```

### Set Correct Permissions (if needed)

On Windows, usually not needed, but if you have permission errors:

```cmd
attrib -r -s C:\xampp\htdocs\skillychat\code\storage\* /s /d
attrib -r -s C:\xampp\htdocs\skillychat\code\bootstrap\cache\* /s /d
```

---

## 🐛 Troubleshooting Common Issues

### Issue 1: "500 Internal Server Error"

**Solutions:**
1. Check Apache error log:
   ```
   C:\xampp\apache\logs\error.log
   ```

2. Enable debugging in .env:
   ```env
   APP_DEBUG=true
   ```

3. Clear cache:
   ```cmd
   php artisan config:clear
   php artisan cache:clear
   ```

### Issue 2: "Page Not Found / 404"

**Solutions:**
1. Make sure you're accessing through `/public` directory:
   ```
   http://localhost/skillychat/code/public
   ```

2. Or set up virtual host as described in Part 8

3. Check Apache mod_rewrite is enabled:
   - Open: `C:\xampp\apache\conf\httpd.conf`
   - Find: `#LoadModule rewrite_module modules/mod_rewrite.so`
   - Remove the `#` to enable it
   - Restart Apache

### Issue 3: "SQLSTATE[HY000] [2002] Connection refused"

**Solutions:**
1. Verify MySQL is running in XAMPP
2. Check .env database credentials
3. Verify database exists in phpMyAdmin
4. Try connecting with:
   ```cmd
   php artisan tinker
   >>> DB::connection()->getPdo();
   ```

### Issue 4: "Class not found" errors

**Solutions:**
1. Run composer install again:
   ```cmd
   composer install
   ```

2. Dump autoload:
   ```cmd
   composer dump-autoload
   ```

### Issue 5: Widget Not Loading

**Solutions:**
1. Check widget.js exists:
   ```
   C:\xampp\htdocs\skillychat\code\public\widget.js
   ```

2. Verify chatbot UID is correct in embed code

3. Check browser console for JavaScript errors (F12)

4. Ensure chatbot status is "active"

### Issue 6: "Token Mismatch" on Login

**Solutions:**
1. Clear browser cookies
2. Ensure APP_KEY is set in .env
3. Clear application cache
4. Try in incognito mode

### Issue 7: Images/Files Not Uploading

**Solutions:**
1. Run storage link:
   ```cmd
   php artisan storage:link
   ```

2. Check storage permissions

3. Verify `upload_max_filesize` in php.ini:
   ```
   C:\xampp\php\php.ini

   upload_max_filesize = 20M
   post_max_size = 20M
   ```

---

## 📊 Part 12: Monitoring Your Installation

### Check System Status

1. **XAMPP Control Panel**
   - Apache: Should be green/running
   - MySQL: Should be green/running

2. **Database Tables**
   - Open phpMyAdmin
   - Check `skillychat` database has 50+ tables

3. **Test Accounts**
   - User login works: `/login`
   - Admin login works: `/admin/login`

4. **Chatbot System**
   - Can create chatbots
   - Can add training data
   - Widget loads on test page
   - Messages send successfully

---

## 🔄 Part 13: Daily Usage

### Starting Your Development

1. **Open XAMPP Control Panel**
2. **Start Apache** (if not running)
3. **Start MySQL** (if not running)
4. **Open browser** to your project URL
5. **Start developing!**

### Stopping After Work

1. **Stop Apache** in XAMPP
2. **Stop MySQL** in XAMPP
3. **Close XAMPP Control Panel**

---

## 📝 Quick Reference Card

**Project Path:**
```
C:\xampp\htdocs\skillychat\code
```

**URLs:**
```
Frontend:  http://localhost/skillychat/code/public
           or http://skillychat.test (if virtual host set up)

User Login:   /login
Admin Login:  /admin/login
Chatbots:     /user/chatbot/list
API Keys:     /user/api-keys
Live Agent:   /user/live-agent/dashboard
```

**Test Credentials:**
```
Admin:
  Email: admin@test.com
  Password: password123

User:
  Email: user@test.com
  Password: password123
```

**Useful Commands:**
```cmd
cd C:\xampp\htdocs\skillychat\code

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate

# Create test accounts
php artisan db:seed --class=TestAccountsSeeder

# Generate app key
php artisan key:generate

# Create storage link
php artisan storage:link
```

**File Locations:**
```
.env file:           C:\xampp\htdocs\skillychat\code\.env
httpd-vhosts.conf:   C:\xampp\apache\conf\extra\httpd-vhosts.conf
hosts file:          C:\Windows\System32\drivers\etc\hosts
php.ini:             C:\xampp\php\php.ini
Apache error log:    C:\xampp\apache\logs\error.log
```

---

## ✨ Congratulations!

You've successfully set up SkillyChat with the AI Chatbot System on XAMPP! 🎉

### Next Steps:

1. ✅ **Test all features** using the test accounts
2. ✅ **Create your first real chatbot** with your own API key
3. ✅ **Explore the live agent dashboard**
4. ✅ **Check out the analytics** after some test conversations
5. ✅ **Read the full documentation** in `CHATBOT_SYSTEM_README.md`

### Important Notes:

- 🔒 **Change test passwords** before going to production
- 💾 **Backup your database** regularly
- 🔑 **Keep API keys secure** - never commit them to git
- 📊 **Monitor usage** to avoid unexpected API costs
- 🔄 **Update regularly** by pulling latest changes

---

**Need Help?**

- Check `CHATBOT_SYSTEM_README.md` for detailed technical docs
- Check `TEST_CREDENTIALS.md` for testing guide
- Review troubleshooting section above
- Check Apache/PHP error logs

**Happy Coding! 🚀**
