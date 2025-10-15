<?php
/**
 * Script para verificar los logs de debug
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar que somos administradores
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

echo "<h2>Verificar Logs de Debug</h2>";

// Verificar si WP_DEBUG_LOG está habilitado
if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
    echo "<p style='color: green;'>✅ WP_DEBUG_LOG está habilitado</p>";
} else {
    echo "<p style='color: red;'>❌ WP_DEBUG_LOG no está habilitado</p>";
    echo "<p>Para habilitar los logs, añade esto a wp-config.php:</p>";
    echo "<pre>define('WP_DEBUG', true);\ndefine('WP_DEBUG_LOG', true);\ndefine('WP_DEBUG_DISPLAY', false);</pre>";
}

// Verificar archivo de log
$log_file = WP_CONTENT_DIR . '/debug.log';
if (file_exists($log_file)) {
    echo "<p style='color: green;'>✅ Archivo de log existe: {$log_file}</p>";
    
    // Leer las últimas 50 líneas del log
    $lines = file($log_file);
    $recent_lines = array_slice($lines, -50);
    
    echo "<h3>Últimas 50 líneas del log:</h3>";
    echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 400px; overflow-y: scroll;'>";
    foreach ($recent_lines as $line) {
        if (strpos($line, 'Chatbot IA:') !== false) {
            echo "<strong style='color: blue;'>" . htmlspecialchars($line) . "</strong>";
        } else {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ Archivo de log no existe: {$log_file}</p>";
}

echo "<hr>";
echo "<p><a href='" . admin_url() . "'>← Volver al panel de administración</a></p>";
echo "<p><a href='" . admin_url('admin.php?page=chatbot-ia') . "'>→ Ir a configuración del Chatbot IA</a></p>";
?>
