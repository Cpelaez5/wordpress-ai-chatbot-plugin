<?php
/**
 * Script para probar el guardado del formulario
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar que somos administradores
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

echo "<h2>Probar Guardado del Formulario - Chatbot IA DeepSeek</h2>";

// Verificar que el plugin esté activo
if (!class_exists('Chatbot_IA')) {
    echo "<p style='color: red;'>❌ Plugin no está activo</p>";
    exit;
}

$plugin_instance = Chatbot_IA::get_instance();

echo "<h3>1. Estado Actual:</h3>";

// Verificar clave API actual
$encrypted_api_key = get_option('chatbot_ia_api_key', '');
echo "<p>Clave API encriptada: " . (empty($encrypted_api_key) ? 'NO CONFIGURADA' : 'CONFIGURADA (' . strlen($encrypted_api_key) . ' caracteres)') . "</p>";

echo "<h3>2. Probar Guardado via AJAX (simulando el formulario):</h3>";

// Simular los datos que enviaría el formulario
$_POST['nonce'] = wp_create_nonce('chatbot_ia_admin_nonce');
$_POST['api_key'] = 'sk-a42f9205e7ba4a338c80bc8c1f1fd88e';
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

echo "<p>Simulando envío del formulario...</p>";

// Capturar la salida de la función de guardado
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

// Verificar que se guardó
$saved_key = get_option('chatbot_ia_api_key', '');
if (empty($saved_key)) {
    echo "<p style='color: red;'>❌ Clave API no guardada</p>";
} else {
    echo "<p style='color: green;'>✅ Clave API guardada (" . strlen($saved_key) . " caracteres)</p>";
    
    // Desencriptar y verificar
    $decrypted = $plugin_instance->decrypt_api_key($saved_key);
    if (!empty($decrypted)) {
        echo "<p style='color: green;'>✅ Clave API se desencripta correctamente</p>";
        echo "<p>Clave desencriptada: " . substr($decrypted, 0, 10) . "...</p>";
    } else {
        echo "<p style='color: red;'>❌ Error al desencriptar clave API</p>";
    }
}

echo "<h3>4. Probar Carga de Opciones:</h3>";

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

echo "<h3>5. Probar Función de Prueba de API:</h3>";

// Simular la función de prueba de API
$_POST['nonce'] = wp_create_nonce('chatbot_ia_admin_nonce');
$_POST['api_key'] = $decrypted; // Usar la clave desencriptada

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

echo "<h3>6. Resumen:</h3>";

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
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 ¡Todo funciona correctamente!</p>";
    echo "<p>La clave API se guarda, encripta y desencripta correctamente.</p>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>⚠️ Hay problemas con el guardado de la clave API</p>";
    echo "<p>Revisa los elementos marcados con ❌</p>";
}

echo "<hr>";
echo "<p><a href='" . admin_url() . "'>← Volver al panel de administración</a></p>";
echo "<p><a href='" . admin_url('admin.php?page=chatbot-ia') . "'>→ Ir a configuración del Chatbot IA</a></p>";
?>
