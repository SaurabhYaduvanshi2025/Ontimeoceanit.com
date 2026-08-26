<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ITWeb Assistant</title>

    <link rel="stylesheet" href="chat.css">
</head>

<body>

<div class="chat-wrapper">

    <!-- Chat Header -->
    <div class="chat-header">

        <div class="bot-info">

            <div class="bot-avatar">
                🤖
            </div>

            <div>
                <h3>ITWeb Assistant</h3>

                <div class="online-status">
                    <span></span>
                    Online
                </div>
            </div>

        </div>

        <button class="close-btn" id="closeChat">
            ×
        </button>

    </div>


    <!-- Chat Messages -->
    <div class="chat-messages" id="chatMessages">

        <div class="message bot-message">

            <div class="message-avatar">
                🤖
            </div>

            <div class="message-content">
                <div class="message-name">
                    ITWeb Assistant
                </div>

                <div class="message-text">
                    Hello! 👋<br>
                    Welcome to ITWeb Solutions.<br>
                    How can I help you today?
                </div>

                <div class="message-time">
                    Just now
                </div>
            </div>

        </div>

    </div>


    <!-- Input Area -->
    <div class="chat-input-area">

        <div class="input-wrapper">

            <input
                type="text"
                id="messageInput"
                placeholder="Type your message..."
                maxlength="1000"
                autocomplete="off"
            >

            <button id="sendButton" class="send-btn">
                ➤
            </button>

        </div>

        <div class="chat-footer">
            Powered by ITWeb Solutions
        </div>

    </div>

</div>


<script src="chat.js"></script>

</body>
</html>