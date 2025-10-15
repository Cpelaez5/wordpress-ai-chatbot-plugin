<?php
/**
 * Debug en Tiempo Real - Verificar Estado Actual
 */

// Cargar WordPress
require_once('../../../wp-load.php');

echo "<h2>Debug en Tiempo Real - Estado Actual</h2>";

// 1. Verificar estado actual de la base de datos
echo "<h3>1. Estado Actual de la Base de Datos:</h3>";

$api_key_option = get_option('chatbot_ia_api_key', 'NO_EXISTE');
$encryption_key = get_option('chatbot_ia_encryption_key', 'NO_EXISTE');

echo "<p><strong>Clave API en BD:</strong> ";
if ($api_key_option === 'NO_EXISTE') {
    echo "<span style='color: red;'>❌ NO EXISTE</span>";
} else {
    echo "<span style='color: green;'>✅ EXISTE (" . strlen($api_key_option) . " caracteres)</span>";
    echo "<br><strong>Valor encriptado:</strong> " . substr($api_key_option, 0, 20) . "...";
}

echo "</p>";

echo "<p><strong>Clave de Encriptación:</strong> ";
if ($encryption_key === 'NO_EXISTE') {
    echo "<span style='color: red;'>❌ NO EXISTE</span>";
} else {
    echo "<span style='color: green;'>✅ EXISTE (" . strlen($encryption_key) . " caracteres)</span>";
}
echo "</p>";

// 2. Probar desencriptación si existe
if ($api_key_option !== 'NO_EXISTE' && $encryption_key !== 'NO_EXISTE') {
    echo "<h3>2. Probar Desencriptación:</h3>";
    
    // Cargar la clase del plugin
    if (class_exists('Chatbot_IA')) {
        $plugin_instance = Chatbot_IA::get_instance();
        $decrypted_key = $plugin_instance->decrypt_api_key($api_key_option);
        
        if ($decrypted_key) {
            echo "<p><strong>Clave desencriptada:</strong> " . substr($decrypted_key, 0, 20) . "...</p>";
            echo "<p><strong>Longitud:</strong> " . strlen($decrypted_key) . " caracteres</p>";
        } else {
            echo "<p style='color: red;'>❌ Error al desencriptar</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Clase Chatbot_IA no encontrada</p>";
    }
}

// 3. Verificar todas las opciones del plugin
echo "<h3>3. Todas las Opciones del Plugin:</h3>";

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

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Opción</th><th>Estado</th><th>Valor</th></tr>";

foreach ($plugin_options as $option) {
    $value = get_option($option, 'NO_EXISTE');
    $status = ($value === 'NO_EXISTE') ? '❌ NO EXISTE' : '✅ EXISTE';
    $display_value = ($value === 'NO_EXISTE') ? 'N/A' : (strlen($value) > 50 ? substr($value, 0, 30) . '...' : $value);
    
    echo "<tr>";
    echo "<td>{$option}</td>";
    echo "<td>{$status}</td>";
    echo "<td>{$display_value}</td>";
    echo "</tr>";
}

echo "</table>";

// 4. Probar guardado directo
echo "<h3>4. Probar Guardado Directo:</h3>";

$test_key = 'sk-test-debug-' . time();
echo "<p><strong>Probando con clave:</strong> {$test_key}</p>";

// Eliminar si existe
delete_option('chatbot_ia_api_key');

// Intentar guardar
$result = add_option('chatbot_ia_api_key', $test_key);
echo "<p><strong>Resultado add_option:</strong> " . ($result ? '✅ EXITOSO' : '❌ FALLÓ') . "</p>";

// Verificar
$saved_value = get_option('chatbot_ia_api_key', 'NO_EXISTE');
echo "<p><strong>Valor guardado:</strong> " . ($saved_value === $test_key ? '✅ CORRECTO' : '❌ INCORRECTO') . "</p>";
echo "<p><strong>Valor en BD:</strong> {$saved_value}</p>";

// 5. Verificar logs recientes
echo "<h3>5. Logs Recientes:</h3>";

$log_file = ABSPATH . 'wp-content/debug.log';
if (file_exists($log_file)) {
    $logs = file_get_contents($log_file);
    $lines = explode("\n", $logs);
    $recent_lines = array_slice($lines, -20); // Últimas 20 líneas
    
    echo "<div style='background: #f0f0f0; padding: 10px; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto;'>";
    foreach ($recent_lines as $line) {
        if (strpos($line, 'Chatbot IA') !== false) {
            echo htmlspecialchars($line) . "<br>";
        }
    }
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ Archivo de log no encontrado</p>";
}

echo "<hr>";
echo "<p><a href='../admin/admin-page.php'>← Volver al panel de administración</a></p>";
?>
