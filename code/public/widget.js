(function() {
    'use strict';

    class ChatbotWidget {
        constructor(config) {
            this.chatbotId = config.chatbotId;
            this.apiUrl = config.apiUrl || '/api/chatbot';
            this.visitorId = this.getOrCreateVisitorId();
            this.conversationId = null;
            this.isOpen = false;
            this.config = {};
            this.lastMessageId = null;

            // Image preview state
            this.selectedImage = null;

            // Voice recording state
            this.isRecording = false;
            this.mediaRecorder = null;
            this.audioChunks = [];
            this.recordingStartTime = null;
            this.recordingInterval = null;

            this.init();
        }

        async init() {
            console.log('[Chatbot Widget] Starting init()...');
            await this.loadConfig();
            console.log('[Chatbot Widget] Config loaded:', this.config);
            this.createWidget();
            console.log('[Chatbot Widget] Widget DOM created');
            this.attachEventListeners();
            console.log('[Chatbot Widget] Event listeners attached');
            this.startPolling();
            console.log('[Chatbot Widget] Polling started');
        }

        async loadConfig() {
            try {
                const configUrl = `${this.apiUrl}/${this.chatbotId}/config`;
                console.log('[Chatbot Widget] Fetching config from:', configUrl);
                const response = await fetch(configUrl);
                console.log('[Chatbot Widget] Config response status:', response.status);
                const data = await response.json();
                console.log('[Chatbot Widget] Config data:', data);
                if (data.success) {
                    this.config = data.config;
                } else {
                    console.error('[Chatbot Widget] Config fetch failed:', data);
                }
            } catch (error) {
                console.error('[Chatbot Widget] Failed to load chatbot config:', error);
            }
        }

        createWidget() {
            const position = this.config.widget_position || 'bottom-right';
            const primaryColor = this.config.primary_color || '#0084ff';

            const styles = `
                <style>
                    .chatbot-widget-container {
                        position: fixed;
                        ${position.includes('bottom') ? 'bottom: 20px;' : 'top: 20px;'}
                        ${position.includes('right') ? 'right: 20px;' : 'left: 20px;'}
                        z-index: 9999;
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    }
                    .chatbot-widget-button {
                        width: 60px;
                        height: 60px;
                        border-radius: 50%;
                        background-color: ${primaryColor};
                        border: none;
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: transform 0.3s ease;
                    }
                    .chatbot-widget-button:hover {
                        transform: scale(1.1);
                    }
                    .chatbot-widget-button svg {
                        width: 30px;
                        height: 30px;
                        fill: white;
                    }
                    .chatbot-widget-window {
                        position: absolute;
                        ${position.includes('bottom') ? 'bottom: 80px;' : 'top: 80px;'}
                        ${position.includes('right') ? 'right: 0;' : 'left: 0;'}
                        width: 380px;
                        height: 600px;
                        background: white;
                        border-radius: 12px;
                        box-shadow: 0 5px 40px rgba(0, 0, 0, 0.16);
                        display: none;
                        flex-direction: column;
                        overflow: hidden;
                    }
                    .chatbot-widget-window.open {
                        display: flex;
                    }
                    .chatbot-widget-header {
                        background-color: ${primaryColor};
                        color: white;
                        padding: 20px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    }
                    .chatbot-widget-header h3 {
                        margin: 0;
                        font-size: 18px;
                        font-weight: 600;
                    }
                    .chatbot-widget-close {
                        background: none;
                        border: none;
                        color: white;
                        font-size: 24px;
                        cursor: pointer;
                        padding: 0;
                        line-height: 1;
                    }
                    .chatbot-widget-messages {
                        flex: 1;
                        overflow-y: auto;
                        padding: 20px;
                        background: #f8f9fa;
                    }
                    .chatbot-message {
                        margin-bottom: 15px;
                        display: flex;
                        gap: 10px;
                    }
                    .chatbot-message.visitor {
                        justify-content: flex-end;
                    }
                    .chatbot-message-content {
                        max-width: 70%;
                        padding: 10px 15px;
                        border-radius: 18px;
                        word-wrap: break-word;
                    }
                    .chatbot-message.ai .chatbot-message-content,
                    .chatbot-message.agent .chatbot-message-content {
                        background: white;
                        color: #333;
                        border-bottom-left-radius: 4px;
                    }
                    .chatbot-message.visitor .chatbot-message-content {
                        background: ${primaryColor};
                        color: white;
                        border-bottom-right-radius: 4px;
                    }
                    .chatbot-widget-input-area {
                        padding: 15px;
                        background: white;
                        border-top: 1px solid #e1e8ed;
                        display: flex;
                        gap: 10px;
                    }
                    .chatbot-widget-input {
                        flex: 1;
                        border: 1px solid #e1e8ed;
                        border-radius: 20px;
                        padding: 10px 15px;
                        font-size: 14px;
                        outline: none;
                    }
                    .chatbot-widget-send {
                        background-color: ${primaryColor};
                        border: none;
                        border-radius: 50%;
                        width: 40px;
                        height: 40px;
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        flex-shrink: 0;
                    }
                    .chatbot-widget-send svg {
                        width: 20px;
                        height: 20px;
                        fill: white;
                    }
                    .chatbot-widget-attachment-btn {
                        background: none;
                        border: none;
                        color: #90949c;
                        cursor: pointer;
                        padding: 8px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: color 0.2s;
                        flex-shrink: 0;
                    }
                    .chatbot-widget-attachment-btn:hover {
                        color: ${primaryColor};
                    }
                    .chatbot-widget-attachment-btn svg {
                        width: 20px;
                        height: 20px;
                        fill: currentColor;
                    }
                    .chatbot-widget-attachments {
                        display: flex;
                        gap: 5px;
                        align-items: center;
                    }
                    input[type="file"].chatbot-file-input {
                        display: none;
                    }
                    .chatbot-typing-indicator {
                        display: flex;
                        gap: 4px;
                        padding: 10px 15px;
                        background: white;
                        border-radius: 18px;
                        width: fit-content;
                    }
                    .chatbot-typing-indicator span {
                        width: 8px;
                        height: 8px;
                        background: #90949c;
                        border-radius: 50%;
                        animation: typing 1.4s infinite;
                    }
                    .chatbot-typing-indicator span:nth-child(2) {
                        animation-delay: 0.2s;
                    }
                    .chatbot-typing-indicator span:nth-child(3) {
                        animation-delay: 0.4s;
                    }
                    @keyframes typing {
                        0%, 60%, 100% { transform: translateY(0); }
                        30% { transform: translateY(-10px); }
                    }
                    .chatbot-emoji-picker {
                        position: absolute;
                        bottom: 70px;
                        left: 15px;
                        background: white;
                        border-radius: 12px;
                        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                        padding: 15px;
                        display: none;
                        width: 300px;
                        max-height: 350px;
                        z-index: 10000;
                    }
                    .chatbot-emoji-picker.open {
                        display: block;
                    }
                    .chatbot-emoji-picker-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 10px;
                        padding-bottom: 10px;
                        border-bottom: 1px solid #e1e8ed;
                    }
                    .chatbot-emoji-picker-title {
                        font-size: 14px;
                        font-weight: 600;
                        color: #333;
                    }
                    .chatbot-emoji-picker-close {
                        background: none;
                        border: none;
                        font-size: 20px;
                        color: #90949c;
                        cursor: pointer;
                        padding: 0;
                        line-height: 1;
                    }
                    .chatbot-emoji-grid {
                        display: grid;
                        grid-template-columns: repeat(8, 1fr);
                        gap: 8px;
                        max-height: 250px;
                        overflow-y: auto;
                    }
                    .chatbot-emoji-item {
                        background: none;
                        border: none;
                        font-size: 24px;
                        cursor: pointer;
                        padding: 5px;
                        border-radius: 5px;
                        transition: background 0.2s;
                    }
                    .chatbot-emoji-item:hover {
                        background: #f5f5f5;
                    }
                    .chatbot-image-preview {
                        position: relative;
                        padding: 10px 15px;
                        background: #f8f9fa;
                        border-radius: 8px;
                        margin: 10px 15px;
                        display: none;
                    }
                    .chatbot-image-preview.show {
                        display: block;
                    }
                    .chatbot-image-preview img {
                        max-width: 100%;
                        max-height: 200px;
                        border-radius: 8px;
                        display: block;
                    }
                    .chatbot-image-preview-info {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-top: 8px;
                        font-size: 12px;
                        color: #666;
                    }
                    .chatbot-image-preview-remove {
                        background: #dc3545;
                        color: white;
                        border: none;
                        border-radius: 4px;
                        padding: 4px 8px;
                        font-size: 11px;
                        cursor: pointer;
                    }
                    .chatbot-voice-recording {
                        padding: 15px;
                        background: #fff3cd;
                        border-radius: 8px;
                        margin: 10px 15px;
                        display: none;
                        align-items: center;
                        gap: 10px;
                    }
                    .chatbot-voice-recording.active {
                        display: flex;
                    }
                    .chatbot-voice-recording-dot {
                        width: 12px;
                        height: 12px;
                        background: #dc3545;
                        border-radius: 50%;
                        animation: pulse 1.5s infinite;
                    }
                    @keyframes pulse {
                        0%, 100% { opacity: 1; }
                        50% { opacity: 0.5; }
                    }
                    .chatbot-voice-recording-time {
                        flex: 1;
                        font-size: 14px;
                        color: #333;
                    }
                    .chatbot-voice-recording-controls {
                        display: flex;
                        gap: 5px;
                    }
                    .chatbot-voice-btn-stop {
                        background: #dc3545;
                        color: white;
                        border: none;
                        border-radius: 4px;
                        padding: 5px 10px;
                        font-size: 12px;
                        cursor: pointer;
                    }
                    .chatbot-voice-btn-cancel {
                        background: #6c757d;
                        color: white;
                        border: none;
                        border-radius: 4px;
                        padding: 5px 10px;
                        font-size: 12px;
                        cursor: pointer;
                    }
                </style>
            `;

            const html = `
                <div class="chatbot-widget-container">
                    <button class="chatbot-widget-button" id="chatbot-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12c0 1.54.36 3 .97 4.29L2 22l5.71-.97C9 21.64 10.46 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm0 18c-1.38 0-2.68-.29-3.86-.8l-.28-.13-2.86.49.49-2.86-.13-.28C4.85 14.68 4.5 13.38 4.5 12c0-4.14 3.36-7.5 7.5-7.5s7.5 3.36 7.5 7.5-3.36 7.5-7.5 7.5z"/>
                        </svg>
                    </button>
                    <div class="chatbot-widget-window" id="chatbot-window">
                        <div class="chatbot-widget-header">
                            <h3>${this.config.name || 'Chat'}</h3>
                            <button class="chatbot-widget-close" id="chatbot-close">&times;</button>
                        </div>
                        <div class="chatbot-widget-messages" id="chatbot-messages">
                            <div class="chatbot-message ai">
                                <div class="chatbot-message-content">
                                    ${this.config.welcome_message || 'Hello! How can I help you today?'}
                                </div>
                            </div>
                        </div>
                        <!-- Image Preview -->
                        <div class="chatbot-image-preview" id="chatbot-image-preview">
                            <img id="chatbot-preview-img" src="" alt="Preview">
                            <div class="chatbot-image-preview-info">
                                <span id="chatbot-preview-name"></span>
                                <button class="chatbot-image-preview-remove" id="chatbot-preview-remove">Remove</button>
                            </div>
                        </div>
                        <!-- Voice Recording -->
                        <div class="chatbot-voice-recording" id="chatbot-voice-recording">
                            <div class="chatbot-voice-recording-dot"></div>
                            <div class="chatbot-voice-recording-time" id="chatbot-recording-time">00:00</div>
                            <div class="chatbot-voice-recording-controls">
                                <button class="chatbot-voice-btn-stop" id="chatbot-voice-stop">Stop & Send</button>
                                <button class="chatbot-voice-btn-cancel" id="chatbot-voice-cancel">Cancel</button>
                            </div>
                        </div>
                        <div class="chatbot-widget-input-area">
                            <!-- Emoji Picker -->
                            <div class="chatbot-emoji-picker" id="chatbot-emoji-picker">
                                <div class="chatbot-emoji-picker-header">
                                    <div class="chatbot-emoji-picker-title">Emojis</div>
                                    <button class="chatbot-emoji-picker-close" id="chatbot-emoji-close">&times;</button>
                                </div>
                                <div class="chatbot-emoji-grid" id="chatbot-emoji-grid"></div>
                            </div>
                            <div class="chatbot-widget-attachments">
                                ${this.config.emoji_support ? `
                                    <button class="chatbot-widget-attachment-btn" id="chatbot-emoji-btn" title="Add emoji">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                                        </svg>
                                    </button>
                                ` : ''}
                                ${this.config.image_support ? `
                                    <button class="chatbot-widget-attachment-btn" id="chatbot-image-btn" title="Upload image">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                            <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                                        </svg>
                                    </button>
                                    <input type="file" class="chatbot-file-input" id="chatbot-image-input" accept="image/*">
                                ` : ''}
                                ${this.config.voice_support ? `
                                    <button class="chatbot-widget-attachment-btn" id="chatbot-voice-btn" title="Voice message">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                            <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                                            <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                                        </svg>
                                    </button>
                                ` : ''}
                            </div>
                            <input type="text" class="chatbot-widget-input" id="chatbot-input" placeholder="Type a message...">
                            <button class="chatbot-widget-send" id="chatbot-send">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.head.insertAdjacentHTML('beforeend', styles);
            document.body.insertAdjacentHTML('beforeend', html);

            // Populate emoji picker if emoji support is enabled
            if (this.config.emoji_support) {
                this.populateEmojiPicker();
            }
        }

        populateEmojiPicker() {
            const emojiGrid = document.getElementById('chatbot-emoji-grid');
            if (!emojiGrid) return;

            // Comprehensive emoji library
            const emojis = [
                // Smileys & Emotion
                '😀', '😃', '😄', '😁', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘',
                '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳', '😏', '😒',
                '😞', '😔', '😟', '😕', '🙁', '😣', '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠', '😡', '🤬',
                '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗', '🤔', '🤭', '🤫', '🤥', '😶', '😐',
                '😑', '😬', '🙄', '😯', '😦', '😧', '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐', '🥴', '🤢',
                // Gestures & Body Parts
                '👍', '👎', '👊', '✊', '🤛', '🤜', '🤞', '✌️', '🤟', '🤘', '👌', '🤌', '🤏', '👈', '👉', '👆',
                '👇', '☝️', '✋', '🤚', '🖐️', '🖖', '👋', '🤙', '💪', '🦾', '🖕', '✍️', '🙏', '🦶', '🦵', '🦿',
                '👂', '🦻', '👃', '🧠', '🫀', '🫁', '🦷', '🦴', '👀', '👁️', '👅', '👄', '💋', '🩸',
                // Hearts & Symbols
                '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖',
                '💘', '💝', '💟', '☮️', '✝️', '☪️', '🕉️', '☸️', '✡️', '🔯', '🕎', '☯️', '☦️', '🛐', '⛎', '♈',
                // Nature & Weather
                '🌍', '🌎', '🌏', '🌐', '🗺️', '🧭', '🏔️', '⛰️', '🌋', '🗻', '🏕️', '🏖️', '🏜️', '🏝️', '🏞️', '🏟️',
                '🌅', '🌄', '🌠', '🎇', '🎆', '🌇', '🌆', '🏙️', '🌃', '🌌', '🌉', '🌁', '☀️', '🌤️', '⛅', '🌥️',
                '☁️', '🌦️', '🌧️', '⛈️', '🌩️', '🌨️', '❄️', '☃️', '⛄', '🌬️', '💨', '💧', '💦', '☔', '☂️', '🌊',
                // Animals
                '🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐨', '🐯', '🦁', '🐮', '🐷', '🐽', '🐸', '🐵',
                '🙈', '🙉', '🙊', '🐒', '🐔', '🐧', '🐦', '🐤', '🐣', '🐥', '🦆', '🦅', '🦉', '🦇', '🐺', '🐗',
                // Food & Drink
                '🍏', '🍎', '🍐', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🫐', '🍈', '🍒', '🍑', '🥭', '🍍', '🥥',
                '🥝', '🍅', '🍆', '🥑', '🥦', '🥬', '🥒', '🌶️', '🫑', '🌽', '🥕', '🫒', '🧄', '🧅', '🥔', '🍠',
                '🍞', '🥐', '🥖', '🫓', '🥨', '🥯', '🥞', '🧇', '🧀', '🍖', '🍗', '🥩', '🥓', '🍔', '🍟', '🍕',
                '🌭', '🥪', '🌮', '🌯', '🫔', '🥙', '🧆', '🥚', '🍳', '🥘', '🍲', '🫕', '🥣', '🥗', '🍿', '🧈',
                '☕', '🍵', '🧃', '🥤', '🧋', '🍶', '🍺', '🍻', '🍷', '🥂', '🍸', '🍹', '🧉', '🍾', '🧊', '🥄',
                // Activities & Objects
                '⚽', '🏀', '🏈', '⚾', '🥎', '🎾', '🏐', '🏉', '🥏', '🎱', '🪀', '🏓', '🏸', '🏒', '🏑', '🥍',
                '🎯', '🎮', '🕹️', '🎰', '🎲', '🧩', '🎭', '🎨', '🧵', '🪡', '🧶', '🪢', '👓', '🕶️', '🥽', '🥼',
                '💼', '🎒', '🧳', '👜', '👝', '💍', '💎', '🔊', '📢', '📣', '📯', '🔔', '🎵', '🎶', '🎤', '🎧',
                '📻', '🎷', '🪗', '🎸', '🎹', '🎺', '🎻', '🪕', '🥁', '🪘', '📱', '📲', '☎️', '📞', '📟', '📠',
                // Symbols & Flags
                '✅', '❌', '⭕', '🔴', '🟠', '🟡', '🟢', '🔵', '🟣', '🟤', '⚫', '⚪', '🟥', '🟧', '🟨', '🟩',
                '🟦', '🟪', '🟫', '⬛', '⬜', '💯', '🔥', '⚡', '💥', '✨', '🌟', '⭐', '💫', '🎉', '🎊', '🎈',
                '🎁', '🎀', '🏆', '🥇', '🥈', '🥉', '🏅', '🎖️', '🏵️', '🎗️', '🎫', '🎟️', '🎪', '🎭', '🎬', '🎨'
            ];

            emojis.forEach(emoji => {
                const button = document.createElement('button');
                button.className = 'chatbot-emoji-item';
                button.textContent = emoji;
                button.onclick = () => this.insertEmoji(emoji);
                emojiGrid.appendChild(button);
            });
        }

        attachEventListeners() {
            document.getElementById('chatbot-toggle').addEventListener('click', () => this.toggleWidget());
            document.getElementById('chatbot-close').addEventListener('click', () => this.closeWidget());
            document.getElementById('chatbot-send').addEventListener('click', () => this.sendMessage());
            document.getElementById('chatbot-input').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.sendMessage();
            });

            // Emoji button
            if (this.config.emoji_support) {
                const emojiBtn = document.getElementById('chatbot-emoji-btn');
                if (emojiBtn) {
                    emojiBtn.addEventListener('click', () => this.showEmojiPicker());
                }
            }

            // Image upload button
            if (this.config.image_support) {
                const imageBtn = document.getElementById('chatbot-image-btn');
                const imageInput = document.getElementById('chatbot-image-input');
                if (imageBtn && imageInput) {
                    imageBtn.addEventListener('click', () => imageInput.click());
                    imageInput.addEventListener('change', (e) => this.handleImageUpload(e));
                }
            }

            // Voice button
            if (this.config.voice_support) {
                const voiceBtn = document.getElementById('chatbot-voice-btn');
                if (voiceBtn) {
                    voiceBtn.addEventListener('click', () => this.toggleVoiceRecording());
                }
            }

            // Emoji picker close button
            const emojiClose = document.getElementById('chatbot-emoji-close');
            if (emojiClose) {
                emojiClose.addEventListener('click', () => this.hideEmojiPicker());
            }

            // Image preview remove button
            const previewRemove = document.getElementById('chatbot-preview-remove');
            if (previewRemove) {
                previewRemove.addEventListener('click', () => this.removeImagePreview());
            }

            // Voice recording controls
            const voiceStop = document.getElementById('chatbot-voice-stop');
            const voiceCancel = document.getElementById('chatbot-voice-cancel');
            if (voiceStop) {
                voiceStop.addEventListener('click', () => this.stopVoiceRecording());
            }
            if (voiceCancel) {
                voiceCancel.addEventListener('click', () => this.cancelVoiceRecording());
            }
        }

        showEmojiPicker() {
            const picker = document.getElementById('chatbot-emoji-picker');
            if (picker) {
                picker.classList.add('open');
            }
        }

        hideEmojiPicker() {
            const picker = document.getElementById('chatbot-emoji-picker');
            if (picker) {
                picker.classList.remove('open');
            }
        }

        insertEmoji(emoji) {
            const input = document.getElementById('chatbot-input');
            if (input) {
                input.value += emoji;
                input.focus();
                this.hideEmojiPicker();
            }
        }

        async handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            // File size validation (max 5MB)
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            if (file.size > maxSize) {
                alert('Image file is too large. Maximum size is 5MB.');
                event.target.value = '';
                return;
            }

            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select a valid image file.');
                event.target.value = '';
                return;
            }

            console.log('[Chatbot Widget] Image selected:', file.name, `(${(file.size / 1024 / 1024).toFixed(2)}MB)`);

            // Show image preview
            this.selectedImage = file;
            this.showImagePreview(file);

            // Reset file input
            event.target.value = '';
        }

        showImagePreview(file) {
            const preview = document.getElementById('chatbot-image-preview');
            const previewImg = document.getElementById('chatbot-preview-img');
            const previewName = document.getElementById('chatbot-preview-name');

            if (!preview || !previewImg || !previewName) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                previewImg.src = e.target.result;
                previewName.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)`;
                preview.classList.add('show');
            };
            reader.readAsDataURL(file);
        }

        removeImagePreview() {
            const preview = document.getElementById('chatbot-image-preview');
            const previewImg = document.getElementById('chatbot-preview-img');
            const previewName = document.getElementById('chatbot-preview-name');

            if (preview) preview.classList.remove('show');
            if (previewImg) previewImg.src = '';
            if (previewName) previewName.textContent = '';

            this.selectedImage = null;
        }

        async sendImageMessage() {
            if (!this.selectedImage) return;

            const file = this.selectedImage;
            console.log('[Chatbot Widget] Uploading image:', file.name);

            this.removeImagePreview();
            this.showTypingIndicator();

            try {
                const formData = new FormData();
                formData.append('chatbot_id', this.chatbotId);
                formData.append('visitor_id', this.visitorId);
                formData.append('image', file);
                formData.append('prompt', 'What is in this image?');

                const response = await fetch(`${this.apiUrl}/upload-image`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                this.hideTypingIndicator();

                if (data.success && data.message) {
                    this.conversationId = data.conversation_id;
                    this.addMessageToUI('visitor', `📷 Image: ${file.name}`);
                    this.addMessageToUI(data.message.sender_type, data.message.message);
                    this.lastMessageId = data.message.id;
                } else {
                    this.addMessageToUI('ai', 'Sorry, there was an error processing the image.');
                }
            } catch (error) {
                this.hideTypingIndicator();
                console.error('[Chatbot Widget] Failed to upload image:', error);
                this.addMessageToUI('ai', 'Sorry, there was an error uploading the image.');
            }
        }

        async toggleVoiceRecording() {
            if (this.isRecording) {
                await this.stopVoiceRecording();
            } else {
                await this.startVoiceRecording();
            }
        }

        async startVoiceRecording() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.mediaRecorder = new MediaRecorder(stream);
                this.audioChunks = [];

                this.mediaRecorder.ondataavailable = (event) => {
                    this.audioChunks.push(event.data);
                };

                this.mediaRecorder.onstop = async () => {
                    const audioBlob = new Blob(this.audioChunks, { type: 'audio/webm' });
                    await this.sendVoiceMessage(audioBlob);

                    // Stop all tracks
                    stream.getTracks().forEach(track => track.stop());
                };

                this.mediaRecorder.start();
                this.isRecording = true;
                this.recordingStartTime = Date.now();

                // Show recording UI
                const recordingUI = document.getElementById('chatbot-voice-recording');
                if (recordingUI) recordingUI.classList.add('active');

                // Update timer
                this.recordingInterval = setInterval(() => this.updateRecordingTime(), 1000);

                console.log('[Chatbot Widget] Voice recording started');
            } catch (error) {
                console.error('[Chatbot Widget] Failed to start recording:', error);
                alert('Unable to access microphone. Please check your browser permissions.');
            }
        }

        updateRecordingTime() {
            if (!this.isRecording || !this.recordingStartTime) return;

            const elapsed = Math.floor((Date.now() - this.recordingStartTime) / 1000);
            const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
            const seconds = (elapsed % 60).toString().padStart(2, '0');

            const timeDisplay = document.getElementById('chatbot-recording-time');
            if (timeDisplay) {
                timeDisplay.textContent = `${minutes}:${seconds}`;
            }
        }

        async stopVoiceRecording() {
            if (!this.isRecording || !this.mediaRecorder) return;

            this.mediaRecorder.stop();
            this.isRecording = false;

            // Clear timer
            if (this.recordingInterval) {
                clearInterval(this.recordingInterval);
                this.recordingInterval = null;
            }

            // Hide recording UI
            const recordingUI = document.getElementById('chatbot-voice-recording');
            if (recordingUI) recordingUI.classList.remove('active');

            console.log('[Chatbot Widget] Voice recording stopped');
        }

        cancelVoiceRecording() {
            if (!this.isRecording || !this.mediaRecorder) return;

            this.mediaRecorder.stop();
            this.isRecording = false;
            this.audioChunks = [];

            // Clear timer
            if (this.recordingInterval) {
                clearInterval(this.recordingInterval);
                this.recordingInterval = null;
            }

            // Hide recording UI
            const recordingUI = document.getElementById('chatbot-voice-recording');
            if (recordingUI) recordingUI.classList.remove('active');

            // Reset time display
            const timeDisplay = document.getElementById('chatbot-recording-time');
            if (timeDisplay) timeDisplay.textContent = '00:00';

            console.log('[Chatbot Widget] Voice recording cancelled');
        }

        async sendVoiceMessage(audioBlob) {
            const duration = Math.floor((Date.now() - this.recordingStartTime) / 1000);
            console.log('[Chatbot Widget] Sending voice message...', `Duration: ${duration}s`, `Size: ${(audioBlob.size / 1024).toFixed(2)}KB`);

            this.showTypingIndicator();

            try {
                // Upload voice to backend for transcription
                const formData = new FormData();
                formData.append('audio', audioBlob, 'voice.webm');
                formData.append('chatbot_id', this.chatbotId);
                formData.append('visitor_id', this.visitorId);

                const response = await fetch(`${this.apiUrl}/voice-message`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                console.log('[Chatbot Widget] Voice response:', data);
                this.hideTypingIndicator();

                if (data.success) {
                    this.conversationId = data.conversation_id;

                    // Show voice message with transcription
                    const transcription = data.transcription || 'Voice message';
                    this.addMessageToUI('visitor', `🎤 ${transcription} (${duration}s)`);

                    // Show AI response
                    if (data.message) {
                        this.addMessageToUI(data.message.sender_type, data.message.message);
                        this.lastMessageId = data.message.id;
                    }
                } else {
                    console.error('[Chatbot Widget] Voice upload failed:', data);
                    this.addMessageToUI('ai', 'Sorry, there was an error processing the voice message.');
                }

                // Reset time display
                const timeDisplay = document.getElementById('chatbot-recording-time');
                if (timeDisplay) timeDisplay.textContent = '00:00';

            } catch (error) {
                this.hideTypingIndicator();
                console.error('[Chatbot Widget] Failed to send voice message:', error);
                this.addMessageToUI('ai', 'Sorry, there was an error with the voice message.');

                // Reset time display
                const timeDisplay = document.getElementById('chatbot-recording-time');
                if (timeDisplay) timeDisplay.textContent = '00:00';
            }
        }

        toggleWidget() {
            this.isOpen = !this.isOpen;
            const window = document.getElementById('chatbot-window');
            if (this.isOpen) {
                window.classList.add('open');
            } else {
                window.classList.remove('open');
            }
        }

        closeWidget() {
            this.isOpen = false;
            document.getElementById('chatbot-window').classList.remove('open');
        }

        async sendMessage() {
            // Check if there's an image to send
            if (this.selectedImage) {
                await this.sendImageMessage();
                return;
            }

            const input = document.getElementById('chatbot-input');
            const message = input.value.trim();

            if (!message) return;

            this.addMessageToUI('visitor', message);
            input.value = '';

            this.showTypingIndicator();

            try {
                const payload = {
                    chatbot_id: this.chatbotId,
                    visitor_id: this.visitorId,
                    message: message,
                    page_url: window.location.href,
                    referrer: document.referrer || null
                };

                console.log('[Chatbot Widget] Sending message with payload:', payload);

                const response = await fetch(`${this.apiUrl}/message`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                console.log('[Chatbot Widget] Message response:', data);
                this.hideTypingIndicator();

                if (data.success && data.message) {
                    this.conversationId = data.conversation_id;
                    this.addMessageToUI(data.message.sender_type, data.message.message);
                    this.lastMessageId = data.message.id;
                } else if (data.errors) {
                    console.error('[Chatbot Widget] Validation errors:', data.errors);
                    this.addMessageToUI('ai', 'Sorry, there was an error processing your message. Please try again.');
                }
            } catch (error) {
                this.hideTypingIndicator();
                console.error('[Chatbot Widget] Failed to send message:', error);
                this.addMessageToUI('ai', 'Sorry, there was an error. Please try again later.');
            }
        }

        addMessageToUI(senderType, message) {
            const messagesContainer = document.getElementById('chatbot-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `chatbot-message ${senderType}`;
            messageDiv.innerHTML = `
                <div class="chatbot-message-content">${this.escapeHtml(message)}</div>
            `;
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        showTypingIndicator() {
            const messagesContainer = document.getElementById('chatbot-messages');
            const indicator = document.createElement('div');
            indicator.className = 'chatbot-message ai';
            indicator.id = 'typing-indicator';
            indicator.innerHTML = `
                <div class="chatbot-typing-indicator">
                    <span></span><span></span><span></span>
                </div>
            `;
            messagesContainer.appendChild(indicator);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        hideTypingIndicator() {
            const indicator = document.getElementById('typing-indicator');
            if (indicator) indicator.remove();
        }

        async startPolling() {
            setInterval(async () => {
                if (this.conversationId) {
                    await this.pollNewMessages();
                }
            }, 5000);
        }

        async pollNewMessages() {
            try {
                const response = await fetch(`${this.apiUrl}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        chatbot_id: this.chatbotId,
                        visitor_id: this.visitorId,
                        last_message_id: this.lastMessageId
                    })
                });

                const data = await response.json();
                if (data.success && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        if (msg.id !== this.lastMessageId) {
                            this.addMessageToUI(msg.sender_type, msg.message);
                            this.lastMessageId = msg.id;
                        }
                    });
                }
            } catch (error) {
                console.error('Failed to poll messages:', error);
            }
        }

        getOrCreateVisitorId() {
            let visitorId = localStorage.getItem('chatbot_visitor_id');
            if (!visitorId) {
                visitorId = 'visitor_' + Math.random().toString(36).substring(2, 15);
                localStorage.setItem('chatbot_visitor_id', visitorId);
            }
            return visitorId;
        }

        escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    }

    // Capture script reference immediately (before DOMContentLoaded)
    const currentScript = document.currentScript || document.querySelector('script[data-chatbot-id]');

    // Auto-initialize from script tag
    function initializeWidget() {
        console.log('[Chatbot Widget] Initializing...');
        const script = currentScript || document.querySelector('script[data-chatbot-id]');
        console.log('[Chatbot Widget] Script element:', script);

        if (script) {
            const chatbotId = script.getAttribute('data-chatbot-id');
            console.log('[Chatbot Widget] Chatbot ID:', chatbotId);

            if (chatbotId) {
                const apiUrl = script.getAttribute('data-api-url') || '/api/chatbot';
                console.log('[Chatbot Widget] API URL:', apiUrl);

                new ChatbotWidget({
                    chatbotId: chatbotId,
                    apiUrl: apiUrl
                });
                console.log('[Chatbot Widget] Widget instance created');
            } else {
                console.error('[Chatbot Widget] No chatbot-id attribute found');
            }
        } else {
            console.error('[Chatbot Widget] No script element found with data-chatbot-id attribute');
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        window.addEventListener('DOMContentLoaded', initializeWidget);
    } else {
        // DOM already loaded
        initializeWidget();
    }

    // Export for manual initialization
    window.ChatbotWidget = ChatbotWidget;
})();
