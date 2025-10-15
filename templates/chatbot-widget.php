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
        <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 1179 1000"><path fill="#ffffff" d="M1179 465q0 126-79 233.5T885 868t-296 62q-122 0-234-39l2 3L0 1001q44-59 70.5-126.5T102 769l4-38Q0 611 0 465q0-126 79-233T293.5 63T589 1t296 62t215 169t79 233zm-250 0q0-29-21-50t-51-21q-29 0-50 21t-21 50q0 30 21 51t50 21q30 0 51-21t21-51zm-250 0q0-29-21-50t-51-21q-29 0-50 21t-21 50q0 30 21 51t50 21q30 0 51-21t21-51zm-250 0q0-29-21-50t-51-21q-29 0-50 21t-21 50q0 30 21 51t50 21q30 0 51-21t21-51z"/></svg>
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
                <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 1664 1664"><path fill="#ffffff" d="M832 0Q673 0 560.5 112.5T448 384t112.5 271.5T832 768t271.5-112.5T1216 384t-112.5-271.5T832 0zm0 896q112 0 227 22t224 69.5t193.5 114t136 162.5t51.5 208q0 75-57 133.5t-135 58.5H192q-78 0-135-58.5T0 1472q0-112 51.5-208t136-162.5t193.5-114T605 918t227-22z"/></svg>
                </div>
                <div class="chatbot-ia-header-text">
                    <h3 id="chatbot-ia-header-title"><?php _e('Asistente Virtual', 'chatbot-ia'); ?></h3>
                    <span class="chatbot-ia-status"><?php _e('En línea', 'chatbot-ia'); ?></span>
                </div>
            </div>
            <div class="chatbot-ia-header-actions">
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 1664 1664"><path fill="currentColor" d="M832 0Q673 0 560.5 112.5T448 384t112.5 271.5T832 768t271.5-112.5T1216 384t-112.5-271.5T832 0zm0 896q112 0 227 22t224 69.5t193.5 114t136 162.5t51.5 208q0 75-57 133.5t-135 58.5H192q-78 0-135-58.5T0 1472q0-112 51.5-208t136-162.5t193.5-114T605 918t227-22z"/></svg>
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
                          maxlength="500"
                          style="height: 47.2px; resize: none;"></textarea>
                <button type="button" id="chatbot-ia-send" class="chatbot-ia-btn chatbot-ia-btn-send" aria-label="<?php _e('Enviar mensaje', 'chatbot-ia'); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" fill="currentColor"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- El chatbot se inicializa automáticamente desde el script externo -->
