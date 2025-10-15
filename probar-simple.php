<?php
/**
 * Script de prueba simple para verificar el guardado de la clave API
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar que somos administradores
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

echo "<h2>Prueba Simple - Clave API</h2>";

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

// Verificar clave de encriptación
$encryption_key = get_option('chatbot_ia_encryption_key', '');
echo "<p>Clave de encriptación: " . (empty($encryption_key) ? 'NO CONFIGURADA' : 'CONFIGURADA (' . strlen($encryption_key) . ' caracteres)') . "</p>";

echo "<h3>2. Probar Guardado Directo:</h3>";

// Clave API de prueba
$test_api_key = 'sk-a42f9205e7ba4a338c80bc8c1f1fd88e';
echo "<p>Probando con clave: " . substr($test_api_key, 0, 10) . "...</p>";

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

echo "<h3>3. Probar Carga de Opciones:</h3>";

// Recargar opciones del plugin
$plugin_instance->load_options();

// Verificar que la clave API esté disponible en las opciones
$options = $plugin_instance->get_options();
if (isset($options['api_key']) && !empty($options['api_key'])) {
    echo "<p>✅ Clave API disponible en opciones del plugin</p>";
    echo "<p>Clave en opciones: " . substr($options['api_key'], 0, 10) . "...</p>";
} else {
    echo "<p>❌ Clave API no disponible en opciones del plugin</p>";
}

echo "<h3>4. Probar Función de Sanitización:</h3>";

// Probar la función de sanitización
$sanitized = $plugin_instance->sanitize_api_key($test_api_key);
echo "<p>✅ Sanitización: " . (empty($sanitized) ? 'VACÍA' : 'ENCRIPTADA (' . strlen($sanitized) . ' caracteres)') . "</p>";

echo "<h3>5. Resumen:</h3>";

$all_good = true;

// Verificar que la clave API esté guardada
if (empty(get_option('chatbot_ia_api_key', ''))) {
    echo "<p style='color: red;'>❌ Clave API no guardada</p>";
    $all_good = false;
} else {
    echo "<p style='color: green;'>✅ Clave API guardada</p>";
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
