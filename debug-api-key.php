<?php
/**
 * Script de debug para verificar el guardado de la clave API
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar que somos administradores
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

echo "<h2>Debug de Clave API - Chatbot IA DeepSeek</h2>";

// Verificar que el plugin esté activo
if (!class_exists('Chatbot_IA')) {
    echo "<p style='color: red;'>❌ Plugin no está activo</p>";
    exit;
}

$plugin_instance = Chatbot_IA::get_instance();

echo "<h3>1. Estado Actual de la Base de Datos:</h3>";

// Verificar todas las opciones del plugin
$options_to_check = array(
    'chatbot_ia_api_key' => 'Clave API',
    'chatbot_ia_encryption_key' => 'Clave de Encriptación',
    'chatbot_ia_system_instructions' => 'Instrucciones del Sistema',
    'chatbot_ia_context' => 'Contexto',
    'chatbot_ia_model' => 'Modelo',
    'chatbot_ia_max_tokens' => 'Máximo de Tokens',
    'chatbot_ia_temperature' => 'Temperatura'
);

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Opción</th><th>Valor</th><th>Longitud</th><th>Estado</th></tr>";

foreach ($options_to_check as $option => $label) {
    $value = get_option($option, 'NO CONFIGURADO');
    
    if ($option === 'chatbot_ia_api_key' && $value !== 'NO CONFIGURADO') {
        $display_value = 'ENCRIPTADA';
        $length = strlen($value);
        $status = '✅ Configurada';
    } elseif ($option === 'chatbot_ia_encryption_key' && $value !== 'NO CONFIGURADO') {
        $display_value = 'CONFIGURADA';
        $length = strlen($value);
        $status = '✅ Configurada';
    } else {
        $display_value = $value;
        $length = strlen($value);
        $status = ($value !== 'NO CONFIGURADO') ? '✅ Configurada' : '❌ No configurada';
    }
    
    echo "<tr>";
    echo "<td><strong>$label</strong></td>";
    echo "<td>$display_value</td>";
    echo "<td>$length</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>2. Probar Encriptación/Desencriptación:</h3>";

// Clave API de prueba
$test_api_key = 'sk-a42f9205e7ba4a338c80bc8c1f1fd88e';

echo "<p>Clave API de prueba: " . substr($test_api_key, 0, 10) . "...</p>";

// Probar encriptación
$encrypted = $plugin_instance->encrypt_api_key($test_api_key);
echo "<p>✅ Encriptación: " . substr($encrypted, 0, 20) . "... (longitud: " . strlen($encrypted) . ")</p>";

// Probar desencriptación
$decrypted = $plugin_instance->decrypt_api_key($encrypted);
if ($decrypted === $test_api_key) {
    echo "<p>✅ Desencriptación: Correcta</p>";
} else {
    echo "<p>❌ Desencriptación: Error</p>";
}

echo "<h3>3. Probar Guardado Manual:</h3>";

// Guardar clave API de prueba
$encrypted_test_key = $plugin_instance->encrypt_api_key($test_api_key);
$saved = update_option('chatbot_ia_api_key', $encrypted_test_key);

if ($saved) {
    echo "<p>✅ Clave API guardada correctamente</p>";
} else {
    echo "<p>❌ Error al guardar clave API</p>";
}

// Verificar que se guardó
$saved_key = get_option('chatbot_ia_api_key', '');
if (!empty($saved_key)) {
    echo "<p>✅ Clave API verificada en base de datos</p>";
    
    // Desencriptar y verificar
    $decrypted_saved = $plugin_instance->decrypt_api_key($saved_key);
    if ($decrypted_saved === $test_api_key) {
        echo "<p>✅ Clave API se desencripta correctamente</p>";
    } else {
        echo "<p>❌ Error: La clave desencriptada no coincide</p>";
    }
} else {
    echo "<p>❌ Error: La clave API no se guardó</p>";
}

echo "<h3>4. Probar Carga de Opciones:</h3>";

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

echo "<h3>5. Probar Función de Prueba de API:</h3>";

// Simular la función de prueba de API
$_POST['nonce'] = wp_create_nonce('chatbot_ia_admin_nonce');
$_POST['api_key'] = $test_api_key;

echo "<p>Simulando función de prueba de API...</p>";

// Capturar la salida
ob_start();
try {
    $plugin_instance->test_api_connection();
    $output = ob_get_clean();
    echo "<p>✅ Función de prueba ejecutada</p>";
    echo "<p>Salida: " . htmlspecialchars($output) . "</p>";
} catch (Exception $e) {
    ob_end_clean();
    echo "<p>❌ Error en función de prueba: " . $e->getMessage() . "</p>";
}

echo "<h3>6. Verificar Permisos de Base de Datos:</h3>";

global $wpdb;

// Probar inserción directa
$test_data = array(
    'option_name' => 'chatbot_ia_test_option',
    'option_value' => 'test_value_' . time()
);

$inserted = $wpdb->insert($wpdb->options, $test_data);

if ($inserted) {
    echo "<p>✅ Permisos de escritura en base de datos: OK</p>";
    
    // Limpiar
    $wpdb->delete($wpdb->options, array('option_name' => 'chatbot_ia_test_option'));
} else {
    echo "<p>❌ Error de permisos en base de datos: " . $wpdb->last_error . "</p>";
}

echo "<h3>7. Resumen Final:</h3>";

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
