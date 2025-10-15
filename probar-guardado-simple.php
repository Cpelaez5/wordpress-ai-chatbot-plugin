<?php
/**
 * Script simple para probar el guardado
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar que somos administradores
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

echo "<h2>Prueba Simple de Guardado</h2>";

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

// Clave API de prueba
$test_api_key = 'sk-prueba1234567890123456789012345678901234567890';
echo "<p>Probando con clave: " . substr($test_api_key, 0, 10) . "...</p>";

// Encriptar y guardar
$encrypted = $plugin_instance->encrypt_api_key($test_api_key);
echo "<p>✅ Encriptación: " . substr($encrypted, 0, 20) . "... (longitud: " . strlen($encrypted) . ")</p>";

$saved = update_option('chatbot_ia_api_key', $encrypted);
echo "<p>✅ Guardado: " . ($saved ? 'EXITOSO' : 'FALLÓ') . "</p>";

// Verificar
$saved_key = get_option('chatbot_ia_api_key', '');
echo "<p>✅ Verificación: " . (empty($saved_key) ? 'NO GUARDADA' : 'GUARDADA (' . strlen($saved_key) . ' caracteres)') . "</p>";

// Desencriptar
$decrypted = $plugin_instance->decrypt_api_key($saved_key);
echo "<p>✅ Desencriptación: " . ($decrypted === $test_api_key ? 'CORRECTA' : 'ERROR') . "</p>";

echo "<h3>3. Probar Función de Sanitización:</h3>";

$sanitized = $plugin_instance->sanitize_api_key($test_api_key);
echo "<p>✅ Sanitización: " . (empty($sanitized) ? 'VACÍA' : 'ENCRIPTADA (' . strlen($sanitized) . ' caracteres)') . "</p>";

echo "<h3>4. Resumen:</h3>";

if (!empty(get_option('chatbot_ia_api_key', ''))) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 ¡El guardado funciona correctamente!</p>";
    echo "<p>El problema puede estar en el frontend (JavaScript) o en la configuración del formulario.</p>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>⚠️ Hay problemas con el guardado</p>";
}

echo "<hr>";
echo "<p><a href='" . admin_url() . "'>← Volver al panel de administración</a></p>";
echo "<p><a href='" . admin_url('admin.php?page=chatbot-ia') . "'>→ Ir a configuración del Chatbot IA</a></p>";
?>