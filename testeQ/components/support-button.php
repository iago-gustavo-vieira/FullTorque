<style>
.support-button {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #109349, #0d7a3a);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(16, 147, 73, 0.4);
    transition: all 0.3s;
    z-index: 999;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 4px 15px rgba(16, 147, 73, 0.4); }
    50% { box-shadow: 0 4px 20px rgba(16, 147, 73, 0.6); }
    100% { box-shadow: 0 4px 15px rgba(16, 147, 73, 0.4); }
}

.support-button:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(16, 147, 73, 0.5);
}

.theme-alemanha .support-button {
    background: linear-gradient(135deg, #FFCE00, #e6b800);
    box-shadow: 0 4px 15px rgba(255, 206, 0, 0.4);
}

.mini-chat {
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 350px;
    height: 450px;
    background-color: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    z-index: 998;
    display: none;
    overflow: hidden;
    border: 1px solid #e0e0e0;
}

.mini-chat.show {
    display: flex;
    animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.chat-header {
    padding: 15px 20px;
    background: linear-gradient(135deg, #109349, #0d7a3a);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.theme-alemanha .chat-header {
    background: linear-gradient(135deg, #FFCE00, #e6b800);
    color: #333;
}

.chat-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.chat-status {
    font-size: 12px;
    opacity: 0.9;
    display: flex;
    align-items: center;
}

.status-dot {
    width: 8px;
    height: 8px;
    background-color: #4ade80;
    border-radius: 50%;
    margin-right: 5px;
    animation: blink 1.5s infinite;
}

@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0.3; }
}

.close-chat {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    font-size: 18px;
    padding: 5px;
    border-radius: 50%;
    transition: all 0.3s;
}

.close-chat:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.chat-messages {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    background: linear-gradient(to bottom, #f8f9fa, #ffffff);
}

.message {
    margin-bottom: 15px;
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.message.system .message-content {
    background: linear-gradient(135deg, #f1f3f4, #e8eaed);
    color: #333;
    padding: 12px 15px;
    border-radius: 18px 18px 18px 5px;
    display: inline-block;
    max-width: 85%;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    position: relative;
}

.message.user .message-content {
    background: linear-gradient(135deg, #109349, #0d7a3a);
    color: white;
    padding: 12px 15px;
    border-radius: 18px 18px 5px 18px;
    display: inline-block;
    max-width: 85%;
    float: right;
    box-shadow: 0 2px 5px rgba(16, 147, 73, 0.3);
}

.theme-alemanha .message.user .message-content {
    background: linear-gradient(135deg, #FFCE00, #e6b800);
    color: #333;
}

.message-time {
    font-size: 11px;
    color: #999;
    margin-top: 5px;
    clear: both;
}

.quick-options {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}

.quick-option {
    background-color: #e3f2fd;
    color: #1976d2;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s;
    border: 1px solid #bbdefb;
}

.quick-option:hover {
    background-color: #1976d2;
    color: white;
    transform: translateY(-1px);
}

.typing-indicator {
    display: none;
    padding: 10px 15px;
    background-color: #f1f3f4;
    border-radius: 18px;
    margin-bottom: 10px;
    max-width: 80px;
}

.typing-dots {
    display: flex;
    gap: 3px;
}

.typing-dot {
    width: 6px;
    height: 6px;
    background-color: #999;
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing-dot:nth-child(2) { animation-delay: 0.2s; }
.typing-dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
}

.chat-input {
    padding: 15px;
    border-top: 1px solid #e0e0e0;
    display: flex;
    background-color: #fafafa;
}

.chat-input input {
    flex: 1;
    padding: 12px 15px;
    border: 1px solid #e0e0e0;
    border-radius: 25px;
    outline: none;
    font-size: 14px;
    transition: all 0.3s;
}

.chat-input input:focus {
    border-color: #109349;
    box-shadow: 0 0 0 3px rgba(16, 147, 73, 0.1);
}

.send-message {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #109349, #0d7a3a);
    color: white;
    border: none;
    margin-left: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    box-shadow: 0 2px 5px rgba(16, 147, 73, 0.3);
}

.send-message:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 10px rgba(16, 147, 73, 0.4);
}

.theme-alemanha .send-message {
    background: linear-gradient(135deg, #FFCE00, #e6b800);
    color: #333;
}

.chat-suggestions {
    padding: 10px 15px;
    background-color: #f8f9fa;
    border-top: 1px solid #e0e0e0;
}

.suggestion-title {
    font-size: 12px;
    color: #666;
    margin-bottom: 8px;
    font-weight: 500;
}

.suggestions-list {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.suggestion-item {
    background-color: white;
    color: #333;
    padding: 5px 10px;
    border-radius: 12px;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.3s;
    border: 1px solid #e0e0e0;
}

.suggestion-item:hover {
    background-color: #109349;
    color: white;
    border-color: #109349;
}

@media (max-width: 768px) {
    .mini-chat {
        width: calc(100vw - 40px);
        right: 20px;
        left: 20px;
        height: 400px;
    }
    
    .support-button {
        width: 55px;
        height: 55px;
    }
}
</style>

<!-- Botão de suporte ao cliente -->
<div class="support-button" onclick="toggleChat()" title="Precisa de ajuda? Clique aqui!">
    <i class="fas fa-headset"></i>
</div>

<!-- Mini chat de suporte -->
<div class="mini-chat" id="miniChat">
    <div class="chat-header">
        <div>
            <h3>🏁 FULL TORQUE</h3>
            <div class="chat-status">
                <div class="status-dot"></div>
                Online - Resposta rápida
            </div>
        </div>
        <button class="close-chat" onclick="toggleChat()" title="Fechar chat">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="chat-messages" id="chatMessages">
        <div class="message system">
            <div class="message-content">
                👋 Olá! Sou o assistente virtual da Auto Service.<br><br>
                Estou aqui para ajudar com:
                <br>• Agendamentos
                <br>• Orçamentos
                <br>• Dúvidas sobre serviços
                <br>• Status do seu veículo
                <br><br>
                Como posso ajudar você hoje?
            </div>
            <div class="message-time">Agora</div>
            <div class="quick-options">
                <div class="quick-option" onclick="selectQuickOption('agendar')">📅 Agendar Serviço</div>
                <div class="quick-option" onclick="selectQuickOption('orcamento')">💰 Solicitar Orçamento</div>
                <div class="quick-option" onclick="selectQuickOption('status')">🔍 Status do Veículo</div>
                <div class="quick-option" onclick="selectQuickOption('servicos')">🔧 Nossos Serviços</div>
            </div>
        </div>
    </div>
    
    <div class="typing-indicator" id="typingIndicator">
        <div class="typing-dots">
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        </div>
    </div>
    
    <div class="chat-suggestions">
        <div class="suggestion-title">Sugestões rápidas:</div>
        <div class="suggestions-list">
            <div class="suggestion-item" onclick="sendQuickMessage('Qual o horário de funcionamento?')">⏰ Horários</div>
            <div class="suggestion-item" onclick="sendQuickMessage('Onde vocês ficam localizados?')">📍 Localização</div>
            <div class="suggestion-item" onclick="sendQuickMessage('Quais formas de pagamento aceitam?')">💳 Pagamento</div>
            <div class="suggestion-item" onclick="sendQuickMessage('Preciso falar com um atendente')">👨‍💼 Atendente</div>
        </div>
    </div>
    
    <div class="chat-input">
        <input type="text" placeholder="Digite sua mensagem..." id="chatMessageInput" maxlength="500">
        <button class="send-message" onclick="sendMessage()" title="Enviar mensagem">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>

<script>
let chatHistory = [];
let isTyping = false;

// Base de conhecimento do chatbot
const knowledgeBase = {
    'horario': {
        keywords: ['horario', 'funcionamento', 'aberto', 'fecha', 'abre', 'horários'],
        response: '🕐 Nossos horários de funcionamento:<br><br>📅 Segunda a Sexta: 8h às 18h<br>📅 Sábado: 8h às 12h<br>📅 Domingo: Fechado<br><br>Para emergências, temos plantão 24h! 🚨'
    },
    'localizacao': {
        keywords: ['onde', 'localização', 'endereço', 'fica', 'localizada', 'local'],
        response: '📍 Estamos localizados em:<br><br>🏢 Rua das Oficinas, 123<br>🏙️ Centro - Sua Cidade<br>📞 Telefone: (11) 99999-9999<br><br>Temos estacionamento gratuito! 🅿️'
    },
    'pagamento': {
        keywords: ['pagamento', 'pagar', 'cartão', 'dinheiro', 'pix', 'parcelamento'],
        response: '💳 Formas de pagamento aceitas:<br><br>💰 Dinheiro<br>💳 Cartão de Crédito/Débito<br>📱 PIX<br>🏦 Transferência bancária<br>📄 Parcelamento em até 12x<br><br>Pagamento facilitado para você! ✨'
    },
    'servicos': {
        keywords: ['serviços', 'serviço', 'fazem', 'oferecem', 'tipos'],
        response: '🔧 Nossos principais serviços:<br><br>🛠️ Manutenção preventiva<br>⚙️ Reparos em geral<br>🔩 Troca de óleo e filtros<br>🚗 Diagnóstico computadorizado<br>🛞 Alinhamento e balanceamento<br>🔋 Sistema elétrico<br>❄️ Ar condicionado<br><br>Qualidade garantida! 🏆'
    },
    'orcamento': {
        keywords: ['orçamento', 'preço', 'valor', 'custa', 'quanto'],
        response: '💰 Para solicitar um orçamento:<br><br>1️⃣ Faça login no sistema<br>2️⃣ Vá em "Solicitar Orçamento"<br>3️⃣ Descreva o problema<br>4️⃣ Anexe fotos se possível<br><br>📞 Ou ligue: (11) 99999-9999<br><br>Orçamento gratuito e sem compromisso! 🆓'
    },
    'agendamento': {
        keywords: ['agendar', 'agendamento', 'marcar', 'horário'],
        response: '📅 Para agendar seu atendimento:<br><br>1️⃣ Acesse "Solicitar Diagnóstico"<br>2️⃣ Escolha seu mecânico<br>3️⃣ Selecione data e horário<br>4️⃣ Confirme o agendamento<br><br>📱 Você receberá confirmação por SMS!<br><br>Rápido e fácil! ⚡'
    },
    'emergencia': {
        keywords: ['emergência', 'urgente', 'socorro', 'guincho', 'pane'],
        response: '🚨 EMERGÊNCIA 24H:<br><br>📞 Ligue: (11) 99999-9999<br>🚗 Guincho disponível<br>⚡ Atendimento rápido<br>🛠️ Mecânico no local<br><br>Estamos aqui para ajudar! 💪'
    },
    'garantia': {
        keywords: ['garantia', 'prazo', 'cobertura'],
        response: '🛡️ Nossa garantia:<br><br>⏰ 90 dias para serviços<br>📅 1 ano para peças originais<br>📋 Certificado de garantia<br>🔄 Revisão gratuita<br><br>Sua tranquilidade é nossa prioridade! ✅'
    }
};

function toggleChat() {
    const chat = document.getElementById('miniChat');
    const isVisible = chat.classList.contains('show');
    
    if (isVisible) {
        chat.classList.remove('show');
    } else {
        chat.classList.add('show');
        // Foco no input quando abrir
        setTimeout(() => {
            document.getElementById('chatMessageInput').focus();
        }, 300);
    }
}

function sendMessage() {
    const input = document.getElementById('chatMessageInput');
    const message = input.value.trim();
    
    if (message && !isTyping) {
        addUserMessage(message);
        input.value = '';
        
        // Processa resposta inteligente
        setTimeout(() => {
            processIntelligentResponse(message);
        }, 800);
    }
}

function addUserMessage(message) {
    const messagesContainer = document.getElementById('chatMessages');
    const userMessage = document.createElement('div');
    userMessage.className = 'message user';
    userMessage.innerHTML = `
        <div class="message-content">${escapeHtml(message)}</div>
        <div class="message-time" style="text-align: right;">${getCurrentTime()}</div>
    `;
    messagesContainer.appendChild(userMessage);
    scrollToBottom();
    
    // Adiciona ao histórico
    chatHistory.push({type: 'user', message: message, time: new Date()});
}

function addSystemMessage(message, hasOptions = false) {
    const messagesContainer = document.getElementById('chatMessages');
    const systemMessage = document.createElement('div');
    systemMessage.className = 'message system';
    
    let optionsHtml = '';
    if (hasOptions) {
        optionsHtml = `
            <div class="quick-options">
                <div class="quick-option" onclick="sendQuickMessage('Quero agendar um horário')">📅 Agendar</div>
                <div class="quick-option" onclick="sendQuickMessage('Preciso de um orçamento')">💰 Orçamento</div>
                <div class="quick-option" onclick="sendQuickMessage('Falar com atendente')">👨‍💼 Atendente</div>
            </div>
        `;
    }
    
    systemMessage.innerHTML = `
        <div class="message-content">${message}</div>
        <div class="message-time">${getCurrentTime()}</div>
        ${optionsHtml}
    `;
    messagesContainer.appendChild(systemMessage);
    scrollToBottom();
    
    // Adiciona ao histórico
    chatHistory.push({type: 'system', message: message, time: new Date()});
}

function processIntelligentResponse(userMessage) {
    showTypingIndicator();
    
    const lowerMessage = userMessage.toLowerCase();
    let response = null;
    let hasOptions = false;
    
    // Verifica saudações
    if (lowerMessage.match(/\b(oi|olá|ola|hey|bom dia|boa tarde|boa noite)\b/)) {
        response = '👋 Olá! Seja bem-vindo(a) à Auto Service!<br><br>Como posso ajudar você hoje?';
        hasOptions = true;
    }
    // Verifica despedidas
    else if (lowerMessage.match(/\b(tchau|bye|obrigado|obrigada|valeu|até)\b/)) {
        response = '😊 Foi um prazer ajudar!<br><br>Qualquer dúvida, estaremos aqui.<br><br>Tenha um ótimo dia! 🌟';
    }
    // Verifica pedido de atendente
    else if (lowerMessage.match(/\b(atendente|humano|pessoa|funcionário)\b/)) {
        response = '👨‍💼 Vou conectar você com um atendente!<br><br>📞 Ligue: (11) 99999-9999<br>📧 Email: contato@autoservice.com<br><br>Ou continue aqui que um atendente responderá em breve! ⏰';
    }
    // Procura na base de conhecimento
    else {
        for (const [key, data] of Object.entries(knowledgeBase)) {
            if (data.keywords.some(keyword => lowerMessage.includes(keyword))) {
                response = data.response;
                break;
            }
        }
    }
    
    // Resposta padrão se não encontrou correspondência
    if (!response) {
        const defaultResponses = [
            '🤔 Interessante! Deixe-me ajudar você com isso.<br><br>Para informações mais específicas, recomendo:<br><br>📞 Ligar: (11) 99999-9999<br>📧 Email: contato@autoservice.com',
            '💭 Entendi sua dúvida! Para uma resposta mais precisa, que tal falar diretamente com nossa equipe?<br><br>📞 (11) 99999-9999<br><br>Ou me conte mais detalhes que posso tentar ajudar! 😊',
            '🔍 Vou verificar isso para você!<br><br>Enquanto isso, você pode:<br>📞 Ligar para (11) 99999-9999<br>📧 Enviar email para contato@autoservice.com<br><br>Nossa equipe terá a resposta exata! 👍'
        ];
        response = defaultResponses[Math.floor(Math.random() * defaultResponses.length)];
        hasOptions = true;
    }
    
    setTimeout(() => {
        hideTypingIndicator();
        addSystemMessage(response, hasOptions);
    }, 1500);
}

function selectQuickOption(option) {
    const responses = {
        'agendar': 'Para agendar seu atendimento, acesse "Solicitar Diagnóstico" no menu principal. Lá você pode escolher o mecânico, data e horário!',
        'orcamento': 'Para solicitar um orçamento, vá em "Solicitar Orçamento" no menu. Descreva o problema e anexe fotos se possível!',
        'status': 'Para verificar o status do seu veículo, acesse "Meus Diagnósticos" no menu principal.',
        'servicos': 'Oferecemos manutenção preventiva, reparos, diagnóstico computadorizado, troca de óleo, alinhamento e muito mais!'
    };
    
    addSystemMessage(responses[option] || 'Como posso ajudar você?', true);
}

function sendQuickMessage(message) {
    document.getElementById('chatMessageInput').value = message;
    sendMessage();
}

function showTypingIndicator() {
    isTyping = true;
    document.getElementById('typingIndicator').style.display = 'block';
    scrollToBottom();
}

function hideTypingIndicator() {
    isTyping = false;
    document.getElementById('typingIndicator').style.display = 'none';
}

function scrollToBottom() {
    const messagesContainer = document.getElementById('chatMessages');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function getCurrentTime() {
    return new Date().toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('chatMessageInput');
    
    // Enter para enviar
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    // Contador de caracteres
    input.addEventListener('input', function() {
        const remaining = 500 - this.value.length;
        if (remaining < 50) {
            this.style.borderColor = remaining < 10 ? '#e74c3c' : '#f39c12';
        } else {
            this.style.borderColor = '#e0e0e0';
        }
    });
});

// Fechar chat ao clicar fora
document.addEventListener('click', function(e) {
    const chat = document.getElementById('miniChat');
    const button = document.querySelector('.support-button');
    
    if (chat.classList.contains('show') && 
        !chat.contains(e.target) && 
        !button.contains(e.target)) {
        // Não fecha automaticamente para melhor UX
    }
});
</script>