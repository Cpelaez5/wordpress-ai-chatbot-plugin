<?php
/**
 * Script para probar la validación de la clave API
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar que somos administradores
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

echo "<h2>Probar Validación de Clave API</h2>";

// Claves API de prueba
$test_keys = [
    'sk-bcfd2441b809475ba468d7f10f0124f6', // Tu clave real
    'sk-12345678901234567890123456789012', // Sin guiones
    'sk-test-key-with-dashes-123456789012345678901234567890', // Con guiones
    'sk_test_key_with_underscores_123456789012345678901234567890', // Con guiones bajos
    'sk-invalid', // Muy corta
    'invalid-key', // Sin sk-
    'sk-123456789012345678901234567890', // Solo 30 caracteres después de sk-
];

echo "<h3>Probando Validación:</h3>";

foreach ($test_keys as $key) {
    echo "<p><strong>Clave:</strong> {$key}</p>";
    
    // Patrón antiguo (solo alfanuméricos)
    $old_pattern = '/^sk-[a-zA-Z0-9]{32,}$/';
    $old_valid = preg_match($old_pattern, $key);
    echo "<p>Patrón antiguo: " . ($old_valid ? '✅ VÁLIDA' : '❌ INVÁLIDA') . "</p>";
    
    // Patrón nuevo (permite guiones y guiones bajos)
    $new_pattern = '/^sk-[a-zA-Z0-9\-_]{32,}$/';
    $new_valid = preg_match($new_pattern, $key);
    echo "<p>Patrón nuevo: " . ($new_valid ? '✅ VÁLIDA' : '❌ INVÁLIDA') . "</p>";
    
    echo "<hr>";
}

echo "<h3>Tu Clave API:</h3>";
$your_key = 'sk-bcfd2441b809475ba468d7f10f0124f6';
echo "<p><strong>Clave:</strong> {$your_key}</p>";
echo "<p><strong>Longitud:</strong> " . strlen($your_key) . " caracteres</p>";
echo "<p><strong>Después de sk-:</strong> " . (strlen($your_key) - 3) . " caracteres</p>";

$pattern = '/^sk-[a-zA-Z0-9\-_]{32,}$/';
$valid = preg_match($pattern, $your_key);
echo "<p><strong>Validación:</strong> " . ($valid ? '✅ VÁLIDA' : '❌ INVÁLIDA') . "</p>";

echo "<hr>";
echo "<p><a href='" . admin_url() . "'>← Volver al panel de administración</a></p>";
echo "<p><a href='" . admin_url('admin.php?page=chatbot-ia') . "'>→ Ir a configuración del Chatbot IA</a></p>";
?>
