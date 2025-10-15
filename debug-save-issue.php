<?php
/**
 * Debug del problema de guardado de API Key
 */

// Incluir WordPress
require_once('../../../wp-config.php');

echo "<h2>Debug del Problema de Guardado - API Key</h2>";

// 1. Verificar estado actual
echo "<h3>1. Estado Actual de la Base de Datos:</h3>";
$current_api_key = get_option('chatbot_ia_api_key', '');
if (empty($current_api_key)) {
    echo "<p>Clave API actual: <strong>NO CONFIGURADA</strong></p>";
} else {
    echo "<p>Clave API actual: <strong>CONFIGURADA</strong> (longitud: " . strlen($current_api_key) . " caracteres)</p>";
}

// 2. Simular el proceso de guardado
echo "<h3>2. Simular Proceso de Guardado:</h3>";
$test_api_key = 'sk-7b6c8658a95b4f699fc50a399f5de2e1';
echo "<p>Probando con clave: <strong>" . substr($test_api_key, 0, 10) . "...</strong></p>";
echo "<p>Longitud de la clave: <strong>" . strlen($test_api_key) . " caracteres</strong></p>";

// 3. Verificar validación
echo "<h3>3. Verificar Validación:</h3>";
if (preg_match('/^sk-[a-zA-Z0-9\-_]{32,}$/', $test_api_key)) {
    echo "<p style='color: green;'>✅ <strong>Formato de clave API válido</strong></p>";
} else {
    echo "<p style='color: red;'>❌ <strong>Formato de clave API inválido</strong></p>";
}

// 4. Probar encriptación
echo "<h3>4. Probar Encriptación:</h3>";
if (class_exists('Chatbot_IA')) {
    $plugin_instance = Chatbot_IA::get_instance();
    $encrypted_key = $plugin_instance->encrypt_api_key($test_api_key);
    echo "<p>✅ <strong>Encriptación:</strong> " . substr($encrypted_key, 0, 20) . "... (longitud: " . strlen($encrypted_key) . ")</p>";
    
    // 5. Probar guardado directo
    echo "<h3>5. Probar Guardado Directo:</h3>";
    
    // Eliminar opción existente
    delete_option('chatbot_ia_api_key');
    echo "<p>✅ <strong>Eliminada opción existente</strong></p>";
    
    // Guardar nueva clave
    $result = add_option('chatbot_ia_api_key', $encrypted_key);
    if ($result) {
        echo "<p>✅ <strong>Guardado:</strong> EXITOSO</p>";
        
        // Verificar que se guardó
        $saved_key = get_option('chatbot_ia_api_key', '');
        if (!empty($saved_key)) {
            echo "<p>✅ <strong>Verificación:</strong> GUARDADA (longitud: " . strlen($saved_key) . " caracteres)</p>";
            
            // Probar desencriptación
            $decrypted_key = $plugin_instance->decrypt_api_key($saved_key);
            if ($decrypted_key === $test_api_key) {
                echo "<p>✅ <strong>Desencriptación:</strong> CORRECTA</p>";
            } else {
                echo "<p>❌ <strong>Desencriptación:</strong> FALLÓ</p>";
            }
        } else {
            echo "<p>❌ <strong>Verificación:</strong> NO SE ENCONTRÓ EN BD</p>";
        }
    } else {
        echo "<p>❌ <strong>Guardado:</strong> FALLÓ</p>";
    }
} else {
    echo "<p>❌ <strong>Error:</strong> Clase Chatbot_IA no encontrada</p>";
}

// 6. Verificar logs de WordPress
echo "<h3>6. Verificar Logs de WordPress:</h3>";
$log_file = WP_CONTENT_DIR . '/debug.log';
if (file_exists($log_file)) {
    $logs = file_get_contents($log_file);
    $recent_logs = array_slice(explode("\n", $logs), -20); // Últimas 20 líneas
    echo "<p>✅ <strong>Archivo de log existe:</strong> " . $log_file . "</p>";
    echo "<h4>Últimas 20 líneas del log:</h4>";
    echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 300px; overflow-y: auto;'>";
    foreach ($recent_logs as $log) {
        if (strpos($log, 'Chatbot IA') !== false) {
            echo htmlspecialchars($log) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p>❌ <strong>Archivo de log no existe:</strong> " . $log_file . "</p>";
}

// 7. Verificar permisos de usuario
echo "<h3>7. Verificar Permisos:</h3>";
if (current_user_can('manage_options')) {
    echo "<p>✅ <strong>Usuario actual tiene permisos:</strong> manage_options</p>";
} else {
    echo "<p>❌ <strong>Usuario actual NO tiene permisos:</strong> manage_options</p>";
}

// 8. Verificar nonce
echo "<h3>8. Verificar Nonce:</h3>";
$nonce = wp_create_nonce('chatbot_ia_admin_nonce');
echo "<p>✅ <strong>Nonce generado:</strong> " . $nonce . "</p>";
if (wp_verify_nonce($nonce, 'chatbot_ia_admin_nonce')) {
    echo "<p>✅ <strong>Verificación de nonce:</strong> VÁLIDA</p>";
} else {
    echo "<p>❌ <strong>Verificación de nonce:</strong> INVÁLIDA</p>";
}

echo "<hr>";
echo "<p><strong>Conclusión:</strong> Si todos los pasos anteriores muestran ✅, el problema podría estar en el JavaScript o en la comunicación AJAX.</p>";
?>
