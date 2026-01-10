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

            this.init();
        }

        async init() {
            await this.loadConfig();
            this.createWidget();
            this.attachEventListeners();
            this.startPolling();
        }

        async loadConfig() {
            try {
                const response = await fetch(`${this.apiUrl}/${this.chatbotId}/config`);
                const data = await response.json();
                if (data.success) {
                    this.config = data.config;
                }
            } catch (error) {
                console.error('Failed to load chatbot config:', error);
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
                    }
                    .chatbot-widget-send svg {
                        width: 20px;
                        height: 20px;
                        fill: white;
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
                        <div class="chatbot-widget-input-area">
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
        }

        attachEventListeners() {
            document.getElementById('chatbot-toggle').addEventListener('click', () => this.toggleWidget());
            document.getElementById('chatbot-close').addEventListener('click', () => this.closeWidget());
            document.getElementById('chatbot-send').addEventListener('click', () => this.sendMessage());
            document.getElementById('chatbot-input').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.sendMessage();
            });
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
            const input = document.getElementById('chatbot-input');
            const message = input.value.trim();

            if (!message) return;

            this.addMessageToUI('visitor', message);
            input.value = '';

            this.showTypingIndicator();

            try {
                const response = await fetch(`${this.apiUrl}/message`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        chatbot_id: this.chatbotId,
                        visitor_id: this.visitorId,
                        message: message,
                        page_url: window.location.href,
                        referrer: document.referrer
                    })
                });

                const data = await response.json();
                this.hideTypingIndicator();

                if (data.success && data.message) {
                    this.conversationId = data.conversation_id;
                    this.addMessageToUI(data.message.sender_type, data.message.message);
                    this.lastMessageId = data.message.id;
                }
            } catch (error) {
                this.hideTypingIndicator();
                console.error('Failed to send message:', error);
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
        const script = currentScript || document.querySelector('script[data-chatbot-id]');
        if (script) {
            const chatbotId = script.getAttribute('data-chatbot-id');
            if (chatbotId) {
                new ChatbotWidget({
                    chatbotId: chatbotId,
                    apiUrl: script.getAttribute('data-api-url') || '/api/chatbot'
                });
            }
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
