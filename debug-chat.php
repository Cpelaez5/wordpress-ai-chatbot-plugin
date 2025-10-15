<?php
/**
 * Debug del Chat - Investigar Error Desconocido
 */

// Cargar WordPress
require_once('../../../wp-load.php');

echo "<h2>Debug del Chat - Investigar Error Desconocido</h2>";

// 1. Verificar estado del plugin
echo "<h3>1. Estado del Plugin:</h3>";

if (class_exists('Chatbot_IA')) {
    $plugin_instance = Chatbot_IA::get_instance();
    echo "<p>✅ Plugin cargado correctamente</p>";
    
    // Obtener opciones
    $options = $plugin_instance->get_options();
    echo "<p><strong>Opciones cargadas:</strong> " . (empty($options) ? '❌ VACÍAS' : '✅ OK') . "</p>";
    
    if (!empty($options)) {
        echo "<p><strong>Clave API:</strong> " . (empty($options['api_key']) ? '❌ VACÍA' : '✅ CONFIGURADA') . "</p>";
        echo "<p><strong>Modelo:</strong> " . ($options['model'] ?? 'NO CONFIGURADO') . "</p>";
        echo "<p><strong>Max Tokens:</strong> " . ($options['max_tokens'] ?? 'NO CONFIGURADO') . "</p>";
        echo "<p><strong>Temperatura:</strong> " . ($options['temperature'] ?? 'NO CONFIGURADO') . "</p>";
    }
} else {
    echo "<p>❌ Plugin no encontrado</p>";
    exit;
}

// 2. Verificar clave API
echo "<h3>2. Verificar Clave API:</h3>";

$encrypted_api_key = get_option('chatbot_ia_api_key', '');
if (empty($encrypted_api_key)) {
    echo "<p>❌ No hay clave API guardada</p>";
} else {
    echo "<p>✅ Clave API encriptada encontrada (" . strlen($encrypted_api_key) . " caracteres)</p>";
    
    // Intentar desencriptar
    $decrypted_key = $plugin_instance->decrypt_api_key($encrypted_api_key);
    if (empty($decrypted_key)) {
        echo "<p>❌ Error al desencriptar la clave API</p>";
    } else {
        echo "<p>✅ Clave API desencriptada correctamente: " . substr($decrypted_key, 0, 20) . "...</p>";
    }
}

// 3. Probar función de consulta directamente
echo "<h3>3. Probar Consulta Directa:</h3>";

$test_message = "Hola, ¿cómo estás?";
echo "<p><strong>Mensaje de prueba:</strong> {$test_message}</p>";

// Usar reflexión para acceder al método privado
$reflection = new ReflectionClass($plugin_instance);
$method = $reflection->getMethod('query_deepseek_api');
$method->setAccessible(true);

try {
    $response = $method->invoke($plugin_instance, $test_message);
    
    if (is_wp_error($response)) {
        echo "<p style='color: red;'>❌ Error en consulta: " . $response->get_error_message() . "</p>";
        echo "<p><strong>Código de error:</strong> " . $response->get_error_code() . "</p>";
    } else {
        echo "<p style='color: green;'>✅ Consulta exitosa</p>";
        echo "<p><strong>Respuesta:</strong> " . substr($response, 0, 100) . "...</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Excepción: " . $e->getMessage() . "</p>";
}

// 4. Probar función de consulta con clave específica
echo "<h3>4. Probar Consulta con Clave Específica:</h3>";

if (!empty($decrypted_key)) {
    $method_with_key = $reflection->getMethod('query_deepseek_api_with_key');
    $method_with_key->setAccessible(true);
    
    try {
        $response_with_key = $method_with_key->invoke($plugin_instance, $test_message, $decrypted_key);
        
        if (is_wp_error($response_with_key)) {
            echo "<p style='color: red;'>❌ Error en consulta con clave: " . $response_with_key->get_error_message() . "</p>";
            echo "<p><strong>Código de error:</strong> " . $response_with_key->get_error_code() . "</p>";
        } else {
            echo "<p style='color: green;'>✅ Consulta con clave exitosa</p>";
            echo "<p><strong>Respuesta:</strong> " . substr($response_with_key, 0, 100) . "...</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Excepción con clave: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>⚠️ No se puede probar con clave específica (clave no disponible)</p>";
}

// 5. Verificar logs recientes
echo "<h3>5. Logs Recientes:</h3>";

$log_file = ABSPATH . 'wp-content/debug.log';
if (file_exists($log_file)) {
    $logs = file_get_contents($log_file);
    $lines = explode("\n", $logs);
    $recent_lines = array_slice($lines, -30); // Últimas 30 líneas
    
    echo "<div style='background: #f0f0f0; padding: 10px; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto;'>";
    foreach ($recent_lines as $line) {
        if (strpos($line, 'Chatbot IA') !== false || strpos($line, 'chatbot') !== false) {
            echo htmlspecialchars($line) . "<br>";
        }
    }
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ Archivo de log no encontrado</p>";
}

// 6. Probar AJAX manualmente
echo "<h3>6. Probar AJAX Manualmente:</h3>";

// Simular datos POST
$_POST['nonce'] = wp_create_nonce('chatbot_ia_nonce');
$_POST['message'] = $test_message;

echo "<p><strong>Nonce generado:</strong> " . $_POST['nonce'] . "</p>";
echo "<p><strong>Mensaje:</strong> " . $_POST['message'] . "</p>";

// Capturar salida
ob_start();
try {
    $plugin_instance->handle_chat_query();
    $output = ob_get_clean();
    echo "<p style='color: green;'>✅ AJAX ejecutado sin errores</p>";
    echo "<p><strong>Salida:</strong> " . htmlspecialchars($output) . "</p>";
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color: red;'>❌ Error en AJAX: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='../admin/admin-page.php'>← Volver al panel de administración</a></p>";
?>
