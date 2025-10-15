<?php
/**
 * Script para activar el plugin y configurar opciones por defecto
 * Ejecutar este archivo una vez para configurar el plugin
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar que somos administradores
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

echo "<h2>Activando y configurando Chatbot IA DeepSeek...</h2>";

// Activar el plugin si no está activo
$active_plugins = get_option('active_plugins', array());
$plugin_file = 'testplugin/testplugin.php';

if (!in_array($plugin_file, $active_plugins)) {
    $active_plugins[] = $plugin_file;
    update_option('active_plugins', $active_plugins);
    echo "<p>✅ Plugin activado</p>";
} else {
    echo "<p>✅ Plugin ya estaba activo</p>";
}

// Configurar opciones por defecto
$default_options = array(
    'chatbot_ia_api_key' => 'sk-a42f9205e7ba4a338c80bc8c1f1fd88e',
    'chatbot_ia_system_instructions' => 'Responde de manera útil y concisa como un agente de soporte al cliente. Siempre responde en español.',
    'chatbot_ia_context' => 'Eres un asistente de IA especializado en ayudar a los usuarios. Responde siempre en español independientemente del idioma de la consulta del usuario.',
    'chatbot_ia_model' => 'deepseek-chat',
    'chatbot_ia_max_tokens' => 1000,
    'chatbot_ia_temperature' => 1.0,
    'chatbot_ia_default_language' => 'es',
    'chatbot_ia_enable_caching' => true,
    'chatbot_ia_cache_duration' => 3600,
    'chatbot_ia_rate_limit' => 10,
    'chatbot_ia_rate_limit_window' => 60
);

// Generar clave de encriptación si no existe
if (!get_option('chatbot_ia_encryption_key')) {
    update_option('chatbot_ia_encryption_key', wp_generate_password(32, false));
    echo "<p>✅ Clave de encriptación generada</p>";
}

foreach ($default_options as $option => $value) {
    update_option($option, $value);
    echo "<p>✅ Configurado: $option</p>";
}

// Limpiar cache
wp_cache_flush();

echo "<h3>🎉 ¡Configuración completada!</h3>";
echo "<p>El plugin Chatbot IA DeepSeek está ahora activo y configurado.</p>";
echo "<p>Puedes acceder a la configuración en: <strong>Chatbot IA</strong> en el menú principal del admin.</p>";
echo "<p>Para usar el chatbot, añade el shortcode <code>[chatbot-ia]</code> en cualquier página.</p>";

echo "<hr>";
echo "<p><a href='" . admin_url() . "'>← Volver al panel de administración</a></p>";
?>
