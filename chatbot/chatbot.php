<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>



<meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
<div class="chatbot-widget">

    <!-- Floating Button -->
    <button id="chatToggle" class="chat-toggle" type="button">
        💬
    </button>

    <!-- Chat Window -->
    <div id="chatWindow" class="chat-window">

        <div class="chat-header">

            <div class="bot-info">
                <div class="bot-avatar">🤖</div>

                <div>
                    <h3>OntimeoceanIT Assistant</h3>

                    <span>
                        <i></i> Online
                    </span>
                </div>
            </div>

            <button id="closeChat" class="close-btn" type="button">
                ×
            </button>

        </div>


        <div id="chatMessages" class="chat-messages">

            <div class="message bot-message">

                <div class="message-avatar">
                    🤖
                </div>

                <div class="message-content">

                    <div class="message-name">
                        OntimeoceanIT Assistant
                    </div>

                    <div class="message-text">
                        Hello! 👋<br>
                        Welcome to OntimeoceanIT Solutions.<br>
                        How can I help you today?
                    </div>

                    <div class="message-time">
                        Just now
                    </div>

                </div>

            </div>

        </div>


        <div class="chat-input-area">

            <div class="input-wrapper">

                <input
                    type="text"
                    id="messageInput"
                    placeholder="Type your message..."
                    maxlength="1000"
                    autocomplete="off"
                >

                <button
                    id="sendButton"
                    class="send-btn"
                    type="button"
                >
                    ➤
                </button>

            </div>

            <div class="chat-footer">
                Powered by OntimeoceanIT Solutions
            </div>

        </div>

    </div>

</div>