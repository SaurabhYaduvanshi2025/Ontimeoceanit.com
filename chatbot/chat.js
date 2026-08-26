const messageInput = document.getElementById("messageInput");
const sendButton = document.getElementById("sendButton");
const chatMessages = document.getElementById("chatMessages");


function addUserMessage(message) {

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

            <div class="message-text">
                ${message}
            </div>

            <div class="message-time">
                Just now
            </div>

        </div>

    `;

    chatMessages.appendChild(messageDiv);

    chatMessages.scrollTop = chatMessages.scrollHeight;
}


function sendMessage() {

    const message = messageInput.value.trim();

    if (message === "") {
        return;
    }

    addUserMessage(message);

    messageInput.value = "";

    messageInput.focus();
}


sendButton.addEventListener("click", sendMessage);


messageInput.addEventListener("keydown", function(event) {

    if (event.key === "Enter") {

        sendMessage();

    }

});