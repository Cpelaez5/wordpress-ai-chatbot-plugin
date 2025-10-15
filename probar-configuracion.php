<?php
/**
 * Script para probar la configuración del plugin
 * Ejecutar este archivo para verificar que todo funciona correctamente
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar que somos administradores
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

echo "<h2>Probando Configuración del Chatbot IA DeepSeek...</h2>";

// Verificar que el plugin esté activo
$active_plugins = get_option('active_plugins', array());
$plugin_file = 'testplugin/testplugin.php';

if (!in_array($plugin_file, $active_plugins)) {
    echo "<p style='color: red;'>❌ Plugin no está activo</p>";
    echo "<p>Activa el plugin desde el panel de administración.</p>";
    exit;
} else {
    echo "<p style='color: green;'>✅ Plugin activo</p>";
}

// Verificar opciones del plugin
$options_to_check = array(
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

echo "<h3>Verificando Opciones del Plugin:</h3>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Opción</th><th>Valor</th><th>Estado</th></tr>";

foreach ($options_to_check as $option => $label) {
    $value = get_option($option, 'NO CONFIGURADO');
    
    if ($option === 'chatbot_ia_api_key' && $value !== 'NO CONFIGURADO') {
        // Verificar si está encriptada
        if (strlen($value) > 50) {
            $display_value = 'ENCRIPTADA (' . strlen($value) . ' caracteres)';
            $status = '✅ Encriptada';
        } else {
            $display_value = $value;
            $status = '⚠️ No encriptada';
        }
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

// Verificar tabla de logs
global $wpdb;
$table_name = $wpdb->prefix . 'chatbot_ia_logs';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;

echo "<h3>Verificando Base de Datos:</h3>";
if ($table_exists) {
    echo "<p style='color: green;'>✅ Tabla de logs existe: $table_name</p>";
    
    $log_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    echo "<p>📊 Número de logs: $log_count</p>";
} else {
    echo "<p style='color: red;'>❌ Tabla de logs no existe: $table_name</p>";
}

// Probar la clase del plugin
echo "<h3>Probando Clase del Plugin:</h3>";
if (class_exists('Chatbot_IA')) {
    echo "<p style='color: green;'>✅ Clase Chatbot_IA existe</p>";
    
    $plugin_instance = Chatbot_IA::get_instance();
    if ($plugin_instance) {
        echo "<p style='color: green;'>✅ Instancia del plugin creada</p>";
        
        // Verificar métodos principales
        $methods_to_check = array(
            'load_options' => 'Cargar Opciones',
            'query_deepseek_api' => 'Consultar API',
            'test_api_connection' => 'Probar Conexión API',
            'save_settings_ajax' => 'Guardar Configuraciones'
        );
        
        echo "<h4>Verificando Métodos:</h4>";
        echo "<ul>";
        foreach ($methods_to_check as $method => $description) {
            if (method_exists($plugin_instance, $method)) {
                echo "<li style='color: green;'>✅ $description ($method)</li>";
            } else {
                echo "<li style='color: red;'>❌ $description ($method) - NO EXISTE</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ No se pudo crear instancia del plugin</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Clase Chatbot_IA no existe</p>";
}

// Verificar archivos del plugin
echo "<h3>Verificando Archivos del Plugin:</h3>";
$plugin_dir = plugin_dir_path(__FILE__);
$required_files = array(
    'testplugin.php' => 'Archivo Principal',
    'admin/admin-page.php' => 'Página de Administración',
    'assets/js/admin.js' => 'JavaScript del Admin',
    'assets/css/admin.css' => 'CSS del Admin',
    'assets/js/frontend.js' => 'JavaScript del Frontend',
    'assets/css/frontend.css' => 'CSS del Frontend',
    'languages/chatbot-ia.pot' => 'Archivo de Traducciones',
    'uninstall.php' => 'Script de Desinstalación'
);

echo "<ul>";
foreach ($required_files as $file => $description) {
    $file_path = $plugin_dir . $file;
    if (file_exists($file_path)) {
        $size = filesize($file_path);
        echo "<li style='color: green;'>✅ $description ($file) - " . number_format($size) . " bytes</li>";
    } else {
        echo "<li style='color: red;'>❌ $description ($file) - NO EXISTE</li>";
    }
}
echo "</ul>";

// Verificar permisos de archivos
echo "<h3>Verificando Permisos:</h3>";
$plugin_dir_perms = substr(sprintf('%o', fileperms($plugin_dir)), -4);
echo "<p>Permisos del directorio del plugin: $plugin_dir_perms</p>";

// Resumen final
echo "<hr>";
echo "<h3>🎯 Resumen de la Verificación:</h3>";

$all_options_configured = true;
foreach ($options_to_check as $option => $label) {
    if (get_option($option, 'NO CONFIGURADO') === 'NO CONFIGURADO') {
        $all_options_configured = false;
        break;
    }
}

if ($all_options_configured && $table_exists && class_exists('Chatbot_IA')) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 ¡Plugin configurado correctamente!</p>";
    echo "<p>El plugin está listo para usar. Puedes:</p>";
    echo "<ul>";
    echo "<li>Ir al panel de administración: <strong>Chatbot IA</strong></li>";
    echo "<li>Probar la conexión API</li>";
    echo "<li>Añadir el shortcode <code>[chatbot-ia]</code> a cualquier página</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>⚠️ Hay problemas con la configuración</p>";
    echo "<p>Revisa los elementos marcados con ❌ y corrígelos.</p>";
}

echo "<hr>";
echo "<p><a href='" . admin_url() . "'>← Volver al panel de administración</a></p>";
echo "<p><a href='" . admin_url('admin.php?page=chatbot-ia') . "'>→ Ir a configuración del Chatbot IA</a></p>";
?>
