<?php
/**
 * Script completo para debuggear el problema de guardado
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar que somos administradores
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

echo "<h2>Debug Completo del Guardado - Chatbot IA</h2>";

// Verificar que el plugin esté activo
if (!class_exists('Chatbot_IA')) {
    echo "<p style='color: red;'>❌ Plugin no está activo</p>";
    exit;
}

$plugin_instance = Chatbot_IA::get_instance();

echo "<h3>1. Estado Actual de la Base de Datos:</h3>";

// Verificar todas las opciones del plugin
$options_to_check = [
    'chatbot_ia_api_key' => 'Clave API',
    'chatbot_ia_system_instructions' => 'Instrucciones del Sistema',
    'chatbot_ia_context' => 'Contexto',
    'chatbot_ia_model' => 'Modelo',
    'chatbot_ia_max_tokens' => 'Máximo de Tokens',
    'chatbot_ia_temperature' => 'Temperatura',
    'chatbot_ia_default_language' => 'Idioma Predeterminado',
    'chatbot_ia_enable_caching' => 'Habilitar Cache',
    'chatbot_ia_cache_duration' => 'Duración del Cache',
    'chatbot_ia_rate_limit' => 'Límite de Velocidad',
    'chatbot_ia_rate_limit_window' => 'Ventana de Límite de Velocidad',
    'chatbot_ia_encryption_key' => 'Clave de Encriptación'
];

foreach ($options_to_check as $option => $label) {
    $value = get_option($option, 'NO CONFIGURADA');
    $status = ($value === 'NO CONFIGURADA') ? '❌' : '✅';
    $display_value = ($option === 'chatbot_ia_api_key' && $value !== 'NO CONFIGURADA') ? 
        'ENCRIPTADA (' . strlen($value) . ' caracteres)' : $value;
    echo "<p>{$status} <strong>{$label}:</strong> {$display_value}</p>";
}

echo "<h3>2. Probar Guardado Manual de API Key:</h3>";

// Clave API de prueba
$test_api_key = 'sk-prueba1234567890123456789012345678901234567890';
echo "<p>Probando con clave: " . substr($test_api_key, 0, 10) . "...</p>";

// Verificar formato
if (preg_match('/^sk-[a-zA-Z0-9]{32,}$/', $test_api_key)) {
    echo "<p style='color: green;'>✅ Formato de clave API válido</p>";
} else {
    echo "<p style='color: red;'>❌ Formato de clave API inválido</p>";
}

// Encriptar
$encrypted = $plugin_instance->encrypt_api_key($test_api_key);
echo "<p>✅ Encriptación: " . substr($encrypted, 0, 20) . "... (longitud: " . strlen($encrypted) . ")</p>";

// Guardar directamente
$saved = update_option('chatbot_ia_api_key', $encrypted);
echo "<p>✅ Guardado directo: " . ($saved ? 'EXITOSO' : 'FALLÓ') . "</p>";

// Verificar guardado
$saved_key = get_option('chatbot_ia_api_key', '');
echo "<p>✅ Verificación: " . (empty($saved_key) ? 'NO GUARDADA' : 'GUARDADA (' . strlen($saved_key) . ' caracteres)') . "</p>";

// Desencriptar
$decrypted = $plugin_instance->decrypt_api_key($saved_key);
echo "<p>✅ Desencriptación: " . ($decrypted === $test_api_key ? 'CORRECTA' : 'ERROR') . "</p>";

echo "<h3>3. Probar Función de Sanitización:</h3>";

// Probar sanitización
$sanitized = $plugin_instance->sanitize_api_key($test_api_key);
echo "<p>✅ Sanitización: " . (empty($sanitized) ? 'VACÍA' : 'ENCRIPTADA (' . strlen($sanitized) . ' caracteres)') . "</p>";

echo "<h3>4. Simular Guardado via AJAX:</h3>";

// Simular datos del formulario
$_POST = [
    'nonce' => wp_create_nonce('chatbot_ia_admin_nonce'),
    'api_key' => $test_api_key,
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

echo "<h3>5. Verificar Estado Después del Guardado:</h3>";

// Verificar estado después del guardado
$final_key = get_option('chatbot_ia_api_key', '');
if (empty($final_key)) {
    echo "<p style='color: red;'>❌ Clave API no guardada después del AJAX</p>";
} else {
    echo "<p style='color: green;'>✅ Clave API guardada después del AJAX</p>";
    
    $decrypted_final = $plugin_instance->decrypt_api_key($final_key);
    echo "<p>Clave final: " . substr($decrypted_final, 0, 10) . "...</p>";
}

echo "<h3>6. Probar Carga de Opciones:</h3>";

// Recargar opciones
$plugin_instance->load_options();
$options = $plugin_instance->get_options();

if (isset($options['api_key']) && !empty($options['api_key'])) {
    echo "<p style='color: green;'>✅ Clave API disponible en opciones del plugin</p>";
    echo "<p>Clave en opciones: " . substr($options['api_key'], 0, 10) . "...</p>";
} else {
    echo "<p style='color: red;'>❌ Clave API no disponible en opciones del plugin</p>";
}

echo "<h3>7. Probar Función de Prueba de API:</h3>";

// Simular prueba de API
$_POST = [
    'nonce' => wp_create_nonce('chatbot_ia_admin_nonce'),
    'api_key' => $test_api_key
];

echo "<p>Simulando función de prueba de API...</p>";

ob_start();
try {
    $plugin_instance->test_api_connection();
    $output = ob_get_clean();
    echo "<p style='color: green;'>✅ Función de prueba ejecutada</p>";
    echo "<p>Salida: " . htmlspecialchars($output) . "</p>";
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color: red;'>❌ Error en función de prueba: " . $e->getMessage() . "</p>";
}

echo "<h3>8. Verificar Permisos y Seguridad:</h3>";

// Verificar permisos
echo "<p>✅ Usuario actual: " . wp_get_current_user()->user_login . "</p>";
echo "<p>✅ Permisos manage_options: " . (current_user_can('manage_options') ? 'SÍ' : 'NO') . "</p>";
echo "<p>✅ Nonce válido: " . (wp_verify_nonce($_POST['nonce'], 'chatbot_ia_admin_nonce') ? 'SÍ' : 'NO') . "</p>";

echo "<h3>9. Resumen Final:</h3>";

$all_good = true;

// Verificar que la clave API esté guardada
if (empty(get_option('chatbot_ia_api_key', ''))) {
    echo "<p style='color: red;'>❌ Clave API no guardada en base de datos</p>";
    $all_good = false;
} else {
    echo "<p style='color: green;'>✅ Clave API guardada en base de datos</p>";
}

// Verificar que se pueda desencriptar
$encrypted = get_option('chatbot_ia_api_key', '');
$decrypted = $plugin_instance->decrypt_api_key($encrypted);
if (empty($decrypted)) {
    echo "<p style='color: red;'>❌ Error al desencriptar clave API</p>";
    $all_good = false;
} else {
    echo "<p style='color: green;'>✅ Clave API se desencripta correctamente</p>";
}

// Verificar que esté disponible en opciones del plugin
$options = $plugin_instance->get_options();
if (empty($options['api_key'])) {
    echo "<p style='color: red;'>❌ Clave API no disponible en opciones del plugin</p>";
    $all_good = false;
} else {
    echo "<p style='color: green;'>✅ Clave API disponible en opciones del plugin</p>";
}

if ($all_good) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 ¡El guardado funciona correctamente!</p>";
    echo "<p>El problema puede estar en el frontend (JavaScript) o en la configuración del formulario.</p>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>⚠️ Hay problemas con el guardado</p>";
    echo "<p>Revisa los elementos marcados con ❌</p>";
}

echo "<hr>";
echo "<p><strong>Próximos pasos:</strong></p>";
echo "<ul>";
echo "<li>Si el guardado funciona aquí, el problema está en el frontend</li>";
echo "<li>Si el guardado no funciona aquí, el problema está en el backend</li>";
echo "<li>Revisa la consola del navegador para errores de JavaScript</li>";
echo "<li>Verifica que el formulario esté enviando los datos correctamente</li>";
echo "</ul>";

echo "<p><a href='" . admin_url() . "'>← Volver al panel de administración</a></p>";
echo "<p><a href='" . admin_url('admin.php?page=chatbot-ia') . "'>→ Ir a configuración del Chatbot IA</a></p>";
?>
