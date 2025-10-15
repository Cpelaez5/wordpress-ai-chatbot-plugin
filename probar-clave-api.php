<?php
/**
 * Script para probar específicamente el guardado de la clave API
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar que somos administradores
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

echo "<h2>Probando Guardado de Clave API...</h2>";

// Verificar que el plugin esté activo
if (!class_exists('Chatbot_IA')) {
    echo "<p style='color: red;'>❌ Plugin no está activo</p>";
    exit;
}

$plugin_instance = Chatbot_IA::get_instance();

echo "<h3>1. Estado Actual de la Clave API:</h3>";

// Verificar clave API actual
$encrypted_api_key = get_option('chatbot_ia_api_key', '');
if (empty($encrypted_api_key)) {
    echo "<p style='color: red;'>❌ No hay clave API guardada</p>";
} else {
    echo "<p style='color: green;'>✅ Clave API guardada (encriptada, " . strlen($encrypted_api_key) . " caracteres)</p>";
    
    // Intentar desencriptar
    $decrypted_key = $plugin_instance->decrypt_api_key($encrypted_api_key);
    if (!empty($decrypted_key)) {
        echo "<p style='color: green;'>✅ Clave API se puede desencriptar correctamente</p>";
        echo "<p>Clave desencriptada: " . substr($decrypted_key, 0, 10) . "...</p>";
    } else {
        echo "<p style='color: red;'>❌ Error al desencriptar la clave API</p>";
    }
}

echo "<h3>2. Probar Guardado de Nueva Clave API:</h3>";

// Clave API de prueba
$test_api_key = 'sk-a42f9205e7ba4a338c80bc8c1f1fd88e';

echo "<p>Probando con clave API: " . substr($test_api_key, 0, 10) . "...</p>";

// Simular el proceso de guardado
$encrypted_test_key = $plugin_instance->encrypt_api_key($test_api_key);
update_option('chatbot_ia_api_key', $encrypted_test_key);

echo "<p style='color: green;'>✅ Clave API guardada encriptada</p>";

// Verificar que se guardó correctamente
$saved_encrypted_key = get_option('chatbot_ia_api_key', '');
if (!empty($saved_encrypted_key)) {
    echo "<p style='color: green;'>✅ Clave API se guardó correctamente</p>";
    
    // Desencriptar y verificar
    $decrypted_saved_key = $plugin_instance->decrypt_api_key($saved_encrypted_key);
    if ($decrypted_saved_key === $test_api_key) {
        echo "<p style='color: green;'>✅ Clave API se desencripta correctamente</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: La clave desencriptada no coincide</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Error: La clave API no se guardó</p>";
}

echo "<h3>3. Probar Carga de Opciones:</h3>";

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

echo "<h3>4. Probar Función de Prueba de API:</h3>";

// Simular la función de prueba de API
$_POST['nonce'] = wp_create_nonce('chatbot_ia_admin_nonce');
$_POST['api_key'] = $test_api_key;

// Capturar la salida de la función de prueba
ob_start();
try {
    $plugin_instance->test_api_connection();
    $output = ob_get_clean();
    echo "<p style='color: green;'>✅ Función de prueba ejecutada sin errores</p>";
    echo "<p>Salida: " . htmlspecialchars($output) . "</p>";
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color: red;'>❌ Error en función de prueba: " . $e->getMessage() . "</p>";
}

echo "<h3>5. Verificar Base de Datos:</h3>";

// Verificar todas las opciones del plugin
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
    'chatbot_ia_rate_limit_window',
    'chatbot_ia_encryption_key'
);

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Opción</th><th>Valor</th><th>Estado</th></tr>";

foreach ($plugin_options as $option) {
    $value = get_option($option, 'NO CONFIGURADO');
    
    if ($option === 'chatbot_ia_api_key' && $value !== 'NO CONFIGURADO') {
        $display_value = 'ENCRIPTADA (' . strlen($value) . ' caracteres)';
        $status = '✅ Configurada';
    } elseif ($option === 'chatbot_ia_encryption_key' && $value !== 'NO CONFIGURADO') {
        $display_value = 'CONFIGURADA (' . strlen($value) . ' caracteres)';
        $status = '✅ Configurada';
    } else {
        $display_value = $value;
        $status = ($value !== 'NO CONFIGURADO') ? '✅ Configurada' : '❌ No configurada';
    }
    
    echo "<tr>";
    echo "<td><strong>$option</strong></td>";
    echo "<td>$display_value</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>6. Resumen:</h3>";

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
