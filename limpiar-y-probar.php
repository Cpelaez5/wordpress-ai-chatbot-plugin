<?php
/**
 * Limpiar y Probar - Solucionar Problema de Encriptación
 */

// Cargar WordPress
require_once('../../../wp-load.php');

echo "<h2>Limpiar y Probar - Solucionar Problema de Encriptación</h2>";

// 1. Limpiar todas las opciones del plugin
echo "<h3>1. Limpiando Base de Datos:</h3>";

$plugin_options = array(
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
    'chatbot_ia_rate_limit_window'
);

foreach ($plugin_options as $option) {
    $result = delete_option($option);
    echo "<p>Eliminando {$option}: " . ($result ? "✅ EXITOSO" : "⚠️ NO EXISTÍA") . "</p>";
}

// 2. Cargar la clase del plugin
echo "<h3>2. Cargando Plugin:</h3>";
if (class_exists('Chatbot_IA')) {
    $plugin_instance = Chatbot_IA::get_instance();
    echo "<p>✅ Plugin cargado correctamente</p>";
} else {
    echo "<p>❌ Error: Plugin no encontrado</p>";
    exit;
}

// 3. Probar encriptación/desencriptación con la nueva función
echo "<h3>3. Probando Encriptación/Desencriptación:</h3>";

$test_key = 'sk-bcfd2441b809475ba468d7f10f0124f6';
echo "<p><strong>Clave de prueba:</strong> {$test_key}</p>";

// Encriptar
$encrypted = $plugin_instance->encrypt_api_key($test_key);
echo "<p><strong>Encriptada:</strong> " . substr($encrypted, 0, 30) . "... (" . strlen($encrypted) . " caracteres)</p>";

// Desencriptar
$decrypted = $plugin_instance->decrypt_api_key($encrypted);
echo "<p><strong>Desencriptada:</strong> {$decrypted}</p>";

if ($decrypted === $test_key) {
    echo "<p style='color: green;'><strong>✅ ¡Encriptación/Desencriptación funciona correctamente!</strong></p>";
} else {
    echo "<p style='color: red;'><strong>❌ Error en encriptación/desencriptación</strong></p>";
    echo "<p><strong>Esperado:</strong> {$test_key}</p>";
    echo "<p><strong>Obtenido:</strong> {$decrypted}</p>";
    exit;
}

// 4. Guardar configuración completa
echo "<h3>4. Guardando Configuración Completa:</h3>";

$settings = array(
    'chatbot_ia_api_key' => $encrypted,
    'chatbot_ia_system_instructions' => 'Responde de manera útil y concisa como un agente de soporte al cliente. Siempre responde en español.',
    'chatbot_ia_context' => 'Eres un asistente de IA especializado en ayudar a los usuarios. Responde siempre en español independientemente del idioma de la consulta del usuario.',
    'chatbot_ia_model' => 'deepseek-chat',
    'chatbot_ia_max_tokens' => 1000,
    'chatbot_ia_temperature' => 1,
    'chatbot_ia_default_language' => 'es',
    'chatbot_ia_enable_caching' => true,
    'chatbot_ia_cache_duration' => 3600,
    'chatbot_ia_rate_limit' => 10,
    'chatbot_ia_rate_limit_window' => 60
);

foreach ($settings as $option => $value) {
    $result = add_option($option, $value);
    echo "<p>Guardando {$option}: " . ($result ? "✅ EXITOSO" : "❌ FALLÓ") . "</p>";
}

// 5. Verificar guardado
echo "<h3>5. Verificando Guardado:</h3>";

$saved_api_key = get_option('chatbot_ia_api_key', 'NO_EXISTE');
if ($saved_api_key !== 'NO_EXISTE') {
    echo "<p>✅ Clave API guardada: " . strlen($saved_api_key) . " caracteres</p>";
    
    // Probar desencriptación de la clave guardada
    $saved_decrypted = $plugin_instance->decrypt_api_key($saved_api_key);
    echo "<p><strong>Clave desencriptada desde BD:</strong> " . substr($saved_decrypted, 0, 20) . "...</p>";
    
    if ($saved_decrypted === $test_key) {
        echo "<p style='color: green;'><strong>✅ ¡La clave se guarda y desencripta correctamente!</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>❌ Error al desencriptar desde BD</strong></p>";
    }
} else {
    echo "<p style='color: red;'>❌ Clave API no guardada</p>";
}

// 6. Estado final
echo "<h3>6. Estado Final:</h3>";
echo "<p><strong>Clave API en BD:</strong> " . (get_option('chatbot_ia_api_key', 'NO_EXISTE') !== 'NO_EXISTE' ? '✅ EXISTE' : '❌ NO EXISTE') . "</p>";
echo "<p><strong>Clave de Encriptación:</strong> " . (get_option('chatbot_ia_encryption_key', 'NO_EXISTE') !== 'NO_EXISTE' ? '✅ EXISTE' : '❌ NO EXISTE') . "</p>";

echo "<hr>";
echo "<p><strong>🎉 ¡Proceso completado!</strong></p>";
echo "<p>Ahora puedes probar en el panel de administración:</p>";
echo "<p><a href='../admin/admin-page.php' target='_blank'>→ Ir al Panel de Administración</a></p>";
?>
