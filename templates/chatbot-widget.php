<?php
/**
 * Template del widget del chatbot
 * Interfaz de usuario del chatbot para el frontend
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Obtener atributos del shortcode
$position = isset($atts['position']) ? sanitize_text_field($atts['position']) : 'bottom-right';
$theme = isset($atts['theme']) ? sanitize_text_field($atts['theme']) : 'default';
$size = isset($atts['size']) ? sanitize_text_field($atts['size']) : 'medium';
?>

<!-- Contenedor principal del chatbot -->
<div id="chatbot-ia-widget" 
     class="chatbot-ia-widget chatbot-ia-<?php echo esc_attr($position); ?> chatbot-ia-<?php echo esc_attr($theme); ?> chatbot-ia-<?php echo esc_attr($size); ?>"
     data-position="<?php echo esc_attr($position); ?>"
     data-theme="<?php echo esc_attr($theme); ?>"
     data-size="<?php echo esc_attr($size); ?>">
    
    <!-- Botón flotante para abrir el chat -->
    <div id="chatbot-ia-toggle" class="chatbot-ia-toggle" role="button" tabindex="0" aria-label="<?php _e('Abrir chat con IA', 'chatbot-ia'); ?>">
        <div class="chatbot-ia-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"/>
            </svg>
        </div>
        <div class="chatbot-ia-pulse"></div>
        <span class="chatbot-ia-tooltip"><?php _e('¡Hola! ¿En qué te ayudo?', 'chatbot-ia'); ?></span>
    </div>
    
    <!-- Ventana del chat -->
    <div id="chatbot-ia-window" class="chatbot-ia-window" style="display: none;">
        <!-- Header del chat -->
        <div class="chatbot-ia-header">
            <div class="chatbot-ia-header-info">
                <div class="chatbot-ia-avatar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"/>
                    </svg>
                </div>
                <div class="chatbot-ia-header-text">
                    <h3><?php _e('Asistente IA', 'chatbot-ia'); ?></h3>
                    <span class="chatbot-ia-status"><?php _e('En línea', 'chatbot-ia'); ?></span>
                </div>
            </div>
            <div class="chatbot-ia-header-actions">
                <button type="button" id="chatbot-ia-minimize" class="chatbot-ia-btn chatbot-ia-btn-minimize" aria-label="<?php _e('Minimizar', 'chatbot-ia'); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 19h12v2H6z" fill="currentColor"/>
                    </svg>
                </button>
                <button type="button" id="chatbot-ia-close" class="chatbot-ia-btn chatbot-ia-btn-close" aria-label="<?php _e('Cerrar', 'chatbot-ia'); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" fill="currentColor"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Área de mensajes -->
        <div id="chatbot-ia-messages" class="chatbot-ia-messages" role="log" aria-live="polite" aria-label="<?php _e('Conversación del chat', 'chatbot-ia'); ?>">
            <!-- Mensaje de bienvenida -->
            <div class="chatbot-ia-message chatbot-ia-message-bot chatbot-ia-message-welcome">
                <div class="chatbot-ia-message-avatar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"/>
                    </svg>
                </div>
                <div class="chatbot-ia-message-content">
                    <div class="chatbot-ia-message-text">
                        <?php _e('¡Hola! Soy tu asistente de IA. ¿En qué puedo ayudarte hoy?', 'chatbot-ia'); ?>
                    </div>
                    <div class="chatbot-ia-message-time">
                        <?php echo current_time('H:i'); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Indicador de escritura -->
        <div id="chatbot-ia-typing" class="chatbot-ia-typing" style="display: none;">
            <div class="chatbot-ia-typing-dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <span class="chatbot-ia-typing-text"><?php _e('Escribiendo...', 'chatbot-ia'); ?></span>
        </div>
        
        <!-- Área de entrada -->
        <div class="chatbot-ia-input-container">
            <div class="chatbot-ia-input-wrapper">
                <textarea id="chatbot-ia-input" 
                          class="chatbot-ia-input" 
                          placeholder="<?php _e('Escribe tu pregunta aquí...', 'chatbot-ia'); ?>"
                          rows="1"
                          aria-label="<?php _e('Escribe tu mensaje', 'chatbot-ia'); ?>"
                          maxlength="2000"></textarea>
                <button type="button" id="chatbot-ia-send" class="chatbot-ia-btn chatbot-ia-btn-send" aria-label="<?php _e('Enviar mensaje', 'chatbot-ia'); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" fill="currentColor"/>
                    </svg>
                </button>
            </div>
            <div class="chatbot-ia-input-footer">
                <div class="chatbot-ia-char-count">
                    <span id="chatbot-ia-char-count">0</span>/2000
                </div>
                <div class="chatbot-ia-actions">
                    <button type="button" id="chatbot-ia-clear" class="chatbot-ia-btn chatbot-ia-btn-clear" aria-label="<?php _e('Limpiar conversación', 'chatbot-ia'); ?>">
                        <?php _e('Limpiar', 'chatbot-ia'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Overlay para cerrar el chat al hacer clic fuera -->
    <div id="chatbot-ia-overlay" class="chatbot-ia-overlay" style="display: none;"></div>
</div>

<!-- Script para inicializar el chatbot -->
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si el chatbot ya está inicializado
    if (window.chatbotIaInitialized) {
        return;
    }
    
    // Marcar como inicializado
    window.chatbotIaInitialized = true;
    
    // Inicializar el chatbot
    if (typeof ChatbotIA !== 'undefined') {
        new ChatbotIA({
            position: '<?php echo esc_js($position); ?>',
            theme: '<?php echo esc_js($theme); ?>',
            size: '<?php echo esc_js($size); ?>',
            strings: {
                loading: '<?php _e('Cargando...', 'chatbot-ia'); ?>',
                error: '<?php _e('Lo siento, ha ocurrido un error. Intenta de nuevo.', 'chatbot-ia'); ?>',
                emptyMessage: '<?php _e('Por favor, escribe un mensaje.', 'chatbot-ia'); ?>',
                rateLimit: '<?php _e('Has alcanzado el límite de mensajes. Espera un momento.', 'chatbot-ia'); ?>',
                apiError: '<?php _e('Error en la conexión con la IA. Verifica la configuración.', 'chatbot-ia'); ?>',
                welcomeMessage: '<?php _e('¡Hola! Soy tu asistente de IA. ¿En qué puedo ayudarte hoy?', 'chatbot-ia'); ?>',
                typing: '<?php _e('Escribiendo...', 'chatbot-ia'); ?>',
                online: '<?php _e('En línea', 'chatbot-ia'); ?>',
                offline: '<?php _e('Desconectado', 'chatbot-ia'); ?>',
                clearChat: '<?php _e('¿Estás seguro de que quieres limpiar la conversación?', 'chatbot-ia'); ?>',
                sendMessage: '<?php _e('Enviar mensaje', 'chatbot-ia'); ?>',
                openChat: '<?php _e('Abrir chat con IA', 'chatbot-ia'); ?>',
                closeChat: '<?php _e('Cerrar chat', 'chatbot-ia'); ?>',
                minimizeChat: '<?php _e('Minimizar chat', 'chatbot-ia'); ?>',
                clearConversation: '<?php _e('Limpiar conversación', 'chatbot-ia'); ?>',
                writeMessage: '<?php _e('Escribe tu mensaje', 'chatbot-ia'); ?>',
                writeQuestion: '<?php _e('Escribe tu pregunta aquí...', 'chatbot-ia'); ?>'
            }
        });
    }
});
</script>
