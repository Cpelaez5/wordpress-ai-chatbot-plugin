<?php
/**
 * Test Específico de Guardado - Simular Exactamente lo que hace el Plugin
 */

// Cargar WordPress
require_once('../../../wp-load.php');

echo "<h2>Test Específico de Guardado</h2>";

// Simular exactamente lo que hace el plugin
$test_api_key = 'sk-bcfd2441b809475ba468d7f10f0124f6';

echo "<h3>1. Estado Inicial:</h3>";
$initial_key = get_option('chatbot_ia_api_key', 'NO_EXISTE');
echo "<p><strong>Clave API inicial:</strong> " . ($initial_key === 'NO_EXISTE' ? 'NO EXISTE' : 'EXISTE (' . strlen($initial_key) . ' caracteres)') . "</p>";

echo "<h3>2. Simular Proceso del Plugin:</h3>";

// Paso 1: Validar clave API (como hace el plugin)
echo "<p><strong>Paso 1 - Validación:</strong> ";
if (preg_match('/^sk-[a-zA-Z0-9\-_]{32,}$/', $test_api_key)) {
    echo "✅ VÁLIDA</p>";
} else {
    echo "❌ INVÁLIDA</p>";
    exit;
}

// Paso 2: Encriptar (como hace el plugin)
echo "<p><strong>Paso 2 - Encriptación:</strong> ";
if (class_exists('Chatbot_IA')) {
    $plugin_instance = Chatbot_IA::get_instance();
    $encrypted_key = $plugin_instance->encrypt_api_key($test_api_key);
    echo "✅ ENCRIPTADA (" . strlen($encrypted_key) . " caracteres)</p>";
} else {
    echo "❌ ERROR: Clase no encontrada</p>";
    exit;
}

// Paso 3: Eliminar opción existente (como hace el plugin)
echo "<p><strong>Paso 3 - Eliminar existente:</strong> ";
$delete_result = delete_option('chatbot_ia_api_key');
echo ($delete_result ? "✅ ELIMINADA" : "⚠️ NO EXISTÍA") . "</p>";

// Paso 4: Guardar con add_option (como hace el plugin)
echo "<p><strong>Paso 4 - Guardar con add_option:</strong> ";
$save_result = add_option('chatbot_ia_api_key', $encrypted_key);
echo ($save_result ? "✅ GUARDADA" : "❌ FALLÓ") . "</p>";

// Paso 5: Verificar guardado
echo "<p><strong>Paso 5 - Verificar guardado:</strong> ";
$saved_key = get_option('chatbot_ia_api_key', 'NO_EXISTE');
if ($saved_key === $encrypted_key) {
    echo "✅ CORRECTO</p>";
} else {
    echo "❌ INCORRECTO</p>";
    echo "<p><strong>Esperado:</strong> " . substr($encrypted_key, 0, 20) . "...</p>";
    echo "<p><strong>Obtenido:</strong> " . ($saved_key === 'NO_EXISTE' ? 'NO_EXISTE' : substr($saved_key, 0, 20) . '...') . "</p>";
}

// Paso 6: Probar desencriptación
echo "<p><strong>Paso 6 - Desencriptación:</strong> ";
$decrypted_key = $plugin_instance->decrypt_api_key($saved_key);
if ($decrypted_key === $test_api_key) {
    echo "✅ CORRECTA</p>";
} else {
    echo "❌ INCORRECTA</p>";
    echo "<p><strong>Esperado:</strong> {$test_api_key}</p>";
    echo "<p><strong>Obtenido:</strong> {$decrypted_key}</p>";
}

echo "<h3>3. Estado Final:</h3>";
$final_key = get_option('chatbot_ia_api_key', 'NO_EXISTE');
echo "<p><strong>Clave API final:</strong> " . ($final_key === 'NO_EXISTE' ? 'NO EXISTE' : 'EXISTE (' . strlen($final_key) . ' caracteres)') . "</p>";

// Verificar si se puede desencriptar
if ($final_key !== 'NO_EXISTE') {
    $final_decrypted = $plugin_instance->decrypt_api_key($final_key);
    echo "<p><strong>Clave desencriptada:</strong> " . substr($final_decrypted, 0, 20) . "...</p>";
}

echo "<h3>4. Test de Actualización:</h3>";

// Probar actualizar con una nueva clave
$new_test_key = 'sk-nueva-clave-de-prueba-123456789012345678901234567890';
echo "<p><strong>Nueva clave de prueba:</strong> {$new_test_key}</p>";

// Encriptar nueva clave
$new_encrypted = $plugin_instance->encrypt_api_key($new_test_key);
echo "<p><strong>Nueva clave encriptada:</strong> " . substr($new_encrypted, 0, 20) . "...</p>";

// Eliminar y guardar nueva clave
delete_option('chatbot_ia_api_key');
$update_result = add_option('chatbot_ia_api_key', $new_encrypted);
echo "<p><strong>Resultado actualización:</strong> " . ($update_result ? "✅ EXITOSO" : "❌ FALLÓ") . "</p>";

// Verificar nueva clave
$updated_key = get_option('chatbot_ia_api_key', 'NO_EXISTE');
$updated_decrypted = $plugin_instance->decrypt_api_key($updated_key);
echo "<p><strong>Clave actualizada desencriptada:</strong> " . substr($updated_decrypted, 0, 20) . "...</p>";

if ($updated_decrypted === $new_test_key) {
    echo "<p style='color: green;'><strong>✅ ¡La actualización funciona correctamente!</strong></p>";
} else {
    echo "<p style='color: red;'><strong>❌ La actualización falló</strong></p>";
}

echo "<hr>";
echo "<p><a href='../admin/admin-page.php'>← Volver al panel de administración</a></p>";
?>
