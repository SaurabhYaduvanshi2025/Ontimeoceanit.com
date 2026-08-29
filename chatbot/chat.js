document.addEventListener("DOMContentLoaded", function () {

    const chatToggle = document.getElementById("chatToggle");
    const chatWindow = document.getElementById("chatWindow");
    const closeChat = document.getElementById("closeChat");

    const messageInput = document.getElementById("messageInput");
    const sendButton = document.getElementById("sendButton");
    const chatMessages = document.getElementById("chatMessages");


    // ========================================
    // OPEN CHATBOT
    // ========================================

    chatToggle.addEventListener("click", function () {
        chatWindow.style.display = "flex";
    });


    // ========================================
    // CLOSE CHATBOT
    // ========================================

    closeChat.addEventListener("click", function () {
        chatWindow.style.display = "none";
    });


    // ========================================
    // SEND MESSAGE
    // ========================================

    async function sendMessage() {

        const message = messageInput.value.trim();

        if (message === "") {
            return;
        }

        sendButton.disabled = true;


        // ========================================
        // SHOW USER MESSAGE
        // ========================================

        const messageDiv = document.createElement("div");

        messageDiv.className = "message user-message";

        messageDiv.innerHTML = `
            <div class="message-avatar">
                👤
            </div>

            <div class="message-content">

                <div class="message-name">
                    You
                </div>

                <div class="message-text"></div>

                <div class="message-time">
                    Just now
                </div>

            </div>
        `;

        messageDiv.querySelector(".message-text").textContent = message;

        chatMessages.appendChild(messageDiv);


        // Input clear
        messageInput.value = "";


        // ========================================
        // SHOW TYPING
        // ========================================

        const typingDiv = document.createElement("div");

        typingDiv.className = "message bot-message";
        typingDiv.id = "typingMessage";

        typingDiv.innerHTML = `
            <div class="message-avatar">
                🤖
            </div>

            <div class="message-content">

                <div class="message-name">
                    OntimeoceanIT Assistant
                </div>

                <div class="message-text">
                    Typing...
                </div>

            </div>
        `;

        chatMessages.appendChild(typingDiv);

        chatMessages.scrollTop = chatMessages.scrollHeight;


        try {

            // ========================================
            // CSRF TOKEN
            // ========================================

            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content');


            // ========================================
            // SEND MESSAGE TO PHP
            // ========================================

            const response = await fetch(
                "chatbot/api/save-message.php",
                {
                    method: "POST",

                    headers: {
                        "Content-Type": "application/json"
                    },

                    body: JSON.stringify({
                        message: message,
                        csrf_token: csrfToken
                    })
                }
            );


            const data = await response.json();


            // ========================================
            // PHP/API ERROR
            // ========================================

            if (!response.ok || !data.success) {

                throw new Error(
                    data.message || "Message could not be sent"
                );

            }


            // ========================================
            // REMOVE TYPING
            // ========================================

            const typingMessage =
                document.getElementById("typingMessage");

            if (typingMessage) {
                typingMessage.remove();
            }


            // ========================================
            // SHOW BOT RESPONSE
            // ========================================

            const botDiv = document.createElement("div");

            botDiv.className = "message bot-message";

            botDiv.innerHTML = `
                <div class="message-avatar">
                    🤖
                </div>

                <div class="message-content">

                    <div class="message-name">
                        OntimeoceanIT Assistant
                    </div>

                    <div class="message-text"></div>

                    <div class="message-time">
                        Just now
                    </div>

                </div>
            `;

            botDiv
                .querySelector(".message-text")
                .textContent = data.reply;

            chatMessages.appendChild(botDiv);

            chatMessages.scrollTop =
                chatMessages.scrollHeight;


        } catch (error) {

            console.error("Chat error:", error);


            // Typing remove on error
            const typingMessage =
                document.getElementById("typingMessage");

            if (typingMessage) {
                typingMessage.remove();
            }


            alert("Message Not Send");


        } finally {

            sendButton.disabled = false;

        }

    }


    // ========================================
    // SEND BUTTON
    // ========================================

    sendButton.addEventListener(
        "click",
        sendMessage
    );


    // ========================================
    // ENTER KEY
    // ========================================

    messageInput.addEventListener(
        "keydown",
        function (event) {

            if (event.key === "Enter") {

                event.preventDefault();

                sendMessage();

            }

        }
    );

});