<?php
/**
 * Script para limpiar la clave API vacía y forzar el guardado
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar que somos administradores
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

echo "<h2>Limpiar Clave API Vacía</h2>";

// Verificar que el plugin esté activo
if (!class_exists('Chatbot_IA')) {
    echo "<p style='color: red;'>❌ Plugin no está activo</p>";
    exit;
}

$plugin_instance = Chatbot_IA::get_instance();

echo "<h3>1. Estado Actual:</h3>";

// Verificar valor actual
$current_value = get_option('chatbot_ia_api_key', 'NO EXISTE');
echo "<p><strong>Valor actual:</strong> " . ($current_value === 'NO EXISTE' ? 'NO EXISTE' : 'EXISTE') . "</p>";
echo "<p><strong>Longitud:</strong> " . strlen($current_value) . " caracteres</p>";

if (empty($current_value)) {
    echo "<p style='color: red;'>❌ La clave API está VACÍA</p>";
} else {
    echo "<p style='color: green;'>✅ La clave API tiene contenido</p>";
}

echo "<h3>2. Limpiar Clave API Vacía:</h3>";

// Eliminar la opción si está vacía
if (empty($current_value)) {
    $deleted = delete_option('chatbot_ia_api_key');
    echo "<p>Eliminar clave API vacía: " . ($deleted ? '✅ EXITOSO' : '❌ FALLÓ') . "</p>";
} else {
    echo "<p>La clave API no está vacía, no se elimina</p>";
}

echo "<h3>3. Probar Guardado de Nueva Clave API:</h3>";

// Clave API de prueba
$test_api_key = 'sk-bcfd2441b809475ba468d7f10f0124f6';
$encrypted_key = $plugin_instance->encrypt_api_key($test_api_key);

echo "<p><strong>Clave original:</strong> {$test_api_key}</p>";
echo "<p><strong>Clave encriptada:</strong> " . substr($encrypted_key, 0, 20) . "...</p>";

// Probar guardar
$result = add_option('chatbot_ia_api_key', $encrypted_key);
echo "<p>Guardar nueva clave API: " . ($result ? '✅ EXITOSO' : '❌ FALLÓ') . "</p>";

// Verificar
$saved_value = get_option('chatbot_ia_api_key', 'NO GUARDADO');
echo "<p><strong>Valor guardado:</strong> " . (strlen($saved_value) > 50 ? 'ENCRIPTADO (' . strlen($saved_value) . ' caracteres)' : 'VACÍO') . "</p>";

echo "<h3>4. Probar Desencriptación:</h3>";

if (!empty($saved_value)) {
    $decrypted = $plugin_instance->decrypt_api_key($saved_value);
    echo "<p><strong>Clave desencriptada:</strong> " . substr($decrypted, 0, 10) . "...</p>";
    echo "<p><strong>Coincide con original:</strong> " . ($decrypted === $test_api_key ? '✅ SÍ' : '❌ NO') . "</p>";
} else {
    echo "<p style='color: red;'>❌ No hay clave para desencriptar</p>";
}

echo "<h3>5. Estado Final:</h3>";

$final_value = get_option('chatbot_ia_api_key', 'NO EXISTE');
echo "<p><strong>Clave API final:</strong> " . (strlen($final_value) > 50 ? 'ENCRIPTADA (' . strlen($final_value) . ' caracteres)' : 'VACÍA') . "</p>";

if (strlen($final_value) > 50) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 ¡Clave API guardada correctamente!</p>";
    echo "<p>Ahora puedes probar en el panel de administración.</p>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>⚠️ La clave API sigue vacía</p>";
    echo "<p>Hay un problema con el guardado.</p>";
}

echo "<hr>";
echo "<p><a href='" . admin_url() . "'>← Volver al panel de administración</a></p>";
echo "<p><a href='" . admin_url('admin.php?page=chatbot-ia') . "'>→ Ir a configuración del Chatbot IA</a></p>";
?>
