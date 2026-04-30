<!-- AI Assistant Modal -->
<style>
.ai-chat-button {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    cursor: pointer;
    z-index: 1000;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.ai-chat-button:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(102, 126, 234, 0.5);
}

.ai-chat-window {
    position: fixed;
    bottom: 100px;
    right: 30px;
    width: 350px;
    height: 500px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    z-index: 1001;
    display: none;
    flex-direction: column;
    overflow: hidden;
}

.ai-chat-window.active {
    display: flex;
}

.ai-chat-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ai-chat-header h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.ai-chat-header h5 i {
    margin-right: 8px;
}

.ai-chat-close {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.3s;
}

.ai-chat-close:hover {
    opacity: 1;
}

.ai-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f8f9fa;
}

.ai-message, .user-message {
    margin-bottom: 15px;
    max-width: 80%;
    clear: both;
}

.ai-message {
    float: left;
}

.user-message {
    float: right;
}

.message-content {
    padding: 12px 15px;
    border-radius: 18px;
    word-wrap: break-word;
    font-size: 14px;
    line-height: 1.5;
}

.ai-message .message-content {
    background: white;
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.user-message .message-content {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom-right-radius: 4px;
}

.message-time {
    font-size: 10px;
    color: #999;
    margin-top: 4px;
    padding: 0 5px;
}

.ai-chat-input {
    padding: 15px;
    background: white;
    border-top: 1px solid #e9ecef;
    display: flex;
    gap: 10px;
}

.ai-chat-input input {
    flex: 1;
    border: 2px solid #e9ecef;
    border-radius: 25px;
    padding: 10px 15px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.3s;
}

.ai-chat-input input:focus {
    border-color: #667eea;
}

.ai-chat-input button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    cursor: pointer;
    transition: transform 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-chat-input button:hover {
    transform: scale(1.05);
}

.ai-chat-input button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.typing-indicator {
    padding: 10px 15px;
    color: #999;
    font-style: italic;
    font-size: 12px;
}

.context-buttons {
    padding: 10px 15px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.context-btn {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 15px;
    padding: 5px 12px;
    font-size: 12px;
    color: #667eea;
    cursor: pointer;
    transition: all 0.3s;
}

.context-btn:hover {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.context-btn i {
    margin-right: 4px;
}
</style>

<div class="ai-chat-button" onclick="toggleChat()">
    <i class="fas fa-robot"></i>
</div>

<div class="ai-chat-window" id="aiChatWindow">
    <div class="ai-chat-header">
        <h5><i class="fas fa-robot"></i> HR Connect Assistant</h5>
        <button class="ai-chat-close" onclick="toggleChat()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="context-buttons">
        <button class="context-btn" onclick="sendContextMessage('resume')">
            <i class="fas fa-file-alt"></i> Резюме
        </button>
        <button class="context-btn" onclick="sendContextMessage('vacancy')">
            <i class="fas fa-briefcase"></i> Вакансия
        </button>
        <button class="context-btn" onclick="sendContextMessage('cover_letter')">
            <i class="fas fa-envelope"></i> Хат
        </button>
        <button class="context-btn" onclick="sendContextMessage('interview')">
            <i class="fas fa-users"></i> Сұхбат
        </button>
    </div>
    
    <div class="ai-chat-messages" id="chatMessages">
        <div class="ai-message">
            <div class="message-content">
                Сәлеметсіз бе! Мен HR Connect көмекшісімін. 
                Сізге қандай көмек керек? 
                Резюме жазу, вакансия сипаттамасы немесе басқа сұрақтар бойынша көмектесе аламын.
            </div>
            <div class="message-time">Жаңа ғана</div>
        </div>
    </div>
    
    <div class="typing-indicator" id="typingIndicator" style="display: none;">
        <i class="fas fa-ellipsis-h"></i> Жазып жатыр...
    </div>
    
    <div class="ai-chat-input">
        <input type="text" id="chatInput" placeholder="Хабарламаңызды жазыңыз..." onkeypress="if(event.key === 'Enter') sendMessage()">
        <button onclick="sendMessage()" id="sendButton">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>

<script src="js/ai_chat.js"></script>