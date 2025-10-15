<?php
/**
 * Script de diagnóstico completo para el problema de guardado
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar que somos administradores
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

echo "<h2>Diagnóstico Completo del Problema de Guardado</h2>";

// Verificar que el plugin esté activo
if (!class_exists('Chatbot_IA')) {
    echo "<p style='color: red;'>❌ Plugin no está activo</p>";
    exit;
}

$plugin_instance = Chatbot_IA::get_instance();

echo "<h3>1. Verificar Estado de la Base de Datos:</h3>";

// Verificar conexión a la base de datos
global $wpdb;
echo "<p><strong>Conexión a BD:</strong> " . ($wpdb->db_connect_error ? '❌ ERROR: ' . $wpdb->db_connect_error : '✅ OK') . "</p>";

// Verificar tabla wp_options
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->options}'");
echo "<p><strong>Tabla wp_options:</strong> " . ($table_exists ? '✅ EXISTE' : '❌ NO EXISTE') . "</p>";

// Verificar permisos de escritura
$test_option = 'chatbot_ia_test_permissions_' . time();
$test_value = 'test_value_' . time();

echo "<h3>2. Probar Permisos de Base de Datos:</h3>";

// Probar INSERT
$result_insert = $wpdb->insert(
    $wpdb->options,
    array(
        'option_name' => $test_option,
        'option_value' => $test_value,
        'autoload' => 'no'
    ),
    array('%s', '%s', '%s')
);

echo "<p><strong>INSERT directo:</strong> " . ($result_insert ? '✅ EXITOSO' : '❌ FALLÓ') . "</p>";
if (!$result_insert) {
    echo "<p><strong>Error INSERT:</strong> " . $wpdb->last_error . "</p>";
}

// Probar SELECT
$result_select = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $test_option));
echo "<p><strong>SELECT directo:</strong> " . ($result_select === $test_value ? '✅ EXITOSO' : '❌ FALLÓ') . "</p>";

// Probar UPDATE
$new_value = 'updated_value_' . time();
$result_update = $wpdb->update(
    $wpdb->options,
    array('option_value' => $new_value),
    array('option_name' => $test_option),
    array('%s'),
    array('%s')
);

echo "<p><strong>UPDATE directo:</strong> " . ($result_update !== false ? '✅ EXITOSO' : '❌ FALLÓ') . "</p>";
if ($result_update === false) {
    echo "<p><strong>Error UPDATE:</strong> " . $wpdb->last_error . "</p>";
}

// Limpiar
$wpdb->delete($wpdb->options, array('option_name' => $test_option), array('%s'));

echo "<h3>3. Probar Funciones de WordPress:</h3>";

// Probar add_option
$test_option_wp = 'chatbot_ia_test_wp_' . time();
$test_value_wp = 'test_wp_value_' . time();

$result_add = add_option($test_option_wp, $test_value_wp);
echo "<p><strong>add_option():</strong> " . ($result_add ? '✅ EXITOSO' : '❌ FALLÓ') . "</p>";

// Probar get_option
$result_get = get_option($test_option_wp, 'NO_ENCONTRADO');
echo "<p><strong>get_option():</strong> " . ($result_get === $test_value_wp ? '✅ EXITOSO' : '❌ FALLÓ') . "</p>";

// Probar update_option
$new_value_wp = 'updated_wp_value_' . time();
$result_update_wp = update_option($test_option_wp, $new_value_wp);
echo "<p><strong>update_option():</strong> " . ($result_update_wp ? '✅ EXITOSO' : '❌ FALLÓ') . "</p>";

// Verificar update
$result_get_updated = get_option($test_option_wp, 'NO_ENCONTRADO');
echo "<p><strong>get_option() después de update:</strong> " . ($result_get_updated === $new_value_wp ? '✅ EXITOSO' : '❌ FALLÓ') . "</p>";

// Limpiar
delete_option($test_option_wp);

echo "<h3>4. Probar Clave API Específica:</h3>";

// Verificar estado actual
$current_api_key = get_option('chatbot_ia_api_key', 'NO_EXISTE');
echo "<p><strong>Clave API actual:</strong> " . (strlen($current_api_key) > 50 ? 'ENCRIPTADA (' . strlen($current_api_key) . ' caracteres)' : 'VACÍA') . "</p>";

// Probar eliminar y añadir
echo "<p><strong>Eliminando clave API actual...</strong></p>";
$deleted = delete_option('chatbot_ia_api_key');
echo "<p>delete_option(): " . ($deleted ? '✅ EXITOSO' : '❌ FALLÓ') . "</p>";

// Probar añadir nueva
$test_api_key = 'sk-bcfd2441b809475ba468d7f10f0124f6';
$encrypted_key = $plugin_instance->encrypt_api_key($test_api_key);

echo "<p><strong>Añadiendo nueva clave API...</strong></p>";
$result_add_api = add_option('chatbot_ia_api_key', $encrypted_key);
echo "<p>add_option() para clave API: " . ($result_add_api ? '✅ EXITOSO' : '❌ FALLÓ') . "</p>";

// Verificar
$saved_api_key = get_option('chatbot_ia_api_key', 'NO_GUARDADO');
echo "<p><strong>Verificación:</strong> " . (strlen($saved_api_key) > 50 ? '✅ ENCRIPTADA (' . strlen($saved_api_key) . ' caracteres)' : '❌ VACÍA') . "</p>";

// Probar desencriptación
if (strlen($saved_api_key) > 50) {
    $decrypted = $plugin_instance->decrypt_api_key($saved_api_key);
    echo "<p><strong>Desencriptación:</strong> " . ($decrypted === $test_api_key ? '✅ CORRECTA' : '❌ INCORRECTA') . "</p>";
}

echo "<h3>5. Información del Sistema:</h3>";

echo "<p><strong>WordPress Version:</strong> " . get_bloginfo('version') . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>MySQL Version:</strong> " . $wpdb->db_version() . "</p>";
echo "<p><strong>Usuario de BD:</strong> " . DB_USER . "</p>";
echo "<p><strong>Host de BD:</strong> " . DB_HOST . "</p>";
echo "<p><strong>Nombre de BD:</strong> " . DB_NAME . "</p>";
echo "<p><strong>Prefijo de tablas:</strong> " . $wpdb->prefix . "</p>";

// Verificar límites de PHP
echo "<p><strong>memory_limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>max_input_vars:</strong> " . ini_get('max_input_vars') . "</p>";
echo "<p><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</p>";
echo "<p><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";

echo "<h3>6. Resumen:</h3>";

if ($result_add && $result_get === $test_value_wp && $result_update_wp && $result_get_updated === $new_value_wp) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✅ Las funciones de WordPress funcionan correctamente</p>";
    echo "<p>El problema puede estar en la lógica del plugin o en conflictos con otros plugins.</p>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>❌ Hay problemas con las funciones de WordPress</p>";
    echo "<p>Revisa los permisos de la base de datos o la configuración del servidor.</p>";
}

echo "<hr>";
echo "<p><a href='" . admin_url() . "'>← Volver al panel de administración</a></p>";
echo "<p><a href='" . admin_url('admin.php?page=chatbot-ia') . "'>→ Ir a configuración del Chatbot IA</a></p>";
?>
