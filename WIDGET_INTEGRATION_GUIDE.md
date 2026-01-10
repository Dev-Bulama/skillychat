# Chatbot Widget Integration Guide

## Quick Start

### 1. Basic Widget Installation

Add this script tag to your HTML page, just before the closing `</body>` tag:

```html
<script src="http://localhost/skillchat/kode/public/widget.js"
        data-chatbot-id="YOUR-CHATBOT-ID-HERE"
        data-api-url="http://localhost/skillchat/api/chatbot"></script>
```

**Replace `YOUR-CHATBOT-ID-HERE`** with your actual chatbot UID from the database.

### 2. Testing the Widget

1. **Use the Test Page**:
   - Navigate to `http://localhost/skillchat/kode/public/test.html`
   - This test page has debugging enabled to help troubleshoot issues

2. **Check Browser Console**:
   - Press `F12` to open Developer Tools
   - Go to the "Console" tab
   - Look for messages starting with `[Chatbot Widget]`
   - These will show you what's happening during initialization

### 3. Common Issues & Solutions

#### Issue: Chat Bubble Not Showing

**Check these things:**

1. **Correct Widget Path**:
   - For XAMPP: `http://localhost/skillchat/kode/public/widget.js`
   - If using `php artisan serve`: `http://127.0.0.1:8000/widget.js`

2. **Chatbot Must Be Active**:
   ```sql
   -- Check in database:
   SELECT uid, name, status FROM chatbots WHERE uid = 'YOUR-CHATBOT-ID';
   -- Make sure status = 'active'
   ```

3. **API Endpoint Must Work**:
   - Test in browser: `http://localhost/skillchat/api/chatbot/YOUR-CHATBOT-ID/config`
   - Should return JSON with `{"success": true, "config": {...}}`

4. **Check Console for Errors**:
   - CORS errors → Check config/cors.php
   - 404 errors → Check widget.js path
   - API errors → Check Laravel logs

#### Issue: Widget Shows But Can't Send Messages

1. **Add API Keys**: Go to user account → API Keys → Add OpenAI/Gemini/Claude key
2. **Check Chatbot AI Provider**: Make sure chatbot has an AI provider selected
3. **Check Network Tab**: Look for failed API calls to `/api/chatbot/message`

### 4. Widget Configuration Options

```html
<!-- Basic Configuration -->
<script src="http://localhost/skillchat/kode/public/widget.js"
        data-chatbot-id="85c081ed-edfe-44d0-9e24-a3d5629ed8b4"
        data-api-url="http://localhost/skillchat/api/chatbot">
</script>

<!-- Custom API URL (for production) -->
<script src="https://yourdomain.com/widget.js"
        data-chatbot-id="85c081ed-edfe-44d0-9e24-a3d5629ed8b4"
        data-api-url="https://yourdomain.com/api/chatbot">
</script>
```

### 5. Customizing Widget Appearance

Widget appearance is controlled from the chatbot settings in the admin/user dashboard:

- **Primary Color**: Changes button and message bubble colors
- **Widget Position**: bottom-right, bottom-left, top-right, top-left
- **Welcome Message**: First message shown when chat opens
- **Offline Message**: Shown when chatbot is inactive

### 6. Production Deployment

When deploying to production:

1. **Update Widget URL**:
   ```html
   <script src="https://yourdomain.com/widget.js"
   ```

2. **Update API URL**:
   ```html
   data-api-url="https://yourdomain.com/api/chatbot"
   ```

3. **Configure CORS** (if widget is on different domain):
   Edit `config/cors.php`:
   ```php
   'allowed_origins' => ['https://yourwebsite.com'],
   ```

4. **Enable HTTPS**: Always use HTTPS in production

### 7. Debugging Tips

#### Enable Debug Mode

The widget now has built-in console logging. Check browser console for:

```
[Chatbot Widget] Initializing...
[Chatbot Widget] Script element: <script>
[Chatbot Widget] Chatbot ID: 85c081ed-edfe-44d0-9e24-a3d5629ed8b4
[Chatbot Widget] API URL: /api/chatbot
[Chatbot Widget] Starting init()...
[Chatbot Widget] Fetching config from: /api/chatbot/85c081ed-edfe-44d0-9e24-a3d5629ed8b4/config
[Chatbot Widget] Config response status: 200
[Chatbot Widget] Config data: {success: true, config: {...}}
[Chatbot Widget] Widget DOM created
[Chatbot Widget] Event listeners attached
[Chatbot Widget] Polling started
```

#### Common Console Errors

1. **"No script element found with data-chatbot-id attribute"**
   - Widget script tag is missing or malformed
   - Make sure you have `data-chatbot-id` attribute

2. **"Failed to load chatbot config: 404"**
   - Chatbot ID is wrong or doesn't exist
   - API route not registered

3. **"Failed to load chatbot config: CORS"**
   - Cross-origin request blocked
   - Update config/cors.php

4. **"Chatbot not found or inactive"**
   - Chatbot status is not 'active' in database
   - Update status: `UPDATE chatbots SET status = 'active' WHERE uid = 'YOUR-ID'`

### 8. Manual Initialization

You can also initialize the widget manually:

```html
<!-- Load widget.js without auto-initialization -->
<script src="http://localhost/skillchat/kode/public/widget.js"></script>

<script>
// Initialize manually when needed
document.addEventListener('DOMContentLoaded', function() {
    new ChatbotWidget({
        chatbotId: '85c081ed-edfe-44d0-9e24-a3d5629ed8b4',
        apiUrl: 'http://localhost/skillchat/api/chatbot'
    });
});
</script>
```

### 9. For XAMPP Users

Your XAMPP setup has this structure:
```
C:\xampp\htdocs\skillchat\
├── index.php              (main entry point)
├── kode\                  (or "code" directory)
│   ├── public\
│   │   ├── index.php      (Laravel public entry)
│   │   ├── widget.js      ← WIDGET FILE HERE
│   │   └── test.html      ← TEST PAGE HERE
│   ├── app\
│   ├── routes\
│   └── ...
```

**Access URLs:**
- Main App: `http://localhost/skillchat/`
- Widget: `http://localhost/skillchat/kode/public/widget.js`
- Test Page: `http://localhost/skillchat/kode/public/test.html`
- API: `http://localhost/skillchat/api/chatbot/`

### 10. Using Laravel Artisan Serve

If you use `php artisan serve` instead of XAMPP:

```bash
# From the code/kode directory:
cd C:\xampp\htdocs\skillchat\kode
php artisan serve
```

Then access:
- Main App: `http://127.0.0.1:8000/`
- Widget: `http://127.0.0.1:8000/widget.js`
- Test Page: `http://127.0.0.1:8000/test.html`
- API: `http://127.0.0.1:8000/api/chatbot/`

Update widget script tag:
```html
<script src="http://127.0.0.1:8000/widget.js"
        data-chatbot-id="YOUR-CHATBOT-ID"
        data-api-url="http://127.0.0.1:8000/api/chatbot">
</script>
```

## Support

If you encounter issues:
1. Check browser console for errors
2. Check Laravel logs: `kode/storage/logs/laravel.log`
3. Verify chatbot is active in database
4. Test API endpoint directly in browser
5. Use the test.html page for debugging
