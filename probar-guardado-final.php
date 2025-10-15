<?php
/**
 * Script final para probar el guardado de API key
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar que somos administradores
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

echo "<h2>Prueba Final de Guardado - API Key</h2>";

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

echo "<h3>2. Probar Guardado Directo:</h3>";

// Clave API de prueba con formato correcto (sk- + 32+ caracteres alfanuméricos)
$test_api_key = 'sk-prueba1234567890123456789012345678901234567890';
echo "<p>Probando con clave: " . substr($test_api_key, 0, 10) . "...</p>";
echo "<p>Longitud de la clave: " . strlen($test_api_key) . " caracteres</p>";

// Verificar formato
if (preg_match('/^sk-[a-zA-Z0-9]{32,}$/', $test_api_key)) {
    echo "<p style='color: green;'>✅ Formato de clave API válido</p>";
} else {
    echo "<p style='color: red;'>❌ Formato de clave API inválido</p>";
}

// Encriptar y guardar directamente
$encrypted = $plugin_instance->encrypt_api_key($test_api_key);
echo "<p>✅ Encriptación: " . substr($encrypted, 0, 20) . "... (longitud: " . strlen($encrypted) . ")</p>";

// Guardar en base de datos
$saved = update_option('chatbot_ia_api_key', $encrypted);
echo "<p>✅ Guardado: " . ($saved ? 'EXITOSO' : 'FALLÓ') . "</p>";

// Verificar que se guardó
$saved_key = get_option('chatbot_ia_api_key', '');
echo "<p>✅ Verificación: " . (empty($saved_key) ? 'NO GUARDADA' : 'GUARDADA (' . strlen($saved_key) . ' caracteres)') . "</p>";

// Desencriptar y verificar
$decrypted = $plugin_instance->decrypt_api_key($saved_key);
echo "<p>✅ Desencriptación: " . ($decrypted === $test_api_key ? 'CORRECTA' : 'ERROR') . "</p>";

echo "<h3>3. Probar Función de Sanitización:</h3>";

// Probar la función de sanitización
$sanitized = $plugin_instance->sanitize_api_key($test_api_key);
echo "<p>✅ Sanitización: " . (empty($sanitized) ? 'VACÍA' : 'ENCRIPTADA (' . strlen($sanitized) . ' caracteres)') . "</p>";

echo "<h3>4. Probar Guardado via AJAX:</h3>";

// Simular el guardado via AJAX
$_POST['nonce'] = wp_create_nonce('chatbot_ia_admin_nonce');
$_POST['api_key'] = $test_api_key;
$_POST['system_instructions'] = 'Responde de manera útil y concisa como un agente de soporte al cliente. Siempre responde en español.';
$_POST['context'] = 'Eres un asistente de IA especializado en ayudar a los usuarios. Responde siempre en español independientemente del idioma de la consulta del usuario.';
$_POST['model'] = 'deepseek-chat';
$_POST['max_tokens'] = '1000';
$_POST['temperature'] = '1.0';
$_POST['default_language'] = 'es';
$_POST['enable_caching'] = '1';
$_POST['cache_duration'] = '3600';
$_POST['rate_limit'] = '10';
$_POST['rate_limit_window'] = '60';

echo "<p>Simulando guardado via AJAX...</p>";

// Capturar la salida
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

echo "<h3>5. Verificar Estado Final:</h3>";

// Verificar estado final
$final_key = get_option('chatbot_ia_api_key', '');
if (empty($final_key)) {
    echo "<p style='color: red;'>❌ Clave API no guardada</p>";
} else {
    echo "<p style='color: green;'>✅ Clave API guardada</p>";
    
    $decrypted_final = $plugin_instance->decrypt_api_key($final_key);
    echo "<p>Clave final: " . substr($decrypted_final, 0, 10) . "...</p>";
}

echo "<h3>6. Probar Carga de Opciones:</h3>";

// Recargar opciones del plugin
$plugin_instance->load_options();

// Verificar que la clave API esté disponible en las opciones
$options = $plugin_instance->get_options();
if (isset($options['api_key']) && !empty($options['api_key'])) {
    echo "<p style='color: green;'>✅ Clave API disponible en opciones del plugin</p>";
    echo "<p>Clave en opciones: " . substr($options['api_key'], 0, 10) . "...</p>";
} else {
    echo "<p style='color: red;'>❌ Clave API no disponible en opciones del plugin</p>";
}

echo "<h3>7. Probar Función de Prueba de API:</h3>";

// Simular la función de prueba de API
$_POST['nonce'] = wp_create_nonce('chatbot_ia_admin_nonce');
$_POST['api_key'] = $test_api_key;

echo "<p>Simulando función de prueba de API...</p>";

// Capturar la salida
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

echo "<h3>8. Resumen:</h3>";

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
    echo "<p>La clave API se guarda, encripta y desencripta correctamente.</p>";
    echo "<p><strong>Próximo paso:</strong> Prueba con tu clave API real en el panel de administración.</p>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>⚠️ Hay problemas con el guardado</p>";
    echo "<p>Revisa los elementos marcados con ❌</p>";
}

echo "<hr>";
echo "<p><strong>Nota:</strong> Si ves errores de 'Saldo insuficiente' o 'Autenticación fallida', significa que la clave API es válida pero puede no tener fondos o ser incorrecta.</p>";
echo "<p><a href='" . admin_url() . "'>← Volver al panel de administración</a></p>";
echo "<p><a href='" . admin_url('admin.php?page=chatbot-ia') . "'>→ Ir a configuración del Chatbot IA</a></p>";
?>
