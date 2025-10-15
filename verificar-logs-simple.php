<?php
/**
 * Verificar logs de WordPress de forma simple
 */

// Incluir WordPress
require_once('../../../wp-config.php');

echo "<h2>Verificar Logs de WordPress</h2>";

// Verificar si WP_DEBUG_LOG está habilitado
if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
    echo "<p>✅ <strong>WP_DEBUG_LOG está habilitado</strong></p>";
} else {
    echo "<p>❌ <strong>WP_DEBUG_LOG no está habilitado</strong></p>";
}

// Verificar archivo de log
$log_file = WP_CONTENT_DIR . '/debug.log';
if (file_exists($log_file)) {
    echo "<p>✅ <strong>Archivo de log existe:</strong> " . $log_file . "</p>";
    
    // Leer las últimas líneas del log
    $logs = file_get_contents($log_file);
    $log_lines = explode("\n", $logs);
    $recent_logs = array_slice($log_lines, -50); // Últimas 50 líneas
    
    echo "<h3>Últimas 50 líneas del log:</h3>";
    echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 400px; overflow-y: auto; font-size: 12px;'>";
    foreach ($recent_logs as $log) {
        if (!empty(trim($log))) {
            echo htmlspecialchars($log) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p>❌ <strong>Archivo de log no existe:</strong> " . $log_file . "</p>";
}

echo "<hr>";
echo "<p><strong>Instrucciones:</strong></p>";
echo "<ol>";
echo "<li>Ve al panel de administración de WordPress</li>";
echo "<li>Intenta guardar la clave API</li>";
echo "<li>Vuelve a ejecutar este script para ver los nuevos logs</li>";
echo "</ol>";
?>
