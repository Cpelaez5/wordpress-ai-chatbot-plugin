<?php
/**
 * Archivo de desinstalación del plugin Chatbot IA DeepSeek
 * Limpia todos los datos del plugin cuando se desinstala
 */

// Prevenir acceso directo
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die();
}

/**
 * Función de limpieza al desinstalar el plugin
 */
function chatbot_ia_uninstall() {
    global $wpdb;
    
    // Eliminar todas las opciones del plugin
    $options_to_delete = array(
        'chatbot_ia_api_key',
        'chatbot_ia_system_instructions',
        'chatbot_ia_context',
        'chatbot_ia_model',
        'chatbot_ia_max_tokens',
        'chatbot_ia_temperature',
        'chatbot_ia_default_language',
        'chatbot_ia_enable_caching',
        'chatbot_ia_cache_duration',
        'chatbot_ia_rate_limit',
        'chatbot_ia_rate_limit_window',
        'chatbot_ia_encryption_key' // Clave de encriptación
    );
    
    foreach ($options_to_delete as $option) {
        delete_option($option);
    }
    
    // Eliminar transients relacionados con el plugin
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE '_transient_chatbot_ia_%' 
         OR option_name LIKE '_transient_timeout_chatbot_ia_%'"
    );
    
    // Eliminar tabla de logs si existe
    $table_name = $wpdb->prefix . 'chatbot_ia_logs';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    
    // Limpiar cache de WordPress
    wp_cache_flush();
    
    // Log de desinstalación (opcional)
    error_log('Chatbot IA DeepSeek: Plugin desinstalado y datos limpiados');
}

// Ejecutar limpieza
chatbot_ia_uninstall();