# AI Chatbot Widget Integration Guide

## Quick Start

After creating a chatbot in your user dashboard, you'll get an embed code. Here's how to use it:

### Step 1: Get Your Embed Code

1. Login to your account at `/user/login`
2. Go to "My Chatbots" from the sidebar
3. Click on your chatbot
4. Copy the embed code shown

### Step 2: Add to Your Website

Paste the embed code just before the closing `</body>` tag of your HTML:

```html
<!DOCTYPE html>
<html>
<head>
    <title>My Website</title>
</head>
<body>
    <h1>Welcome to my website</h1>
    
    <!-- Your chatbot widget -->
    <script src="http://your-domain.com/widget.js" data-chatbot-id="YOUR-CHATBOT-UID"></script>
</body>
</html>
```

### Step 3: Test the Widget

1. Open your website in a browser
2. You should see a chat bubble in the bottom-right corner
3. Click it to start chatting!

## Important Notes

### Widget Requirements

- The widget needs JavaScript enabled
- It requires `localStorage` support
- It uses `fetch` API for AJAX requests

### API Endpoints Used

The widget automatically calls these endpoints:

1. **Get Config**: `GET /api/chatbot/{chatbotId}/config`
   - Loads chatbot colors, position, welcome message

2. **Send Message**: `POST /api/chatbot/message`
   - Sends visitor messages to the AI

3. **Get Messages**: `GET /api/chatbot/{conversationId}/messages`
   - Polls for new messages every 5 seconds

### Troubleshooting

**Widget doesn't appear:**
1. Check browser console for errors (F12)
2. Verify the script URL is correct
3. Ensure `data-chatbot-id` matches your chatbot UID
4. Check that your chatbot status is "Active"

**Widget appears but doesn't respond:**
1. Check if you've added API keys (OpenAI, Gemini, or Claude)
2. Go to "API Keys" in user dashboard
3. Add at least one working API key
4. Test the chatbot from the user dashboard first

**CORS errors:**
1. If you set a domain restriction on your chatbot, make sure the current domain matches
2. Domain restrictions are optional - leave blank to allow any domain

### Example Test Page

Create a file called `test.html` with this content:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Test Page</title>
</head>
<body>
    <h1>Testing AI Chatbot Widget</h1>
    <p>The chatbot should appear in the bottom-right corner.</p>
    
    <!-- Replace YOUR-CHATBOT-UID with your actual chatbot UID -->
    <script src="/widget.js" data-chatbot-id="YOUR-CHATBOT-UID"></script>
</body>
</html>
```

Place this file in your Laravel `/public` directory and access it at:
```
http://your-domain.com/test.html
```

### Widget Customization

The widget automatically uses your chatbot's settings:

- **Primary Color**: Changes the chat bubble and header color
- **Widget Position**: Can be bottom-right, bottom-left, top-right, or top-left
- **Welcome Message**: First message shown to visitors
- **Offline Message**: Shown when chatbot is unavailable

Configure these in your chatbot settings.

### For Developers

#### Manual Initialization

You can also initialize the widget manually:

```html
<script src="/widget.js"></script>
<script>
    new ChatbotWidget({
        chatbotId: 'your-chatbot-uid',
        apiUrl: '/api/chatbot'  // Optional, defaults to /api/chatbot
    });
</script>
```

#### Widget Events

The widget creates a global `ChatbotWidget` class you can extend or customize.

## Security Notes

- The widget uses XSS protection (escapes all HTML)
- Visitor IDs are stored in localStorage (not cookies)
- All API requests use CSRF protection
- Domain restrictions can be enforced at the chatbot level

## Support

If you encounter issues:

1. Check the browser console for JavaScript errors
2. Verify all API endpoints are accessible
3. Ensure your chatbot has an active API key
4. Test the chatbot from the user dashboard first

For more help, contact your system administrator.
