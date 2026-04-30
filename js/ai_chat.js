// AI Chat functionality
let isOpen = false;

function toggleChat() {
    const chatWindow = document.getElementById('aiChatWindow');
    isOpen = !isOpen;
    chatWindow.classList.toggle('active');
    
    if (isOpen) {
        document.getElementById('chatInput').focus();
    }
}

async function sendMessage(context = 'general') {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Добавляем сообщение пользователя
    addMessage(message, 'user');
    input.value = '';
    
    // Показываем индикатор печати
    document.getElementById('typingIndicator').style.display = 'block';
    document.getElementById('sendButton').disabled = true;
    
    try {
        const response = await fetch('ajax/ai_chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `message=${encodeURIComponent(message)}&context=${context}`
        });
        
        const data = await response.json();
        
        // Убираем индикатор печати
        document.getElementById('typingIndicator').style.display = 'none';
        document.getElementById('sendButton').disabled = false;
        
        if (data.success) {
            addMessage(data.response, 'ai');
        } else {
            // Показываем детальную информацию об ошибке если есть
            let errorMsg = data.response || 'Қате орын алды. Қайталап көріңіз.';
            if (data.debug) {
                console.error('API Error:', data.debug);
                errorMsg += '\n\nҚате туралы: ' + data.debug;
            }
            addMessage(errorMsg, 'ai');
        }
    } catch (error) {
        document.getElementById('typingIndicator').style.display = 'none';
        document.getElementById('sendButton').disabled = false;
        addMessage('Қате орын алды. Интернет байланысын тексеріңіз.', 'ai');
    }
}

function sendContextMessage(context) {
    let message = '';
    
    switch(context) {
        case 'resume':
            message = 'Резюме жазуға көмектесіңіз. Негізгі бөлімдер қандай болуы керек?';
            break;
        case 'vacancy':
            message = 'Вакансия сипаттамасын жазуға көмектесіңіз. Қандай мәліметтерді қосу керек?';
            break;
        case 'cover_letter':
            message = 'Сүйемелдеу хатын жазуға көмектесіңіз. Қалай жазу керек?';
            break;
        case 'interview':
            message = 'Сұхбатқа қалай дайындалу керек? Кеңес беріңіз.';
            break;
        default:
            return;
    }
    
    document.getElementById('chatInput').value = message;
    sendMessage(context);
}

function addMessage(text, type) {
    const messagesDiv = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `${type}-message`;
    
    const now = new Date();
    const time = now.getHours().toString().padStart(2, '0') + ':' + 
                 now.getMinutes().toString().padStart(2, '0');
    
    messageDiv.innerHTML = `
        <div class="message-content">${text.replace(/\n/g, '<br>')}</div>
        <div class="message-time">${time}</div>
    `;
    
    messagesDiv.appendChild(messageDiv);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

// Закрытие чата по ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && isOpen) {
        toggleChat();
    }
});