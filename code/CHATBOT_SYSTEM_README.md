# AI Chatbot System with Human Takeover - Complete Documentation

## Table of Contents
1. [Overview](#overview)
2. [System Requirements](#system-requirements)
3. [Installation & Setup](#installation--setup)
4. [API Key Management](#api-key-management)
5. [User Guide](#user-guide)
6. [Architecture](#architecture)
7. [API Reference](#api-reference)
8. [Security](#security)
9. [Troubleshooting](#troubleshooting)
10. [Advanced Configuration](#advanced-configuration)

---

## Overview

The SkillyChat AI Chatbot System is a comprehensive, multi-provider chatbot platform that allows users to create, train, and deploy AI-powered chatbots on their websites with full human takeover capabilities.

### Key Features

✅ **Multi-Provider AI Support**
- OpenAI (GPT-4o, GPT-4o-mini, GPT-3.5-turbo)
- Google Gemini (Gemini 1.5 Pro, Gemini 1.5 Flash)
- Anthropic Claude (Claude 3.5 Sonnet, Claude 3.5 Haiku)

✅ **User-Managed API Keys**
- Users provide their own AI provider API keys
- Encrypted storage with AES-256
- Global and chatbot-specific key options
- System fallback keys for users who don't provide their own

✅ **Training System**
- Text-based training
- File uploads (TXT, PDF, DOCX)
- URL crawling
- FAQ management

✅ **Human Takeover**
- Automatic trigger detection
- Manual agent takeover
- Multi-agent support
- Real-time conversation monitoring

✅ **Analytics & Tracking**
- Conversation metrics
- AI usage and costs
- Agent performance
- Satisfaction ratings

---

## System Requirements

### Server Requirements
- PHP >= 8.1
- Laravel 10.x
- MySQL 5.7+ or PostgreSQL 10+
- Composer 2.x
- 512MB RAM minimum (2GB recommended)
- HTTPS enabled (required for widget)

### PHP Extensions
- OpenSSL (for encryption)
- PDO
- Mbstring
- Tokenizer
- XML
- Ctype
- JSON
- BCMath

### Optional but Recommended
- Redis (for caching)
- Queue worker (for background processing)
- WebSocket server (for real-time updates)

---

## Installation & Setup

### Step 1: Run Database Migrations

```bash
cd /path/to/skillychat/code
php artisan migrate
```

This will create 8 new tables:
- `chatbots` - Chatbot configurations
- `chatbot_training_data` - Knowledge base
- `chatbot_conversations` - Visitor sessions
- `chatbot_messages` - Chat messages
- `chatbot_agents` - Human support agents
- `chatbot_usage_logs` - Analytics data
- `chatbot_api_keys` - Encrypted API keys
- Updates to `packages` table with chatbot limits

### Step 2: Configure System Fallback Keys (Optional)

Add default AI provider keys to `.env` (optional - users can provide their own):

```env
# AI Provider API Keys (Fallback - Optional)
OPENAI_API_KEY=sk-...
GEMINI_API_KEY=...
CLAUDE_API_KEY=...
```

**Important:** These are FALLBACK keys. Users are encouraged to add their own keys through the UI.

### Step 3: Update Subscription Packages

Update your subscription packages to include chatbot features. You can do this via the admin panel or database:

```sql
UPDATE packages SET
  max_chatbots = 5,                    -- Max chatbots per user
  max_messages_per_month = 10000,      -- Monthly message limit
  max_agents_per_chatbot = 3,          -- Max agents per chatbot
  training_data_size_mb = 50,          -- Max training data (MB)
  chatbot_voice_enabled = 1,           -- Enable voice features
  chatbot_image_enabled = 1,           -- Enable image uploads
  chatbot_human_takeover_enabled = 1,  -- Enable human takeover
  chatbot_analytics_enabled = 1        -- Enable analytics
WHERE id = 1;  -- Your package ID
```

### Step 4: Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Step 5: Verify Installation

1. Log in as a user
2. Navigate to `/user/chatbot/list`
3. You should see the chatbot management interface

---

## API Key Management

### How It Works

The system uses a **three-tier API key priority system**:

```
1. Chatbot-Specific Key (Highest Priority)
   ↓ If not found
2. User-Level Default Key
   ↓ If not found
3. System Fallback Key (.env)
```

### For Users: Adding API Keys

#### Option 1: Global API Keys (Recommended)

Navigate to: **Chatbot Management → Manage API Keys**

1. Click "Manage API Keys" button
2. Select AI provider (OpenAI, Gemini, or Claude)
3. Enter API key
4. Optionally name the key (e.g., "Production Key")
5. Check "Set as Default" to use across all chatbots
6. Click "Add Key"

**Benefits:**
- Used across all your chatbots
- Easy to manage in one place
- Can set different defaults per provider

#### Option 2: Chatbot-Specific API Keys

Navigate to: **Chatbot List → [Your Chatbot] → API Keys**

1. Click the key icon next to your chatbot
2. Add API key specific to that chatbot
3. This key will ONLY be used for this chatbot
4. Overrides your global default keys

**Use Cases:**
- Different billing for different clients
- Testing new API keys
- Using different providers per chatbot

### Getting API Keys

#### OpenAI
1. Visit [platform.openai.com/api-keys](https://platform.openai.com/api-keys)
2. Sign up or log in
3. Click "Create new secret key"
4. Copy the key (starts with `sk-`)
5. **Pricing:** ~$0.15-$2.50 per 1M tokens

#### Google Gemini
1. Visit [aistudio.google.com/app/apikey](https://aistudio.google.com/app/apikey)
2. Sign in with Google account
3. Click "Get API key"
4. Create or select a project
5. Copy the API key
6. **Pricing:** Free tier available, then ~$0.075-$1.25 per 1M tokens

#### Anthropic Claude
1. Visit [console.anthropic.com](https://console.anthropic.com)
2. Sign up or log in
3. Go to "API Keys"
4. Click "Create Key"
5. Copy the key
6. **Pricing:** ~$0.80-$15 per 1M tokens

### API Key Security

- ✅ All keys encrypted with AES-256 before storage
- ✅ Keys validated before saving
- ✅ Only decrypted when making API calls
- ✅ Masked in UI (shows `sk-xxxxx...yyyy`)
- ✅ Usage statistics tracked per key
- ✅ Easy deactivation/deletion

---

## User Guide

### Creating Your First Chatbot

1. **Navigate to Chatbot Management**
   - Go to `/user/chatbot/list`
   - Click "Create Chatbot"

2. **Configure Basic Settings**
   - **Name:** Give your chatbot a name
   - **Description:** Describe its purpose
   - **Domain:** Optional - restrict to specific domain
   - **Language:** Select language (default: English)
   - **Tone:** Choose tone (professional, friendly, casual, etc.)

3. **Customize Appearance**
   - **Primary Color:** Choose widget color
   - **Widget Position:** bottom-right, bottom-left, top-right, top-left
   - **Welcome Message:** First message visitors see
   - **Offline Message:** Shown when no agents are online

4. **Configure Features**
   - ☑️ Emoji Support
   - ☑️ Voice Support (coming soon)
   - ☑️ Image Support
   - ☑️ Human Takeover

5. **Select AI Provider**
   - Choose: OpenAI, Gemini, or Claude
   - Uses your configured API keys
   - Falls back to system keys if none provided

6. **Click "Create Chatbot"**

### Adding Training Data

Training data helps your chatbot answer questions accurately.

#### Method 1: Text Input
1. Go to chatbot → Training
2. Select "Text" type
3. Enter title and content
4. Click "Add Training Data"

#### Method 2: File Upload
1. Select "File" type
2. Upload TXT, PDF, or DOCX file
3. System extracts text automatically

#### Method 3: URL Crawling
1. Select "URL" type
2. Enter website URL
3. System fetches and extracts content

#### Method 4: FAQ Pairs
1. Select "FAQ" type
2. Enter question and answer pairs
3. Great for common questions

**Best Practices:**
- Add 10-50 training items for best results
- Keep content focused and relevant
- Update regularly as information changes
- Use clear, concise language

### Managing Agents (Human Takeover)

1. **Add Agents**
   - Go to chatbot → Agents
   - Enter agent name and email
   - Select role (Admin, Agent, Viewer)
   - Click "Add Agent"

2. **Agent Roles**
   - **Admin:** Full control, can manage other agents
   - **Agent:** Can handle conversations
   - **Viewer:** Read-only access

3. **Set Agent Status**
   - Go to `/user/live-agent/dashboard`
   - Select status: Online, Away, Busy, Offline
   - Only "Online" agents receive auto-assignments

### Installing the Widget

1. **Get Embed Code**
   - Go to chatbot → Embed Code
   - Copy the code snippet

2. **Install on Website**

   **WordPress:**
   ```
   Appearance → Theme File Editor → footer.php
   Paste before </body> tag
   ```

   **Shopify:**
   ```
   Online Store → Themes → Edit Code → theme.liquid
   Paste before </body> tag
   ```

   **HTML Website:**
   ```html
   <script src="https://yoursite.com/widget.js"
           data-chatbot-id="your-chatbot-uid">
   </script>
   ```

   **React/Vue:**
   ```javascript
   // Add to your main layout component
   useEffect(() => {
     const script = document.createElement('script');
     script.src = 'https://yoursite.com/widget.js';
     script.setAttribute('data-chatbot-id', 'your-chatbot-uid');
     document.body.appendChild(script);
   }, []);
   ```

3. **Verify Installation**
   - Visit your website
   - Widget should appear in configured position
   - Send a test message

### Handling Live Conversations

1. **Access Live Agent Dashboard**
   - Navigate to `/user/live-agent/dashboard`
   - Set your status to "Online"

2. **View Pending Conversations**
   - See list of conversations needing human help
   - Filter by status: Pending, Active, Resolved

3. **Claim a Conversation**
   - Click "Claim" button on a pending chat
   - Conversation assigned to you
   - Visitor notified they're chatting with a human

4. **Send Messages**
   - Type in message box
   - Messages sent in real-time
   - Can add internal notes (not visible to visitor)

5. **Resolve Conversation**
   - Click "Resolve" when issue is solved
   - Optionally resume AI handling
   - Conversation moved to resolved list

### Viewing Analytics

1. **Navigate to Analytics**
   - Go to chatbot → Analytics
   - Select date range

2. **Key Metrics**
   - Total conversations
   - Total messages
   - Human takeover rate
   - Average satisfaction rating
   - AI provider costs

3. **Usage Breakdown**
   - Daily message counts
   - Token usage per day
   - Estimated costs
   - Recent conversations with ratings

---

## Architecture

### Database Schema

```
chatbots (Main chatbot config)
├── id
├── uid (UUID)
├── user_id → users
├── name, description, domain
├── settings (JSON)
├── ai_provider (openai, gemini, claude)
├── status (active, inactive, suspended)
└── statistics fields

chatbot_api_keys (Encrypted API keys)
├── id
├── user_id → users
├── chatbot_id → chatbots (nullable for global keys)
├── provider (openai, gemini, claude)
├── api_key (encrypted)
├── is_default
└── usage statistics

chatbot_training_data (Knowledge base)
├── id
├── chatbot_id → chatbots
├── type (text, file, url, faq)
├── content (longText)
├── is_processed
└── embedding_vector (for future vector search)

chatbot_conversations (Visitor sessions)
├── id
├── chatbot_id → chatbots
├── visitor_id
├── status (ai_active, human_requested, human_active, resolved)
├── assigned_agent_id → chatbot_agents
├── visitor metadata
└── statistics fields

chatbot_messages (Individual messages)
├── id
├── conversation_id → chatbot_conversations
├── sender_type (visitor, ai, agent)
├── message (longText)
├── message_type (text, image, file, emoji, voice)
├── ai_confidence, ai_tokens_used, ai_cost
└── metadata (JSON)

chatbot_agents (Human support agents)
├── id
├── chatbot_id → chatbots
├── user_id → users (owner or staff)
├── role (admin, agent, viewer)
├── status (online, offline, away, busy)
└── performance metrics

chatbot_usage_logs (Analytics)
├── id
├── chatbot_id → chatbots
├── conversation_id
├── ai_provider
├── tokens_used, cost
├── messages_count
└── usage_date
```

### Service Architecture

```
ChatbotService
├── createChatbot() - Create new chatbot
├── updateChatbot() - Update settings
├── deleteChatbot() - Delete with cleanup
├── addTrainingData() - Add training content
├── processMessage() - Handle visitor message
├── processImageMessage() - Handle image uploads
├── getOrCreateConversation() - Session management
├── assignToAgent() - Human takeover
└── sendAgentMessage() - Agent response

AIManager (Provider Factory)
├── getProvider() - Get AI provider for chatbot
├── createProvider() - Factory method
├── verifyApiKey() - Validate API key
├── getAvailableProviders() - List providers
└── getEmbeddingProvider() - Get embedding service

OpenAIService implements AIProviderInterface
├── chat() - Send message, get response
├── analyzeImage() - Vision API
├── generateEmbedding() - Create embeddings
├── verifyApiKey() - Validate key
└── calculateCost() - Estimate costs

GeminiService implements AIProviderInterface
├── chat() - Gemini chat
├── analyzeImage() - Gemini vision
├── generateEmbedding() - Gemini embeddings
├── verifyApiKey() - Validate key
└── calculateCost() - Estimate costs

ClaudeService implements AIProviderInterface
├── chat() - Claude chat
├── analyzeImage() - Claude vision
├── generateEmbedding() - N/A (not supported)
├── verifyApiKey() - Validate key
└── calculateCost() - Estimate costs
```

### Request Flow

#### Visitor Sends Message
```
1. Widget POST /api/chatbot/message
2. ChatbotMiddleware validates request
3. ChatController::sendMessage()
4. ChatbotService::processMessage()
5. Build conversation context
6. AIManager::getProvider()
7. Check API keys (chatbot → user → system)
8. AIService::chat()
9. Save AI response
10. Check confidence threshold
11. Trigger human takeover if needed
12. Return response to widget
13. Widget displays message
```

#### Human Takeover Flow
```
1. Trigger detected (keyword, low confidence, manual)
2. Conversation status → human_requested
3. Auto-assign to available agent
4. Agent dashboard shows pending conversation
5. Agent claims conversation
6. Conversation status → human_active
7. AI stops responding
8. Agent sends messages via LiveAgentController
9. Agent resolves conversation
10. Conversation status → resolved
11. Optionally resume AI
```

---

## API Reference

### Public Widget API

All endpoints are rate-limited and require chatbot ID.

#### Send Message
```
POST /api/chatbot/message
Headers: Content-Type: application/json
Body: {
  "chatbot_id": "uuid",
  "visitor_id": "uuid",
  "message": "Hello!",
  "visitor_data": {
    "name": "John Doe",
    "email": "john@example.com",
    "page_url": "https://example.com/page"
  }
}
Response: {
  "success": true,
  "message": {
    "id": 123,
    "sender_type": "ai",
    "message": "Hello! How can I help you today?",
    "created_at": "2024-01-08T12:00:00Z"
  }
}
```

#### Get Messages
```
POST /api/chatbot/messages
Body: {
  "chatbot_id": "uuid",
  "visitor_id": "uuid",
  "last_message_id": 123
}
Response: {
  "success": true,
  "messages": [...],
  "conversation_status": "ai_active"
}
```

#### Upload Image
```
POST /api/chatbot/upload-image
Content-Type: multipart/form-data
Fields:
  - chatbot_id: uuid
  - visitor_id: uuid
  - image: file
  - prompt: "What is this?"
Response: {
  "success": true,
  "message": "This is a photo of..."
}
```

#### Rate Conversation
```
POST /api/chatbot/rate
Body: {
  "chatbot_id": "uuid",
  "visitor_id": "uuid",
  "rating": 5
}
Response: {
  "success": true
}
```

#### Request Human
```
POST /api/chatbot/request-human
Body: {
  "chatbot_id": "uuid",
  "visitor_id": "uuid"
}
Response: {
  "success": true,
  "message": "An agent will be with you shortly"
}
```

### Protected User Routes

Authentication required (session-based).

#### Chatbot Management
- `GET /user/chatbot/list` - List chatbots
- `POST /user/chatbot/store` - Create chatbot
- `POST /user/chatbot/update/{uid}` - Update chatbot
- `GET /user/chatbot/destroy/{uid}` - Delete chatbot

#### Training Data
- `POST /user/chatbot/{uid}/training/store` - Add training data
- `GET /user/chatbot/{uid}/training/{id}/destroy` - Delete training

#### API Keys
- `GET /user/api-keys` - Manage global API keys
- `POST /user/api-keys/store` - Add API key
- `GET /user/api-keys/{id}/destroy` - Delete API key
- `POST /user/api-keys/{id}/set-default` - Set as default

#### Live Agent
- `POST /user/live-agent/claim` - Claim conversation
- `POST /user/live-agent/send-message` - Send message
- `POST /user/live-agent/resolve` - Resolve conversation
- `POST /user/live-agent/set-status` - Update status

---

## Security

### API Key Encryption

All user-provided API keys are encrypted using Laravel's built-in encryption:

```php
// Encryption (automatic via model mutator)
$encrypted = Crypt::encryptString($apiKey);

// Decryption (automatic via model accessor)
$decrypted = Crypt::decryptString($encrypted);
```

**Encryption Method:** AES-256-CBC
**Key Source:** `APP_KEY` in `.env`

### Domain Validation

Chatbot widgets are restricted to authorized domains:

```php
// ChatbotMiddleware
if ($chatbot->domain) {
    $allowedDomain = $chatbot->domain;
    $origin = $request->header('Origin');

    // Check exact match or subdomain
    if (!$this->isAllowedDomain($origin, $allowedDomain)) {
        abort(403, 'Domain not authorized');
    }
}
```

### Rate Limiting

All public API endpoints are rate-limited:

```php
// routes/api.php
Route::middleware(['throttle:60,1'])->group(function () {
    // 60 requests per minute per IP
});
```

Override for image uploads:
```php
Route::post('/upload-image', ...)->middleware('throttle:10,1');
```

### CSRF Protection

All form submissions require CSRF token:

```blade
<form method="POST" action="...">
    @csrf
    <!-- form fields -->
</form>
```

### XSS Prevention

- All user input is escaped in Blade: `{{ $variable }}`
- Widget uses `textContent` instead of `innerHTML`
- File uploads validated for type and size

### SQL Injection Prevention

- All queries use Eloquent ORM or parameter binding
- No raw SQL queries with user input

---

## Troubleshooting

### Common Issues

#### 1. "No API key configured" Error

**Problem:** Chatbot can't find API key
**Solution:**
1. Add API key via `/user/api-keys`
2. Or add system fallback in `.env`:
   ```
   OPENAI_API_KEY=sk-...
   ```

#### 2. "Invalid API key" Error

**Problem:** API key is not valid
**Solution:**
1. Verify key is correct (no extra spaces)
2. Check key is active on provider's dashboard
3. Test key using provider's API test tool

#### 3. Widget Not Showing

**Problem:** Chat widget doesn't appear on website
**Solution:**
1. Check browser console for errors
2. Verify script src URL is correct
3. Ensure chatbot status is "active"
4. Check domain validation settings
5. Verify HTTPS is enabled (required)

#### 4. "Domain not authorized" Error

**Problem:** Widget blocked by domain validation
**Solution:**
1. Add allowed domain in chatbot settings
2. Or leave domain field empty to allow all
3. Check for typos in domain name
4. Include `www.` if needed

#### 5. High AI Costs

**Problem:** API costs are too high
**Solution:**
1. Switch to cheaper model (e.g., GPT-4o-mini instead of GPT-4o)
2. Reduce max_tokens setting
3. Improve training data to reduce repeated questions
4. Enable human takeover for complex queries

#### 6. Agent Can't Claim Conversation

**Problem:** "Claim" button doesn't work
**Solution:**
1. Verify agent status is "Online"
2. Check agent has correct role (not "Viewer")
3. Ensure `can_takeover` permission is enabled
4. Clear browser cache

#### 7. Messages Not Updating in Real-Time

**Problem:** Need to refresh to see new messages
**Solution:**
1. Polling is enabled by default (5-second interval)
2. Check browser console for JavaScript errors
3. Verify `/poll-messages` endpoint is working
4. Consider implementing WebSockets for better performance

---

## Advanced Configuration

### Customizing AI Behavior

#### Adjust Confidence Threshold

```php
// In chatbot settings
'ai_confidence_threshold' => 0.70  // Default
```

Lower value (0.5) = More human takeovers
Higher value (0.9) = Fewer human takeovers

#### Temperature Setting

```php
// In ChatbotService::processMessage()
$options = [
    'temperature' => 0.7,  // Default
    'max_tokens' => 1000,
];
```

Temperature:
- 0.0-0.3: Focused, deterministic
- 0.4-0.7: Balanced (recommended)
- 0.8-1.0: Creative, varied

#### Custom System Prompt

Modify in `ChatbotService::buildSystemPrompt()`:

```php
$basePrompt = <<<PROMPT
You are a helpful AI assistant representing {$chatbot->name}.
{$chatbot->description}

Tone: {$chatbot->tone}
Language: {$chatbot->language}

[Add your custom instructions here]
PROMPT;
```

### Subscription Limits

Configure package limits:

```sql
-- Starter Package
max_chatbots = 1
max_messages_per_month = 1000
max_agents_per_chatbot = 1
training_data_size_mb = 10

-- Professional Package
max_chatbots = 5
max_messages_per_month = 10000
max_agents_per_chatbot = 5
training_data_size_mb = 100

-- Enterprise Package
max_chatbots = -1  -- Unlimited
max_messages_per_month = -1  -- Unlimited
max_agents_per_chatbot = -1
training_data_size_mb = -1
```

### Widget Customization

The widget can be customized via data attributes:

```html
<script
    src="/widget.js"
    data-chatbot-id="xxx"
    data-theme="dark"
    data-position="bottom-left"
    data-primary-color="#FF5733"
    data-greeting="Need help?"
>
</script>
```

### Performance Optimization

#### Enable Caching

```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'redis'),
```

#### Queue Background Jobs

```php
// Process training data in background
dispatch(new ProcessTrainingDataJob($trainingData));
```

#### Database Indexing

Indexes are already added in migrations, but verify:

```sql
-- Check indexes
SHOW INDEX FROM chatbot_conversations;
SHOW INDEX FROM chatbot_messages;
```

---

## Conclusion

This chatbot system provides a complete, production-ready solution for adding AI-powered chat to any website with full human takeover capabilities.

**Key Advantages:**
✅ User-controlled API keys (cost transparency)
✅ Multi-provider support (avoid vendor lock-in)
✅ Subscription-based limits (monetization ready)
✅ Human takeover (customer satisfaction)
✅ Comprehensive analytics (ROI tracking)
✅ Secure and scalable architecture

**For Support:**
- Check troubleshooting section
- Review API reference
- Test in a development environment first

**Future Enhancements:**
- Voice support (Speech-to-Text/Text-to-Speech)
- WebSocket real-time updates
- Vector database for semantic search
- Chatbot templates
- A/B testing capabilities
- Multi-language UI

---

**Version:** 1.0
**Last Updated:** January 8, 2026
**License:** Proprietary - SkillyChat Platform
