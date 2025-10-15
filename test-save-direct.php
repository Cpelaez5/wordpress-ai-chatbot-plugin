<?php
/**
 * Test directo de guardado de API Key
 */

// Incluir WordPress
require_once('../../../wp-config.php');

echo "<h2>Test Directo de Guardado - API Key</h2>";

// 1. Verificar estado actual
echo "<h3>1. Estado Actual:</h3>";
$current_api_key = get_option('chatbot_ia_api_key', '');
if (empty($current_api_key)) {
    echo "<p>Clave API actual: <strong>NO CONFIGURADA</strong></p>";
} else {
    echo "<p>Clave API actual: <strong>CONFIGURADA</strong> (longitud: " . strlen($current_api_key) . " caracteres)</p>";
}

// 2. Probar guardado directo
echo "<h3>2. Probar Guardado Directo:</h3>";
$test_api_key = 'sk-7b6c8658a95b4f699fc50a399f5de2e1';
echo "<p>Probando con clave: <strong>" . substr($test_api_key, 0, 10) . "...</strong></p>";

// Verificar que la clase existe
if (class_exists('Chatbot_IA')) {
    $plugin_instance = Chatbot_IA::get_instance();
    
    // Encriptar la clave
    $encrypted_key = $plugin_instance->encrypt_api_key($test_api_key);
    echo "<p>✅ <strong>Encriptación:</strong> " . substr($encrypted_key, 0, 20) . "... (longitud: " . strlen($encrypted_key) . ")</p>";
    
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
                echo "<p>🎉 <strong>¡Todo funciona correctamente!</strong></p>";
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

echo "<hr>";
echo "<p><strong>Si este test funciona, el problema está en el JavaScript o en la comunicación AJAX.</strong></p>";
echo "<p><strong>Si este test falla, el problema está en la lógica de guardado del plugin.</strong></p>";
?>
