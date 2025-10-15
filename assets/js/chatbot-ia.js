/**
 * JavaScript del Chatbot IA
 * Funcionalidad completa para la interfaz del chatbot
 */

(function($) {
    'use strict';

    /**
     * Clase principal del Chatbot IA
     */
    class ChatbotIA {
        constructor(options = {}) {
            this.options = {
                position: 'bottom-right',
                theme: 'default',
                size: 'medium',
                strings: {
                    loading: 'Cargando...',
                    error: 'Lo siento, ha ocurrido un error. Intenta de nuevo.',
                    emptyMessage: 'Por favor, escribe un mensaje.',
                    rateLimit: 'Has alcanzado el límite de mensajes. Espera un momento.',
                    apiError: 'Error en la conexión con la IA. Verifica la configuración.',
                    welcomeMessage: '¡Hola! Soy tu asistente de IA. ¿En qué puedo ayudarte hoy?',
                    typing: 'Escribiendo...',
                    online: 'En línea',
                    offline: 'Desconectado',
                    clearChat: '¿Estás seguro de que quieres limpiar la conversación?',
                    sendMessage: 'Enviar mensaje',
                    openChat: 'Abrir chat con IA',
                    closeChat: 'Cerrar chat',
                    minimizeChat: 'Minimizar chat',
                    clearConversation: 'Limpiar conversación',
                    writeMessage: 'Escribe tu mensaje',
                    writeQuestion: 'Escribe tu pregunta aquí...'
                },
                ...options
            };

            this.isOpen = false;
            this.isMinimized = false;
            this.isTyping = false;
            this.messageHistory = [];
            this.maxHistoryLength = 50;

            this.init();
        }

        /**
         * Inicializar el chatbot
         */
        init() {
            this.bindEvents();
            this.loadHistory();
            this.setupAutoResize();
            this.setupKeyboardShortcuts();
            this.updateCharCount();
        }

        /**
         * Vincular eventos del DOM
         */
        bindEvents() {
            const $widget = $('#chatbot-ia-widget');
            const $toggle = $('#chatbot-ia-toggle');
            const $window = $('#chatbot-ia-window');
            const $close = $('#chatbot-ia-close');
            const $minimize = $('#chatbot-ia-minimize');
            const $send = $('#chatbot-ia-send');
            const $input = $('#chatbot-ia-input');
            const $clear = $('#chatbot-ia-clear');
            const $overlay = $('#chatbot-ia-overlay');

            // Abrir/cerrar chat
            $toggle.on('click', () => this.toggleChat());
            $close.on('click', () => this.closeChat());
            $minimize.on('click', () => this.minimizeChat());
            $overlay.on('click', () => this.closeChat());

            // Enviar mensaje
            $send.on('click', () => this.sendMessage());
            $input.on('keypress', (e) => {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });

            // Limpiar chat
            $clear.on('click', () => this.clearChat());

            // Auto-resize del textarea
            $input.on('input', () => {
                this.autoResizeTextarea();
                this.updateCharCount();
            });

            // Prevenir envío de formulario
            $input.closest('form').on('submit', (e) => e.preventDefault());

            // Cerrar con Escape
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.closeChat();
                }
            });

            // Scroll automático en mensajes
            this.setupAutoScroll();
        }

        /**
         * Alternar estado del chat
         */
        toggleChat() {
            if (this.isOpen) {
                this.closeChat();
            } else {
                this.openChat();
            }
        }

        /**
         * Abrir el chat
         */
        openChat() {
            const $window = $('#chatbot-ia-window');
            const $overlay = $('#chatbot-ia-overlay');
            const $toggle = $('#chatbot-ia-toggle');

            this.isOpen = true;
            this.isMinimized = false;

            $window.show().removeClass('minimized');
            $overlay.show();
            $toggle.addClass('active');

            // Enfocar el input
            setTimeout(() => {
                $('#chatbot-ia-input').focus();
            }, 300);

            // Animar entrada
            $window.addClass('chatbot-ia-opening');
            setTimeout(() => {
                $window.removeClass('chatbot-ia-opening');
            }, 300);

            // Emitir evento personalizado
            $(document).trigger('chatbot:opened');
        }

        /**
         * Cerrar el chat
         */
        closeChat() {
            const $window = $('#chatbot-ia-window');
            const $overlay = $('#chatbot-ia-overlay');
            const $toggle = $('#chatbot-ia-toggle');

            this.isOpen = false;
            this.isMinimized = false;

            $window.addClass('chatbot-ia-closing');
            $overlay.hide();

            setTimeout(() => {
                $window.hide().removeClass('chatbot-ia-closing minimized');
                $toggle.removeClass('active');
            }, 300);

            // Emitir evento personalizado
            $(document).trigger('chatbot:closed');
        }

        /**
         * Minimizar el chat
         */
        minimizeChat() {
            const $window = $('#chatbot-ia-window');
            const $overlay = $('#chatbot-ia-overlay');

            this.isMinimized = !this.isMinimized;

            if (this.isMinimized) {
                $window.addClass('minimized');
                $overlay.hide();
            } else {
                $window.removeClass('minimized');
                $overlay.show();
                setTimeout(() => {
                    $('#chatbot-ia-input').focus();
                }, 100);
            }
        }

        /**
         * Enviar mensaje
         */
        sendMessage() {
            const $input = $('#chatbot-ia-input');
            const message = $input.val().trim();

            if (!message) {
                this.showNotification(this.options.strings.emptyMessage, 'warning');
                return;
            }

            // Añadir mensaje del usuario
            this.addMessage(message, 'user');
            $input.val('');
            this.autoResizeTextarea();
            this.updateCharCount();

            // Mostrar indicador de escritura
            this.showTypingIndicator();

            // Enviar a la API
            this.sendToAPI(message);
        }

        /**
         * Enviar mensaje a la API
         */
        sendToAPI(message) {
            const self = this;

            $.ajax({
                url: chatbotIa.ajax_url,
                type: 'POST',
                data: {
                    action: 'chatbot_ia_query',
                    nonce: chatbotIa.nonce,
                    message: message
                },
                timeout: 30000,
                success: function(response) {
                    self.hideTypingIndicator();

                    if (response.success) {
                        self.addMessage(response.data.response, 'bot');
                        self.saveToHistory(message, response.data.response);
                    } else {
                        self.addMessage(response.data.message || self.options.strings.error, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    self.hideTypingIndicator();

                    let errorMessage = self.options.strings.apiError;
                    
                    if (status === 'timeout') {
                        errorMessage = 'La solicitud tardó demasiado. Intenta de nuevo.';
                    } else if (xhr.status === 429) {
                        errorMessage = self.options.strings.rateLimit;
                    }

                    self.addMessage(errorMessage, 'error');
                }
            });
        }

        /**
         * Añadir mensaje al chat
         */
        addMessage(content, type = 'bot') {
            const $messages = $('#chatbot-ia-messages');
            const timestamp = new Date().toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit'
            });

            const messageHtml = this.createMessageHtml(content, type, timestamp);
            $messages.append(messageHtml);

            // Scroll al final
            this.scrollToBottom();

            // Animar entrada del mensaje
            const $newMessage = $messages.find('.chatbot-ia-message').last();
            $newMessage.addClass('chatbot-ia-message-entering');
            setTimeout(() => {
                $newMessage.removeClass('chatbot-ia-message-entering');
            }, 300);
        }

        /**
         * Crear HTML del mensaje
         */
        createMessageHtml(content, type, timestamp) {
            const isUser = type === 'user';
            const isError = type === 'error';
            
            let avatarHtml = '';
            let messageClass = 'chatbot-ia-message-bot';

            if (isUser) {
                messageClass = 'chatbot-ia-message-user';
                avatarHtml = `
                    <div class="chatbot-ia-message-avatar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="currentColor"/>
                        </svg>
                    </div>
                `;
            } else {
                avatarHtml = `
                    <div class="chatbot-ia-message-avatar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"/>
                        </svg>
                    </div>
                `;
            }

            if (isError) {
                messageClass += ' chatbot-ia-message-error';
            }

            return `
                <div class="chatbot-ia-message ${messageClass}">
                    ${avatarHtml}
                    <div class="chatbot-ia-message-content">
                        <div class="chatbot-ia-message-text">${this.escapeHtml(content)}</div>
                        <div class="chatbot-ia-message-time">${timestamp}</div>
                    </div>
                </div>
            `;
        }

        /**
         * Mostrar indicador de escritura
         */
        showTypingIndicator() {
            const $typing = $('#chatbot-ia-typing');
            this.isTyping = true;
            $typing.show();
            this.scrollToBottom();
        }

        /**
         * Ocultar indicador de escritura
         */
        hideTypingIndicator() {
            const $typing = $('#chatbot-ia-typing');
            this.isTyping = false;
            $typing.hide();
        }

        /**
         * Limpiar conversación
         */
        clearChat() {
            if (!confirm(this.options.strings.clearChat)) {
                return;
            }

            const $messages = $('#chatbot-ia-messages');
            const $welcomeMessage = $messages.find('.chatbot-ia-message-welcome');
            
            // Mantener solo el mensaje de bienvenida
            $messages.find('.chatbot-ia-message:not(.chatbot-ia-message-welcome)').remove();
            
            // Limpiar historial
            this.messageHistory = [];
            this.saveHistory();

            this.showNotification('Conversación limpiada', 'success');
        }

        /**
         * Scroll automático al final
         */
        scrollToBottom() {
            const $messages = $('#chatbot-ia-messages');
            $messages.scrollTop($messages[0].scrollHeight);
        }

        /**
         * Configurar auto-scroll
         */
        setupAutoScroll() {
            const $messages = $('#chatbot-ia-messages');
            
            // Observer para detectar nuevos mensajes
            if (window.MutationObserver) {
                const observer = new MutationObserver(() => {
                    this.scrollToBottom();
                });
                
                observer.observe($messages[0], {
                    childList: true,
                    subtree: true
                });
            }
        }

        /**
         * Auto-resize del textarea
         */
        autoResizeTextarea() {
            const $input = $('#chatbot-ia-input');
            $input.css('height', 'auto');
            $input.css('height', Math.min($input[0].scrollHeight, 100) + 'px');
        }

        /**
         * Configurar auto-resize
         */
        setupAutoResize() {
            const $input = $('#chatbot-ia-input');
            
            $input.on('input', () => {
                this.autoResizeTextarea();
            });
        }

        /**
         * Actualizar contador de caracteres
         */
        updateCharCount() {
            const $input = $('#chatbot-ia-input');
            const $counter = $('#chatbot-ia-char-count');
            const length = $input.val().length;
            
            $counter.text(length);
            
            if (length > 1800) {
                $counter.parent().addClass('warning');
            } else {
                $counter.parent().removeClass('warning');
            }
        }

        /**
         * Configurar atajos de teclado
         */
        setupKeyboardShortcuts() {
            $(document).on('keydown', (e) => {
                // Ctrl/Cmd + K para abrir chat
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    this.toggleChat();
                }
                
                // Ctrl/Cmd + L para limpiar chat
                if ((e.ctrlKey || e.metaKey) && e.key === 'l' && this.isOpen) {
                    e.preventDefault();
                    this.clearChat();
                }
            });
        }

        /**
         * Mostrar notificación
         */
        showNotification(message, type = 'info') {
            const $notification = $(`
                <div class="chatbot-ia-notification chatbot-ia-notification-${type}">
                    ${message}
                </div>
            `);

            $('body').append($notification);

            // Animar entrada
            setTimeout(() => {
                $notification.addClass('chatbot-ia-notification-show');
            }, 100);

            // Auto-ocultar
            setTimeout(() => {
                $notification.removeClass('chatbot-ia-notification-show');
                setTimeout(() => {
                    $notification.remove();
                }, 300);
            }, 3000);
        }

        /**
         * Guardar en historial
         */
        saveToHistory(userMessage, botResponse) {
            this.messageHistory.push({
                user: userMessage,
                bot: botResponse,
                timestamp: Date.now()
            });

            // Limitar longitud del historial
            if (this.messageHistory.length > this.maxHistoryLength) {
                this.messageHistory = this.messageHistory.slice(-this.maxHistoryLength);
            }

            this.saveHistory();
        }

        /**
         * Guardar historial en localStorage
         */
        saveHistory() {
            try {
                localStorage.setItem('chatbot_ia_history', JSON.stringify(this.messageHistory));
            } catch (e) {
                console.warn('No se pudo guardar el historial del chat:', e);
            }
        }

        /**
         * Cargar historial desde localStorage
         */
        loadHistory() {
            try {
                const saved = localStorage.getItem('chatbot_ia_history');
                if (saved) {
                    this.messageHistory = JSON.parse(saved);
                }
            } catch (e) {
                console.warn('No se pudo cargar el historial del chat:', e);
                this.messageHistory = [];
            }
        }

        /**
         * Escapar HTML
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        /**
         * Obtener historial de mensajes
         */
        getHistory() {
            return this.messageHistory;
        }

        /**
         * Establecer historial de mensajes
         */
        setHistory(history) {
            this.messageHistory = history;
            this.saveHistory();
        }

        /**
         * Destruir instancia
         */
        destroy() {
            // Remover eventos
            $(document).off('keydown.chatbot');
            $('#chatbot-ia-widget').off();
            
            // Limpiar DOM
            $('#chatbot-ia-widget').remove();
        }
    }

    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        // Verificar si ya está inicializado
        if (window.chatbotIAInstance) {
            return;
        }

        // Crear instancia global
        window.chatbotIAInstance = new ChatbotIA();
        
        // Hacer disponible globalmente
        window.ChatbotIA = ChatbotIA;
    });

    // Estilos adicionales para notificaciones
    const notificationStyles = `
        <style>
        .chatbot-ia-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            z-index: 1000000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 300px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .chatbot-ia-notification-show {
            transform: translateX(0);
        }
        
        .chatbot-ia-notification-success {
            background: #00a32a;
        }
        
        .chatbot-ia-notification-error {
            background: #d63638;
        }
        
        .chatbot-ia-notification-warning {
            background: #dba617;
        }
        
        .chatbot-ia-notification-info {
            background: #0073aa;
        }
        
        .chatbot-ia-message-entering {
            animation: chatbot-message-slide-in 0.3s ease-out;
        }
        
        @keyframes chatbot-message-slide-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .chatbot-ia-opening {
            animation: chatbot-window-open 0.3s ease-out;
        }
        
        @keyframes chatbot-window-open {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        
        .chatbot-ia-closing {
            animation: chatbot-window-close 0.3s ease-in;
        }
        
        @keyframes chatbot-window-close {
            from {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
            to {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }
        }
        
        .chatbot-ia-toggle.active {
            background: var(--chatbot-primary-hover, #005a87);
        }
        
        .chatbot-ia-char-count.warning {
            color: #d63638;
            font-weight: bold;
        }
        </style>
    `;

    // Añadir estilos al head
    $('head').append(notificationStyles);

})(jQuery);
