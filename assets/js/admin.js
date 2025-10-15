/**
 * JavaScript del panel de administración del Chatbot IA
 * Funcionalidad para la configuración y pruebas del plugin
 */

(function($) {
    'use strict';

    /**
     * Clase del administrador del Chatbot IA
     */
    class ChatbotIAAdmin {
        constructor() {
            this.init();
        }

        /**
         * Inicializar el administrador
         */
        init() {
            this.bindEvents();
            this.setupFormValidation();
            this.setupTabs();
            this.setupTestChat();
            this.setupApiTest();
            this.setupTemperatureSlider();
        }

        /**
         * Vincular eventos del DOM
         */
        bindEvents() {
            // Formulario principal
            $('#chatbot-ia-form').on('submit', (e) => this.handleFormSubmit(e));
            
            // Botón de prueba de API
            $('#test-api-connection').on('click', (e) => this.testApiConnection(e));
            
            // Botón de envío de mensaje de prueba
            $('#send-test-message').on('click', (e) => this.sendTestMessage(e));
            
            // Input de mensaje de prueba
            $('#test-message').on('keypress', (e) => {
                if (e.which === 13) {
                    e.preventDefault();
                    this.sendTestMessage(e);
                }
            });
            
            // Auto-guardado
            $('input, select, textarea').on('change', () => this.autoSave());
            
            // Validación en tiempo real
            $('#chatbot_ia_api_key').on('input', () => this.validateApiKey());
            $('#chatbot_ia_max_tokens').on('input', () => this.validateMaxTokens());
            $('#chatbot_ia_temperature').on('input', () => this.updateTemperatureDisplay());
            
            // Botón del ojo para mostrar/ocultar clave API
            $('#toggle-api-key').on('click', (e) => this.toggleApiKeyVisibility(e));
        }

        /**
         * Configurar pestañas
         */
        setupTabs() {
            $('.nav-tab').on('click', (e) => {
                e.preventDefault();
                
                const $tab = $(e.currentTarget);
                const tabId = $tab.data('tab');
                
                // Remover clase activa de todas las pestañas y contenidos
                $('.nav-tab').removeClass('nav-tab-active');
                $('.tab-content').removeClass('active');
                
                // Añadir clase activa a la pestaña clickeada
                $tab.addClass('nav-tab-active');
                $('#' + tabId).addClass('active');
                
                // Guardar pestaña activa
                localStorage.setItem('chatbot_ia_active_tab', tabId);
            });
            
            // Restaurar pestaña activa
            const activeTab = localStorage.getItem('chatbot_ia_active_tab');
            if (activeTab) {
                $(`.nav-tab[data-tab="${activeTab}"]`).click();
            }
        }

        /**
         * Configurar validación del formulario
         */
        setupFormValidation() {
            // Validación de clave API
            $('#chatbot_ia_api_key').on('blur', () => this.validateApiKey());
            
            // Validación de tokens máximos
            $('#chatbot_ia_max_tokens').on('blur', () => this.validateMaxTokens());
            
            // Validación de temperatura
            $('#chatbot_ia_temperature').on('blur', () => this.validateTemperature());
        }

        /**
         * Configurar chat de prueba
         */
        setupTestChat() {
            this.testMessageHistory = [];
            this.isTestChatOpen = false;
        }

        /**
         * Configurar prueba de API
         */
        setupApiTest() {
            this.apiTestInProgress = false;
        }

        /**
         * Configurar slider de temperatura
         */
        setupTemperatureSlider() {
            const $slider = $('#chatbot_ia_temperature');
            const $display = $('#temp-value');
            
            $slider.on('input', function() {
                $display.text(this.value);
            });
        }

        /**
         * Manejar envío del formulario
         */
        handleFormSubmit(e) {
            e.preventDefault();
            
            if (!this.validateForm()) {
                this.showNotification('Por favor, corrige los errores antes de guardar.', 'error');
                return false;
            }
            
            this.saveSettings();
        }

        /**
         * Validar formulario completo
         */
        validateForm() {
            let isValid = true;
            
            // Validar clave API
            if (!this.validateApiKey()) {
                isValid = false;
            }
            
            // Validar tokens máximos
            if (!this.validateMaxTokens()) {
                isValid = false;
            }
            
            // Validar temperatura
            if (!this.validateTemperature()) {
                isValid = false;
            }
            
            return isValid;
        }

        /**
         * Validar clave API
         */
        validateApiKey() {
            const $input = $('#chatbot_ia_api_key');
            const value = $input.val().trim();
            
            if (!value) {
                this.showFieldError($input, 'La clave API es requerida');
                return false;
            }
            
            if (!value.startsWith('sk-')) {
                this.showFieldError($input, 'La clave API debe comenzar con "sk-"');
                return false;
            }
            
            this.clearFieldError($input);
            return true;
        }

        /**
         * Validar tokens máximos
         */
        validateMaxTokens() {
            const $input = $('#chatbot_ia_max_tokens');
            const value = parseInt($input.val());
            
            if (isNaN(value) || value < 100 || value > 4000) {
                this.showFieldError($input, 'Los tokens deben estar entre 100 y 4000');
                return false;
            }
            
            this.clearFieldError($input);
            return true;
        }

        /**
         * Validar temperatura
         */
        validateTemperature() {
            const $input = $('#chatbot_ia_temperature');
            const value = parseFloat($input.val());
            
            if (isNaN(value) || value < 0 || value > 2) {
                this.showFieldError($input, 'La temperatura debe estar entre 0 y 2');
                return false;
            }
            
            this.clearFieldError($input);
            return true;
        }

        /**
         * Mostrar error en campo
         */
        showFieldError($field, message) {
            this.clearFieldError($field);
            
            $field.addClass('error');
            $field.after(`<div class="field-error">${message}</div>`);
        }

        /**
         * Limpiar error de campo
         */
        clearFieldError($field) {
            $field.removeClass('error');
            $field.siblings('.field-error').remove();
        }

        /**
         * Guardar configuración
         */
        saveSettings() {
            const $form = $('#chatbot-ia-form');
            const $submitBtn = $form.find('input[type="submit"]');
            const originalText = $submitBtn.val();
            
            // Mostrar estado de guardado
            $submitBtn.val(chatbotIaAdmin.strings.saving).prop('disabled', true);
            
            // Recopilar datos del formulario
            const formData = {
                action: 'chatbot_ia_save_settings',
                nonce: chatbotIaAdmin.nonce,
                api_key: $('#chatbot_ia_api_key').val(),
                system_instructions: $('#chatbot_ia_system_instructions').val(),
                context: $('#chatbot_ia_context').val(),
                model: $('#chatbot_ia_model').val(),
                max_tokens: $('#chatbot_ia_max_tokens').val(),
                temperature: $('#chatbot_ia_temperature').val(),
                default_language: $('#chatbot_ia_default_language').val(),
                enable_caching: $('#chatbot_ia_enable_caching').is(':checked'),
                cache_duration: $('#chatbot_ia_cache_duration').val(),
                rate_limit: $('#chatbot_ia_rate_limit').val(),
                rate_limit_window: $('#chatbot_ia_rate_limit_window').val()
            };
            
            $.ajax({
                url: chatbotIaAdmin.ajax_url,
                type: 'POST',
                data: formData,
                timeout: 30000,
                success: (response) => {
                    if (response.success) {
                        this.showNotification(response.data.message || chatbotIaAdmin.strings.saved, 'success');
                    } else {
                        this.showNotification(response.data.message || 'Error al guardar configuraciones', 'error');
                    }
                },
                error: (xhr, status, error) => {
                    let errorMessage = 'Error al guardar configuraciones';
                    if (status === 'timeout') {
                        errorMessage = 'Tiempo de espera agotado. Intenta de nuevo.';
                    } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errorMessage = xhr.responseJSON.data.message;
                    }
                    this.showNotification(errorMessage, 'error');
                },
                complete: () => {
                    $submitBtn.val(originalText).prop('disabled', false);
                }
            });
        }

        /**
         * Auto-guardado
         */
        autoSave() {
            // Implementar auto-guardado si es necesario
            // Por ahora solo validamos en tiempo real
            this.validateForm();
        }

        /**
         * Probar conexión con la API
         */
        testApiConnection(e) {
            e.preventDefault();
            
            if (this.apiTestInProgress) {
                return;
            }
            
            const $button = $('#test-api-connection');
            const $result = $('#api-test-result');
            
            // Validar clave API primero
            if (!this.validateApiKey()) {
                this.showNotification('Por favor, ingresa una clave API válida antes de probar la conexión.', 'error');
                return;
            }
            
            this.apiTestInProgress = true;
            $button.prop('disabled', true).text(chatbotIaAdmin.strings.testing_connection);
            $result.removeClass('success error').text('');
            
            $.ajax({
                url: chatbotIaAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'chatbot_ia_test_api',
                    nonce: chatbotIaAdmin.nonce,
                    api_key: $('#chatbot_ia_api_key').val()
                },
                timeout: 30000,
                success: (response) => {
                    if (response.success) {
                        $result.addClass('success').text(chatbotIaAdmin.strings.connection_success);
                        this.showNotification('Conexión exitosa con la API de DeepSeek', 'success');
                    } else {
                        $result.addClass('error').text(response.data.message || chatbotIaAdmin.strings.connection_error);
                        this.showNotification('Error en la conexión: ' + (response.data.message || 'Error desconocido'), 'error');
                    }
                },
                error: (xhr, status, error) => {
                    let errorMessage = chatbotIaAdmin.strings.connection_error;
                    
                    if (status === 'timeout') {
                        errorMessage = 'La conexión tardó demasiado. Verifica tu conexión a internet.';
                    } else if (xhr.status === 401) {
                        errorMessage = 'Clave API inválida. Verifica tu clave API.';
                    } else if (xhr.status === 429) {
                        errorMessage = 'Límite de solicitudes excedido. Espera un momento.';
                    }
                    
                    $result.addClass('error').text(errorMessage);
                    this.showNotification('Error: ' + errorMessage, 'error');
                },
                complete: () => {
                    this.apiTestInProgress = false;
                    $button.prop('disabled', false).text('Probar Conexión');
                }
            });
        }

        /**
         * Enviar mensaje de prueba
         */
        sendTestMessage(e) {
            e.preventDefault();
            
            const $input = $('#test-message');
            const $button = $('#send-test-message');
            const $messages = $('#chat-test-messages');
            const message = $input.val().trim();
            
            if (!message) {
                this.showNotification('Por favor, escribe un mensaje de prueba.', 'warning');
                return;
            }
            
            // Añadir mensaje del usuario
            this.addTestMessage(message, 'user');
            $input.val('');
            
            // Mostrar indicador de escritura
            this.showTestTyping();
            
            // Deshabilitar botón
            $button.prop('disabled', true);
            
            // Enviar a la API
            $.ajax({
                url: chatbotIaAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'chatbot_ia_query',
                    nonce: chatbotIaAdmin.nonce,
                    message: message
                },
                timeout: 30000,
                success: (response) => {
                    this.hideTestTyping();
                    
                    if (response.success) {
                        this.addTestMessage(response.data.response, 'bot');
                        this.testMessageHistory.push({
                            user: message,
                            bot: response.data.response,
                            timestamp: Date.now()
                        });
                    } else {
                        this.addTestMessage(response.data.message || 'Error en la respuesta', 'error');
                    }
                },
                error: (xhr, status, error) => {
                    this.hideTestTyping();
                    
                    let errorMessage = 'Error de conexión';
                    
                    if (status === 'timeout') {
                        errorMessage = 'La solicitud tardó demasiado. Intenta de nuevo.';
                    } else if (xhr.status === 429) {
                        errorMessage = 'Límite de solicitudes excedido. Espera un momento.';
                    }
                    
                    this.addTestMessage(errorMessage, 'error');
                },
                complete: () => {
                    $button.prop('disabled', false);
                    this.scrollTestMessages();
                }
            });
        }

        /**
         * Añadir mensaje de prueba
         */
        addTestMessage(content, type) {
            const $messages = $('#chat-test-messages');
            const timestamp = new Date().toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit'
            });
            
            const messageClass = type === 'user' ? 'user' : (type === 'error' ? 'error' : 'bot');
            const messageHtml = `
                <div class="message ${messageClass}">
                    <div class="message-content">
                        <div class="message-text">${this.escapeHtml(content)}</div>
                        <div class="message-time">${timestamp}</div>
                    </div>
                </div>
            `;
            
            $messages.append(messageHtml);
            this.scrollTestMessages();
        }

        /**
         * Mostrar indicador de escritura en prueba
         */
        showTestTyping() {
            const $messages = $('#chat-test-messages');
            const typingHtml = `
                <div class="message bot typing-indicator">
                    <div class="message-content">
                        <div class="message-text">
                            <span class="typing-dots">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                            ${chatbotIaAdmin.strings.testing_connection}
                        </div>
                    </div>
                </div>
            `;
            
            $messages.append(typingHtml);
            this.scrollTestMessages();
        }

        /**
         * Ocultar indicador de escritura en prueba
         */
        hideTestTyping() {
            $('#chat-test-messages .typing-indicator').remove();
        }

        /**
         * Scroll en mensajes de prueba
         */
        scrollTestMessages() {
            const $messages = $('#chat-test-messages');
            $messages.scrollTop($messages[0].scrollHeight);
        }

        /**
         * Actualizar display de temperatura
         */
        updateTemperatureDisplay() {
            const $slider = $('#chatbot_ia_temperature');
            const $display = $('#temp-value');
            $display.text($slider.val());
        }

        /**
         * Toggle para mostrar/ocultar clave API
         */
        toggleApiKeyVisibility(e) {
            e.preventDefault();
            
            const $input = $('#chatbot_ia_api_key');
            const $icon = $('#toggle-icon');
            const $button = $('#toggle-api-key');
            
            console.log('Botón del ojo clickeado'); // Debug
            
            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
                $button.attr('title', 'Ocultar clave API');
                console.log('Mostrando clave API'); // Debug
            } else {
                $input.attr('type', 'password');
                $icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
                $button.attr('title', 'Mostrar clave API');
                console.log('Ocultando clave API'); // Debug
            }
        }

        /**
         * Mostrar notificación
         */
        showNotification(message, type = 'info') {
            const $notification = $(`
                <div class="notice notice-${type} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Descartar este aviso.</span>
                    </button>
                </div>
            `);
            
            $('.wrap h1').after($notification);
            
            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                $notification.fadeOut(() => {
                    $notification.remove();
                });
            }, 5000);
            
            // Manejar botón de cerrar
            $notification.find('.notice-dismiss').on('click', () => {
                $notification.fadeOut(() => {
                    $notification.remove();
                });
            });
        }

        /**
         * Escapar HTML
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        // Verificar si estamos en la página del plugin
        if ($('#chatbot-ia-form').length === 0) {
            return;
        }
        
        // Crear instancia del administrador
        window.chatbotIAAdminInstance = new ChatbotIAAdmin();
    });

    // Estilos adicionales para el admin
    const adminStyles = `
        <style>
        .field-error {
            color: #d63638;
            font-size: 12px;
            margin-top: 5px;
            font-weight: 500;
        }
        
        .error {
            border-color: #d63638 !important;
            box-shadow: 0 0 0 2px rgba(214, 54, 56, 0.1) !important;
        }
        
        .typing-indicator .typing-dots {
            display: inline-flex;
            gap: 3px;
            margin-right: 8px;
        }
        
        .typing-indicator .typing-dots span {
            width: 4px;
            height: 4px;
            background: #666;
            border-radius: 50%;
            animation: typing-dots 1.4s infinite ease-in-out;
        }
        
        .typing-indicator .typing-dots span:nth-child(1) {
            animation-delay: -0.32s;
        }
        
        .typing-indicator .typing-dots span:nth-child(2) {
            animation-delay: -0.16s;
        }
        
        @keyframes typing-dots {
            0%, 80%, 100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            40% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .message {
            animation: message-slide-in 0.3s ease-out;
        }
        
        @keyframes message-slide-in {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .notice {
            animation: notice-slide-in 0.3s ease-out;
        }
        
        @keyframes notice-slide-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .tab-content {
            animation: tab-fade-in 0.3s ease-out;
        }
        
        @keyframes tab-fade-in {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        input[type="range"] {
            cursor: pointer;
        }
        
        input[type="range"]:focus {
            outline: 2px solid #0073aa;
            outline-offset: 2px;
        }
        
        .form-table th {
            position: relative;
        }
        
        .form-table th::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 1px;
            height: 20px;
            background: #ddd;
        }
        
        .postbox {
            transition: box-shadow 0.2s ease;
        }
        
        .postbox:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .button:focus {
            outline: 2px solid #0073aa;
            outline-offset: 2px;
        }
        
        .button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .button:disabled:hover {
            background: var(--admin-primary-color);
        }
        
        .chat-test-input input:focus {
            outline: 2px solid #0073aa;
            outline-offset: 2px;
        }
        
        .message-content {
            position: relative;
        }
        
        .message-time {
            font-size: 11px;
            color: #666;
            margin-top: 4px;
            opacity: 0.8;
        }
        
        .message.user .message-time {
            text-align: right;
        }
        
        .message.bot .message-time {
            text-align: left;
        }
        
        .message-text {
            word-wrap: break-word;
            line-height: 1.4;
        }
        
        .message.user .message-text {
            text-align: right;
        }
        
        .message.bot .message-text {
            text-align: left;
        }
        
        .message.error .message-text {
            font-weight: 500;
        }
        
        #chat-test-messages {
            scrollbar-width: thin;
            scrollbar-color: #ddd transparent;
        }
        
        #chat-test-messages::-webkit-scrollbar {
            width: 6px;
        }
        
        #chat-test-messages::-webkit-scrollbar-track {
            background: transparent;
        }
        
        #chat-test-messages::-webkit-scrollbar-thumb {
            background: #ddd;
            border-radius: 3px;
        }
        
        #chat-test-messages::-webkit-scrollbar-thumb:hover {
            background: #bbb;
        }
        </style>
    `;

    // Añadir estilos al head
    $('head').append(adminStyles);

})(jQuery);
