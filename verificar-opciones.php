<?php
/**
 * Script para verificar el estado de las opciones en la base de datos
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar que somos administradores
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

echo "<h2>Verificar Estado de Opciones en Base de Datos</h2>";

// Lista de opciones del plugin
$options = [
    'chatbot_ia_api_key' => 'Clave API',
    'chatbot_ia_system_instructions' => 'Instrucciones del Sistema',
    'chatbot_ia_context' => 'Contexto',
    'chatbot_ia_model' => 'Modelo',
    'chatbot_ia_max_tokens' => 'Máximo de Tokens',
    'chatbot_ia_temperature' => 'Temperatura',
    'chatbot_ia_default_language' => 'Idioma Predeterminado',
    'chatbot_ia_enable_caching' => 'Habilitar Cache',
    'chatbot_ia_cache_duration' => 'Duración del Cache',
    'chatbot_ia_rate_limit' => 'Límite de Velocidad',
    'chatbot_ia_rate_limit_window' => 'Ventana de Límite de Velocidad',
    'chatbot_ia_encryption_key' => 'Clave de Encriptación'
];

echo "<h3>Estado Actual de las Opciones:</h3>";

foreach ($options as $option => $label) {
    $value = get_option($option, 'NO EXISTE');
    $exists = ($value !== 'NO EXISTE');
    
    if ($exists) {
        if ($option === 'chatbot_ia_api_key') {
            echo "<p>✅ <strong>{$label}:</strong> EXISTE (" . strlen($value) . " caracteres encriptados)</p>";
        } else {
            echo "<p>✅ <strong>{$label}:</strong> {$value}</p>";
        }
    } else {
        echo "<p>❌ <strong>{$label}:</strong> NO EXISTE</p>";
    }
}

echo "<h3>Probar Guardado Manual:</h3>";

// Probar guardar una opción simple
$test_option = 'chatbot_ia_test_option';
$test_value = 'valor_de_prueba_' . time();

echo "<p>Probando guardar opción de prueba...</p>";

// Eliminar si existe
delete_option($test_option);

// Probar add_option
$result_add = add_option($test_option, $test_value);
echo "<p>add_option: " . ($result_add ? '✅ EXITOSO' : '❌ FALLÓ') . "</p>";

// Verificar que se guardó
$saved_value = get_option($test_option, 'NO GUARDADO');
echo "<p>Verificación: " . ($saved_value === $test_value ? '✅ CORRECTO' : '❌ INCORRECTO') . "</p>";

// Probar update_option
$new_value = 'nuevo_valor_' . time();
$result_update = update_option($test_option, $new_value);
echo "<p>update_option: " . ($result_update ? '✅ EXITOSO' : '❌ FALLÓ') . "</p>";

// Verificar que se actualizó
$updated_value = get_option($test_option, 'NO ACTUALIZADO');
echo "<p>Verificación: " . ($updated_value === $new_value ? '✅ CORRECTO' : '❌ INCORRECTO') . "</p>";

// Limpiar
delete_option($test_option);

echo "<h3>Probar Guardado de Clave API:</h3>";

// Probar guardar clave API
$test_api_key = 'sk-test-key-123456789012345678901234567890';
$encrypted_key = '';

if (class_exists('Chatbot_IA')) {
    $plugin_instance = Chatbot_IA::get_instance();
    $encrypted_key = $plugin_instance->encrypt_api_key($test_api_key);
    echo "<p>✅ Encriptación: " . substr($encrypted_key, 0, 20) . "...</p>";
    
    // Probar guardar
    $result_api = update_option('chatbot_ia_test_api_key', $encrypted_key);
    echo "<p>Guardado de clave API: " . ($result_api ? '✅ EXITOSO' : '❌ FALLÓ') . "</p>";
    
    // Verificar
    $saved_api_key = get_option('chatbot_ia_test_api_key', 'NO GUARDADO');
    echo "<p>Verificación: " . (strlen($saved_api_key) > 50 ? '✅ CORRECTO' : '❌ INCORRECTO') . "</p>";
    
    // Limpiar
    delete_option('chatbot_ia_test_api_key');
}

echo "<h3>Información del Sistema:</h3>";

echo "<p><strong>WordPress Version:</strong> " . get_bloginfo('version') . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>MySQL Version:</strong> " . $GLOBALS['wpdb']->db_version() . "</p>";
echo "<p><strong>Usuario de BD:</strong> " . DB_USER . "</p>";
echo "<p><strong>Host de BD:</strong> " . DB_HOST . "</p>";
echo "<p><strong>Nombre de BD:</strong> " . DB_NAME . "</p>";

echo "<hr>";
echo "<p><a href='" . admin_url() . "'>← Volver al panel de administración</a></p>";
echo "<p><a href='" . admin_url('admin.php?page=chatbot-ia') . "'>→ Ir a configuración del Chatbot IA</a></p>";
?>
