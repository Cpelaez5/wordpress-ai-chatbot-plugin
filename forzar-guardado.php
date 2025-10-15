<?php
/**
 * Script para forzar el guardado usando solo add_option
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar que somos administradores
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

echo "<h2>Forzar Guardado de Configuraciones</h2>";

// Verificar que el plugin esté activo
if (!class_exists('Chatbot_IA')) {
    echo "<p style='color: red;'>❌ Plugin no está activo</p>";
    exit;
}

$plugin_instance = Chatbot_IA::get_instance();

echo "<h3>1. Eliminar Todas las Opciones del Plugin:</h3>";

// Lista de opciones a eliminar
$options_to_delete = [
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
    'chatbot_ia_rate_limit_window'
];

foreach ($options_to_delete as $option) {
    $deleted = delete_option($option);
    echo "<p>Eliminar {$option}: " . ($deleted ? '✅ EXITOSO' : '❌ FALLÓ') . "</p>";
}

echo "<h3>2. Añadir Todas las Opciones con add_option:</h3>";

// Configuraciones por defecto
$settings = [
    'chatbot_ia_api_key' => $plugin_instance->encrypt_api_key('sk-bcfd2441b809475ba468d7f10f0124f6'),
    'chatbot_ia_system_instructions' => 'Responde de manera útil y concisa como un agente de soporte al cliente. Siempre responde en español.',
    'chatbot_ia_context' => 'Eres un asistente de IA especializado en ayudar a los usuarios. Responde siempre en español independientemente del idioma de la consulta del usuario.',
    'chatbot_ia_model' => 'deepseek-chat',
    'chatbot_ia_max_tokens' => 1000,
    'chatbot_ia_temperature' => 1.0,
    'chatbot_ia_default_language' => 'es',
    'chatbot_ia_enable_caching' => true,
    'chatbot_ia_cache_duration' => 3600,
    'chatbot_ia_rate_limit' => 10,
    'chatbot_ia_rate_limit_window' => 60
];

foreach ($settings as $option => $value) {
    $result = add_option($option, $value);
    $display_value = (strlen($value) > 50) ? substr($value, 0, 20) . '...' : $value;
    echo "<p>Añadir {$option} = {$display_value}: " . ($result ? '✅ EXITOSO' : '❌ FALLÓ') . "</p>";
}

echo "<h3>3. Verificar Guardado:</h3>";

foreach ($options_to_delete as $option) {
    $value = get_option($option, 'NO_EXISTE');
    if ($value !== 'NO_EXISTE') {
        if ($option === 'chatbot_ia_api_key') {
            echo "<p>✅ {$option}: ENCRIPTADA (" . strlen($value) . " caracteres)</p>";
        } else {
            echo "<p>✅ {$option}: {$value}</p>";
        }
    } else {
        echo "<p>❌ {$option}: NO GUARDADA</p>";
    }
}

echo "<h3>4. Probar Desencriptación de Clave API:</h3>";

$saved_api_key = get_option('chatbot_ia_api_key', 'NO_GUARDADA');
if ($saved_api_key !== 'NO_GUARDADA') {
    $decrypted = $plugin_instance->decrypt_api_key($saved_api_key);
    echo "<p><strong>Clave desencriptada:</strong> " . substr($decrypted, 0, 10) . "...</p>";
    echo "<p><strong>Coincide con original:</strong> " . ($decrypted === 'sk-bcfd2441b809475ba468d7f10f0124f6' ? '✅ SÍ' : '❌ NO') . "</p>";
} else {
    echo "<p style='color: red;'>❌ No hay clave API guardada</p>";
}

echo "<h3>5. Estado Final:</h3>";

$all_saved = true;
foreach ($options_to_delete as $option) {
    $value = get_option($option, 'NO_EXISTE');
    if ($value === 'NO_EXISTE') {
        $all_saved = false;
        break;
    }
}

if ($all_saved) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 ¡Todas las configuraciones guardadas correctamente!</p>";
    echo "<p>Ahora puedes probar en el panel de administración.</p>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>⚠️ Algunas configuraciones no se guardaron</p>";
    echo "<p>Hay un problema con el guardado en la base de datos.</p>";
}

echo "<hr>";
echo "<p><a href='" . admin_url() . "'>← Volver al panel de administración</a></p>";
echo "<p><a href='" . admin_url('admin.php?page=chatbot-ia') . "'>→ Ir a configuración del Chatbot IA</a></p>";
?>
