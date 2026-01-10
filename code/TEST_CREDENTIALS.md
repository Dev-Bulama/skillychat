# Test Account Credentials

## Quick Setup

Run the test accounts seeder to create test admin and user accounts:

```bash
php artisan db:seed --class=TestAccountsSeeder
```

This command is **safe to run multiple times** - it will not create duplicates.

---

## 🔑 Test Accounts

### Admin Account

- **URL:** `/admin/login`
- **Email:** `admin@test.com`
- **Password:** `password123`
- **Access:** Full admin panel access

### User Account

- **URL:** `/login`
- **Email:** `user@test.com`
- **Password:** `password123`
- **Status:** Active subscription with chatbot features enabled

---

## 📦 Test User Subscription

The test user has an active subscription with:

- ✅ **10 Chatbots** allowed
- ✅ **50,000 messages/month**
- ✅ **5 agents** per chatbot
- ✅ **100MB** training data storage
- ✅ **Voice support** enabled
- ✅ **Image support** enabled
- ✅ **Human takeover** enabled
- ✅ **Analytics** enabled
- ✅ **Expires:** 1 year from seed date
- ✅ **Status:** Running (Active)

---

## 🚀 Quick Start Guide

### 1. Run Migrations (First Time Only)

```bash
php artisan migrate
```

### 2. Create Test Accounts

```bash
php artisan db:seed --class=TestAccountsSeeder
```

You should see output like:

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
```

### 3. Login and Test

**As User:**
1. Go to `http://yoursite.com/login`
2. Login with `user@test.com` / `password123`
3. Navigate to `/user/chatbot/list`
4. Click "Manage API Keys" to add your OpenAI/Gemini/Claude keys
5. Create your first chatbot!

**As Admin:**
1. Go to `http://yoursite.com/admin/login`
2. Login with `admin@test.com` / `password123`
3. Access full admin panel

---

## 🔐 Security Notes

**IMPORTANT:** These are TEST credentials only!

- ⚠️ **DO NOT** use these credentials in production
- ⚠️ **CHANGE** or **DELETE** these accounts before going live
- ⚠️ Use strong, unique passwords for production accounts
- ⚠️ Enable 2FA for production admin accounts

---

## 🧪 Testing the Chatbot System

### Step-by-Step Test Flow

1. **Login as test user**
   ```
   Email: user@test.com
   Password: password123
   ```

2. **Add API Key** (Required)
   - Go to `/user/api-keys`
   - Select provider (OpenAI, Gemini, or Claude)
   - Paste your API key
   - Check "Set as Default"
   - Click "Add Key"

3. **Create Chatbot**
   - Go to `/user/chatbot/list`
   - Click "Create Chatbot"
   - Fill in:
     - Name: "Test Bot"
     - Description: "Testing chatbot features"
     - Welcome Message: "Hello! How can I help?"
     - AI Provider: OpenAI (or your choice)
   - Click "Create Chatbot"

4. **Add Training Data**
   - Click "Training" on your chatbot
   - Add some test content:
     - Type: Text
     - Title: "FAQ"
     - Content: "We are a testing company. We help test software."
   - Click "Add Training Data"

5. **Add Agents** (Optional)
   - Click "Agents" on your chatbot
   - Add yourself as an agent
   - Set status to "Online"

6. **Get Embed Code**
   - Click "Embed Code" on your chatbot
   - Copy the script tag
   - Test on a local HTML file

7. **Test Live Chat**
   - Open the widget on your test page
   - Send messages
   - Test human takeover feature

8. **View Analytics**
   - Click "Analytics" on your chatbot
   - See conversation statistics
   - Check AI usage and costs

---

## 📊 What Gets Created

The seeder creates:

1. **Test Admin** (`admins` table)
   - Super admin with no role restrictions
   - Full access to admin panel

2. **Test User** (`users` table)
   - Regular user account
   - Email verified
   - KYC verified

3. **Test Package** (`packages` table)
   - Named "Chatbot Test Package"
   - All chatbot features enabled
   - Free package (no cost)
   - Unlimited duration

4. **Test Subscription** (`subscriptions` table)
   - Links test user to test package
   - Status: Running (active)
   - All balances set
   - Expires in 1 year

---

## 🔄 Resetting Test Data

To reset and recreate test accounts:

```bash
# Delete existing test accounts
php artisan tinker
>>> User::where('email', 'user@test.com')->delete();
>>> Admin::where('email', 'admin@test.com')->delete();
>>> exit

# Run seeder again
php artisan db:seed --class=TestAccountsSeeder
```

Or use database soft delete if configured:

```bash
php artisan tinker
>>> User::where('email', 'user@test.com')->forceDelete();
>>> Admin::where('email', 'admin@test.com')->forceDelete();
>>> exit
```

---

## ❓ Troubleshooting

### "Email already exists" error

The seeder uses `firstOrCreate()` which prevents duplicates. If you see this error, the accounts already exist and you can use them.

### Can't login as user

1. Verify email is verified:
   ```bash
   php artisan tinker
   >>> User::where('email', 'user@test.com')->update(['email_verified_at' => now()]);
   ```

2. Check user status:
   ```bash
   >>> User::where('email', 'user@test.com')->update(['status' => 1]);
   ```

### No subscription found

Run the seeder again - it will create the missing subscription:

```bash
php artisan db:seed --class=TestAccountsSeeder
```

### Can't create chatbot - "No active subscription"

Verify subscription status:

```bash
php artisan tinker
>>> $user = User::where('email', 'user@test.com')->first();
>>> $user->runningSubscription;
```

If null, run seeder again.

---

## 📝 Notes

- Test accounts are created with `firstOrCreate()` - safe to run multiple times
- Passwords are hashed using Laravel's default bcrypt
- Subscription expires 1 year from creation date
- All chatbot features are enabled for testing
- Package is set to "unlimited" duration

---

**Created:** January 8, 2026
**For:** SkillyChat AI Chatbot System Testing
