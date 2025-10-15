<?php
/**
 * Script para probar el guardado directo
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar que somos administradores
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

echo "<h2>Probar Guardado Directo</h2>";

// Verificar que el plugin esté activo
if (!class_exists('Chatbot_IA')) {
    echo "<p style='color: red;'>❌ Plugin no está activo</p>";
    exit;
}

$plugin_instance = Chatbot_IA::get_instance();

echo "<h3>1. Estado Actual:</h3>";

// Verificar clave API actual
$encrypted_api_key = get_option('chatbot_ia_api_key', '');
echo "<p>Clave API actual: " . (empty($encrypted_api_key) ? 'NO CONFIGURADA' : 'CONFIGURADA (' . strlen($encrypted_api_key) . ' caracteres)') . "</p>";

echo "<h3>2. Simular Guardado via AJAX:</h3>";

// Simular datos del formulario
$_POST = [
    'nonce' => wp_create_nonce('chatbot_ia_admin_nonce'),
    'api_key' => 'sk-nueva-clave-de-prueba-1234567890123456789012345678901234567890',
    'system_instructions' => 'Responde de manera útil y concisa como un agente de soporte al cliente. Siempre responde en español.',
    'context' => 'Eres un asistente de IA especializado en ayudar a los usuarios. Responde siempre en español independientemente del idioma de la consulta del usuario.',
    'model' => 'deepseek-chat',
    'max_tokens' => '1000',
    'temperature' => '1.0',
    'default_language' => 'es',
    'enable_caching' => '1',
    'cache_duration' => '3600',
    'rate_limit' => '10',
    'rate_limit_window' => '60'
];

echo "<p>Datos simulados:</p>";
echo "<ul>";
foreach ($_POST as $key => $value) {
    if ($key === 'api_key') {
        echo "<li><strong>{$key}:</strong> " . substr($value, 0, 10) . "...</li>";
    } else {
        echo "<li><strong>{$key}:</strong> {$value}</li>";
    }
}
echo "</ul>";

// Capturar salida de la función
ob_start();
try {
    $plugin_instance->save_settings_ajax();
    $output = ob_get_clean();
    echo "<p style='color: green;'>✅ Función de guardado ejecutada</p>";
    echo "<p>Salida: " . htmlspecialchars($output) . "</p>";
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color: red;'>❌ Error en función de guardado: " . $e->getMessage() . "</p>";
}

echo "<h3>3. Verificar Estado Después del Guardado:</h3>";

// Verificar estado después del guardado
$final_key = get_option('chatbot_ia_api_key', '');
if (empty($final_key)) {
    echo "<p style='color: red;'>❌ Clave API no guardada después del AJAX</p>";
} else {
    echo "<p style='color: green;'>✅ Clave API guardada después del AJAX</p>";
    
    $decrypted_final = $plugin_instance->decrypt_api_key($final_key);
    echo "<p>Clave final: " . substr($decrypted_final, 0, 10) . "...</p>";
}

echo "<hr>";
echo "<p><a href='" . admin_url() . "'>← Volver al panel de administración</a></p>";
echo "<p><a href='" . admin_url('admin.php?page=chatbot-ia') . "'>→ Ir a configuración del Chatbot IA</a></p>";
?>
