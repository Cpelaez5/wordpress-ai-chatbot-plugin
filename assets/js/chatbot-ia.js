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
         * Vincular eventos del DOM con mejoras de accesibilidad y manejo de eventos
         */
        bindEvents() {
            const $widget = $('#chatbot-ia-widget');
            const $toggle = $('#chatbot-ia-toggle');
            const $window = $('#chatbot-ia-window');
            const $close = $('#chatbot-ia-close');
            const $send = $('#chatbot-ia-send');
            const $input = $('#chatbot-ia-input');
            const $messages = $('#chatbot-ia-messages');
            const $inputContainer = $('.chatbot-ia-input-container');
            const $header = $('.chatbot-ia-header');

            // Configurar atributos ARIA para accesibilidad
            this.setupAccessibility();

            // Abrir/cerrar chat
            $toggle.on('click.chatbot', (e) => {
                e.stopPropagation();
                this.toggleChat();
            });
            $close.on('click.chatbot', (e) => {
                e.stopPropagation();
                this.closeChat();
            });

            // Enviar mensaje
            $send.on('click.chatbot', (e) => {
                e.stopPropagation();
                this.sendMessage();
            });
            $input.on('keypress.chatbot', (e) => {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
            
            // Prevenir que el clic en el input cierre el chat
            $input.on('click.chatbot', (e) => {
                e.stopPropagation();
            });
            
            // Prevenir que el clic en el contenedor del input cierre el chat
            $input.closest('.chatbot-ia-input-container').on('click.chatbot', (e) => {
                e.stopPropagation();
            });
            
            // Prevenir que el clic en el área de mensajes cierre el chat
            $('#chatbot-ia-messages').on('click.chatbot', (e) => {
                e.stopPropagation();
            });
            
            // Prevenir que el clic en el header cierre el chat (excepto en los botones)
            $('.chatbot-ia-header').on('click.chatbot', (e) => {
                // Solo prevenir si no es un clic en un botón
                if (!$(e.target).closest('.chatbot-ia-btn').length) {
                    e.stopPropagation();
                }
            });
            
            // Prevenir propagación de eventos en elementos clave (refuerzo robusto)
            const preventPropagation = (e) => {
                e.stopPropagation();
                e.stopImmediatePropagation();
                console.log('Chatbot IA: Evento detenido en', e.target.className || e.target.tagName);
            };

            // Aplicar prevención de propagación a todos los elementos críticos
            $window.on('click.chatbot mousedown.chatbot touchstart.chatbot', preventPropagation);
            $messages.on('click.chatbot mousedown.chatbot touchstart.chatbot', preventPropagation);
            $inputContainer.on('click.chatbot mousedown.chatbot touchstart.chatbot', preventPropagation);
            $header.on('click.chatbot mousedown.chatbot touchstart.chatbot', (e) => {
                // Solo prevenir si no es un clic en un botón
                if (!$(e.target).closest('.chatbot-ia-btn').length) {
                    preventPropagation(e);
                }
            });
            
            // Prevenir que cualquier interacción dentro del widget completo se propague
            $('#chatbot-ia-widget').on('click.chatbot mousedown.chatbot touchstart.chatbot', preventPropagation);
            
            // Prevenir específicamente que el overlay se active desde dentro del chat
            $window.on('click.chatbot', (e) => {
                e.stopPropagation();
                e.stopImmediatePropagation();
                console.log('Chatbot IA: Clic en ventana del chat, no cerrar');
            });


            // Limitación de caracteres (sin auto-resize)
            $input.on('input', (e) => {
                this.limitCharacters(e);
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
            
            // Cerrar solo cuando se haga clic fuera del chatbot (solución más robusta)
            $(document).on('click.chatbot', (e) => {
                if (this.isOpen && !this.isMinimized) {
                    const $target = $(e.target);
                    const $widget = $('#chatbot-ia-widget');
                    const $toggle = $('#chatbot-ia-toggle');
                    
                    // Verificar si el clic es dentro del widget o en el toggle
                    const isInsideWidget = $target.closest('#chatbot-ia-widget').length > 0;
                    const isOnToggle = $target.closest('#chatbot-ia-toggle').length > 0;
                    
                    if (!isInsideWidget && !isOnToggle) {
                        console.log('Chatbot IA: Clic fuera del widget, cerrando chat');
                        this.closeChat();
                    } else {
                        console.log('Chatbot IA: Clic dentro del widget, no cerrar');
                    }
                }
            });

            // Scroll automático en mensajes
            this.setupAutoScroll();
        }

        /**
         * Configurar accesibilidad con atributos ARIA
         */
        setupAccessibility() {
            const $input = $('#chatbot-ia-input');
            const $messages = $('#chatbot-ia-messages');
            const $window = $('#chatbot-ia-window');
            const $toggle = $('#chatbot-ia-toggle');

            // Configurar atributos ARIA para el input
            $input.attr({
                'aria-label': 'Escribe tu mensaje',
                'aria-describedby': 'chatbot-ia-char-count',
                'aria-live': 'polite',
                'role': 'textbox'
            });

            // Configurar atributos ARIA para el área de mensajes
            $messages.attr({
                'aria-live': 'polite',
                'aria-label': 'Conversación del chat',
                'role': 'log'
            });

            // Configurar atributos ARIA para la ventana del chat
            $window.attr({
                'role': 'dialog',
                'aria-modal': 'true',
                'aria-labelledby': 'chatbot-ia-header-title'
            });

            // Configurar atributos ARIA para el botón toggle
            $toggle.attr({
                'aria-label': 'Abrir chat con IA',
                'aria-expanded': 'false',
                'role': 'button'
            });

            console.log('Chatbot IA: Atributos ARIA configurados para accesibilidad');
        }

        /**
         * Alternar estado del chat
         */
        toggleChat() {
            console.log('Chatbot IA: toggleChat llamado, estado actual:', this.isOpen);
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
            const $toggle = $('#chatbot-ia-toggle');

            console.log('Chatbot IA: Abriendo chat');
            this.isOpen = true;
            this.isMinimized = false;

            $window.show().removeClass('minimized');
            $toggle.addClass('active');

            // Actualizar atributos ARIA
            $toggle.attr('aria-expanded', 'true');
            $window.attr('aria-hidden', 'false');

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
            console.log('Chatbot IA: Chat abierto exitosamente');
        }

        /**
         * Cerrar el chat
         */
        closeChat() {
            const $window = $('#chatbot-ia-window');
            const $toggle = $('#chatbot-ia-toggle');

            console.log('Chatbot IA: Cerrando chat');
            this.isOpen = false;
            this.isMinimized = false;

            // Actualizar atributos ARIA
            $toggle.attr('aria-expanded', 'false');
            $window.attr('aria-hidden', 'true');

            $window.addClass('chatbot-ia-closing');

            setTimeout(() => {
                $window.hide().removeClass('chatbot-ia-closing minimized');
                $toggle.removeClass('active');
            }, 300);

            // Emitir evento personalizado
            $(document).trigger('chatbot:closed');
            console.log('Chatbot IA: Chat cerrado exitosamente');
        }

        /**
         * Minimizar el chat
         */
        minimizeChat() {
            const $window = $('#chatbot-ia-window');

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

            console.log('Chatbot IA: sendMessage llamado con:', message);

            if (!message) {
                this.showNotification(this.options.strings.emptyMessage, 'warning');
                return;
            }

            // Sanitizar mensaje del usuario para prevenir XSS
            const sanitizedMessage = this.sanitizeMessage(message);

            // Añadir mensaje del usuario
            this.addMessage(sanitizedMessage, 'user');
            $input.val('');
            this.updateCharCount();

            // Mostrar indicador de escritura
            this.showTypingIndicator();

            // Enviar a la API
            this.sendToAPI(sanitizedMessage);
        }

        /**
         * Limitar caracteres en el input
         */
        limitCharacters(event) {
            const maxCharacters = 500; // Límite para conversación rápida
            const textarea = event.target;
            const content = textarea.value;

            if (content.length > maxCharacters) {
                textarea.value = content.substring(0, maxCharacters);
                // Mostrar notificación de límite alcanzado
                this.showNotification(`Límite de ${maxCharacters} caracteres alcanzado`, 'warning');
            }
        }

        /**
         * Sanitizar mensaje del usuario para prevenir XSS
         */
        sanitizeMessage(message) {
            // Crear un elemento temporal para escapar HTML
            const temp = document.createElement('div');
            temp.textContent = message;
            return temp.innerHTML;
        }

        /**
         * Enviar mensaje a la API con reintentos y mejor manejo de errores
         */
        sendToAPI(message, retryCount = 0) {
            const self = this;
            const maxRetries = 2;

            console.log('Chatbot IA: Enviando mensaje a API, intento:', retryCount + 1);

            // Verificar que las variables necesarias estén disponibles
            if (typeof chatbotIa === 'undefined' || !chatbotIa.ajax_url || !chatbotIa.nonce) {
                console.error('Chatbot IA: Variables AJAX no disponibles');
                this.hideTypingIndicator();
                this.addMessage('Error de configuración del chatbot', 'error');
                return;
            }

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
                    console.log('Chatbot IA: Respuesta de API recibida:', response);

                    if (response.success) {
                        self.addMessage(response.data.response, 'bot');
                        self.saveToHistory(message, response.data.response);
                    } else {
                        self.addMessage(response.data.message || self.options.strings.error, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Chatbot IA: Error en API:', status, error, xhr.status);

                    // Manejar reintentos para errores de red
                    if (retryCount < maxRetries && (status === 'timeout' || xhr.status === 0)) {
                        console.log('Chatbot IA: Reintentando envío, intento:', retryCount + 2);
                        setTimeout(() => {
                            self.sendToAPI(message, retryCount + 1);
                        }, 1000 * (retryCount + 1)); // Delay progresivo
                        return;
                    }

                    self.hideTypingIndicator();

                    let errorMessage = self.options.strings.apiError;
                    
                    if (status === 'timeout') {
                        errorMessage = 'La solicitud tardó demasiado. Intenta de nuevo.';
                    } else if (xhr.status === 429) {
                        errorMessage = self.options.strings.rateLimit;
                    } else if (xhr.status === 403) {
                        errorMessage = 'Error de permisos. Verifica la configuración.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Error del servidor. Intenta más tarde.';
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
         * Configurar auto-scroll optimizado
         */
        setupAutoScroll() {
            const $messages = $('#chatbot-ia-messages');
            
            // Observer optimizado para detectar nuevos mensajes
            if (window.MutationObserver && $messages.length) {
                const observer = new MutationObserver((mutations) => {
                    // Solo hacer scroll si hay cambios en los hijos
                    const hasNewMessages = mutations.some(mutation => 
                        mutation.type === 'childList' && mutation.addedNodes.length > 0
                    );
                    
                    if (hasNewMessages) {
                        // Usar requestAnimationFrame para mejor rendimiento
                        requestAnimationFrame(() => {
                            this.scrollToBottom();
                        });
                    }
                });
                
                observer.observe($messages[0], {
                    childList: true,
                    subtree: false // Solo observar cambios directos en hijos
                });
                
                // Guardar referencia para limpieza
                this.mutationObserver = observer;
            }
        }



        /**
         * Actualizar contador de caracteres (funcionalidad interna sin UI)
         */
        updateCharCount() {
            const $input = $('#chatbot-ia-input');
            const length = $input.val().length;
            const maxLength = 500; // Límite para conversación rápida
            
            // Solo verificar límites internamente, sin mostrar contador visual
            if (length > maxLength * 0.9) { // 90% del límite
                console.log('Chatbot IA: Cerca del límite de caracteres (90%)');
            } else if (length > maxLength * 0.8) { // 80% del límite
                console.log('Chatbot IA: Aproximándose al límite de caracteres (80%)');
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
         * Guardar en historial optimizado
         */
        saveToHistory(userMessage, botResponse) {
            // Agregar nuevo mensaje
            this.messageHistory.push({
                user: userMessage,
                bot: botResponse,
                timestamp: Date.now()
            });

            // Limitar longitud del historial para optimizar rendimiento
            if (this.messageHistory.length > this.maxHistoryLength) {
                this.messageHistory = this.messageHistory.slice(-this.maxHistoryLength);
            }

            // Guardar con throttling para evitar escrituras excesivas
            this.throttledSaveHistory();
        }

        /**
         * Guardar historial en localStorage con throttling
         */
        throttledSaveHistory() {
            // Limpiar timeout anterior si existe
            if (this.saveTimeout) {
                clearTimeout(this.saveTimeout);
            }
            
            // Guardar después de 1 segundo de inactividad
            this.saveTimeout = setTimeout(() => {
                this.saveHistory();
            }, 1000);
        }

        /**
         * Guardar historial en localStorage
         */
        saveHistory() {
            try {
                // Limitar el tamaño del historial para evitar problemas de localStorage
                const limitedHistory = this.messageHistory.slice(-30); // Solo últimos 30 mensajes
                localStorage.setItem('chatbot_ia_history', JSON.stringify(limitedHistory));
                console.log('Chatbot IA: Historial guardado en localStorage');
            } catch (e) {
                console.warn('Chatbot IA: No se pudo guardar el historial del chat:', e);
                // Si localStorage está lleno, limpiar historial más antiguo
                if (e.name === 'QuotaExceededError') {
                    this.messageHistory = this.messageHistory.slice(-10);
                    try {
                        localStorage.setItem('chatbot_ia_history', JSON.stringify(this.messageHistory));
                    } catch (e2) {
                        console.warn('Chatbot IA: Error crítico al guardar historial:', e2);
                    }
                }
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
         * Destruir instancia con limpieza completa
         */
        destroy() {
            console.log('Chatbot IA: Destruyendo instancia');
            
            // Limpiar timeouts
            if (this.saveTimeout) {
                clearTimeout(this.saveTimeout);
            }
            
            // Limpiar MutationObserver
            if (this.mutationObserver) {
                this.mutationObserver.disconnect();
            }
            
            // Remover eventos con namespace
            $(document).off('.chatbot');
            $('#chatbot-ia-widget').off('.chatbot');
            
            // Limpiar historial
            this.messageHistory = [];
            
            // Limpiar DOM
            $('#chatbot-ia-widget').remove();
            
            console.log('Chatbot IA: Instancia destruida completamente');
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
