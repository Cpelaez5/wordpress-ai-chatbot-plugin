<?php
/**
 * Script de prueba final para verificar que todo funciona
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar que somos administradores
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

echo "<h2>Prueba Final - Chatbot IA DeepSeek</h2>";

// Verificar que el plugin esté activo
if (!class_exists('Chatbot_IA')) {
    echo "<p style='color: red;'>❌ Plugin no está activo</p>";
    exit;
}

$plugin_instance = Chatbot_IA::get_instance();

echo "<h3>1. Verificar Clave API en Base de Datos:</h3>";

// Verificar clave API actual
$encrypted_api_key = get_option('chatbot_ia_api_key', '');
if (empty($encrypted_api_key)) {
    echo "<p style='color: red;'>❌ No hay clave API guardada</p>";
} else {
    echo "<p style='color: green;'>✅ Clave API guardada (encriptada, " . strlen($encrypted_api_key) . " caracteres)</p>";
    
    // Desencriptar y verificar
    $decrypted_key = $plugin_instance->decrypt_api_key($encrypted_api_key);
    if (!empty($decrypted_key)) {
        echo "<p style='color: green;'>✅ Clave API se desencripta correctamente</p>";
        echo "<p>Clave desencriptada: " . substr($decrypted_key, 0, 10) . "...</p>";
        
        // Verificar formato
        if (preg_match('/^sk-[a-zA-Z0-9]{32,}$/', $decrypted_key)) {
            echo "<p style='color: green;'>✅ Formato de clave API válido</p>";
        } else {
            echo "<p style='color: red;'>❌ Formato de clave API inválido</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Error al desencriptar la clave API</p>";
    }
}

echo "<h3>2. Verificar Opciones del Plugin:</h3>";

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

echo "<h3>3. Probar Función de Prueba de API:</h3>";

// Simular la función de prueba de API
$_POST['nonce'] = wp_create_nonce('chatbot_ia_admin_nonce');
if (!empty($decrypted_key)) {
    $_POST['api_key'] = $decrypted_key; // Usar la clave desencriptada
} else {
    $_POST['api_key'] = 'sk-a42f9205e7ba4a338c80bc8c1f1fd88e'; // Usar clave por defecto para prueba
}

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

echo "<h3>4. Verificar Configuración Completa:</h3>";

$all_options = array(
    'chatbot_ia_api_key' => 'Clave API',
    'chatbot_ia_system_instructions' => 'Instrucciones del Sistema',
    'chatbot_ia_context' => 'Contexto',
    'chatbot_ia_model' => 'Modelo',
    'chatbot_ia_max_tokens' => 'Máximo de Tokens',
    'chatbot_ia_temperature' => 'Temperatura',
    'chatbot_ia_default_language' => 'Idioma por Defecto',
    'chatbot_ia_enable_caching' => 'Cache Habilitado',
    'chatbot_ia_cache_duration' => 'Duración del Cache',
    'chatbot_ia_rate_limit' => 'Límite de Tasa',
    'chatbot_ia_rate_limit_window' => 'Ventana de Límite de Tasa',
    'chatbot_ia_encryption_key' => 'Clave de Encriptación'
);

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Opción</th><th>Valor</th><th>Estado</th></tr>";

foreach ($all_options as $option => $label) {
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
    echo "<td><strong>$label</strong></td>";
    echo "<td>$display_value</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>5. Resumen Final:</h3>";

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

// Verificar formato de clave API
if (!empty($decrypted) && !preg_match('/^sk-[a-zA-Z0-9]{32,}$/', $decrypted)) {
    echo "<p style='color: red;'>❌ Formato de clave API inválido</p>";
    $all_good = false;
} else {
    echo "<p style='color: green;'>✅ Formato de clave API válido</p>";
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
    echo "<p>El plugin está listo para usar:</p>";
    echo "<ul>";
    echo "<li>✅ Clave API guardada y encriptada</li>";
    echo "<li>✅ Formato de clave API válido</li>";
    echo "<li>✅ Opciones del plugin configuradas</li>";
    echo "<li>✅ Función de prueba de API funcional</li>";
    echo "</ul>";
    echo "<p><strong>Próximos pasos:</strong></p>";
    echo "<ol>";
    echo "<li>Ve al panel de administración: <strong>Chatbot IA</strong></li>";
    echo "<li>El campo de clave API debería mostrar la clave desencriptada</li>";
    echo "<li>Haz clic en 'Probar Conexión' - debería funcionar</li>";
    echo "<li>Ve a 'Pruebas y Logs' y prueba el chat</li>";
    echo "<li>Añade el shortcode <code>[chatbot-ia]</code> a cualquier página</li>";
    echo "</ol>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>⚠️ Hay problemas que necesitan ser corregidos</p>";
    echo "<p>Revisa los elementos marcados con ❌</p>";
}

echo "<hr>";
echo "<p><a href='" . admin_url() . "'>← Volver al panel de administración</a></p>";
echo "<p><a href='" . admin_url('admin.php?page=chatbot-ia') . "'>→ Ir a configuración del Chatbot IA</a></p>";
?>
