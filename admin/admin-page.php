<?php
/**
 * Página de administración del plugin Chatbot IA
 * Interfaz completa en español para configurar el chatbot
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Obtener instancia del plugin
$chatbot = Chatbot_IA::get_instance();
?>

<div class="wrap">
    <h1><?php _e('Configuración del Chatbot IA', 'chatbot-ia'); ?></h1>
    
    <?php
    // Mostrar mensajes de éxito/error
    if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Configuración guardada exitosamente.', 'chatbot-ia') . '</p></div>';
    }
    ?>
    
    <div class="chatbot-ia-admin-container">
        <!-- Pestañas de navegación -->
        <nav class="nav-tab-wrapper">
            <a href="#configuracion-general" class="nav-tab nav-tab-active" data-tab="configuracion-general">
                <?php _e('Configuración General', 'chatbot-ia'); ?>
            </a>
            <a href="#configuracion-avanzada" class="nav-tab" data-tab="configuracion-avanzada">
                <?php _e('Configuración Avanzada', 'chatbot-ia'); ?>
            </a>
            <a href="#informacion-api" class="nav-tab" data-tab="informacion-api">
                <?php _e('Información de la API', 'chatbot-ia'); ?>
            </a>
            <a href="#pruebas" class="nav-tab" data-tab="pruebas">
                <?php _e('Pruebas y Logs', 'chatbot-ia'); ?>
            </a>
        </nav>
        
        <form method="post" id="chatbot-ia-form">
            <?php
            settings_fields('chatbot_ia_options');
            do_settings_sections('chatbot-ia');
            ?>
            
            <!-- Pestaña: Configuración General -->
            <div id="configuracion-general" class="tab-content active">
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php _e('Configuración de la API', 'chatbot-ia'); ?></h2>
                    </div>
                    <div class="inside">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="chatbot_ia_api_key"><?php _e('Clave API de DeepSeek', 'chatbot-ia'); ?></label>
                                </th>
                                <td>
                                    <?php
                                    // Obtener y desencriptar la clave API para mostrarla en el formulario
                                    $encrypted_api_key = get_option('chatbot_ia_api_key', '');
                                    $display_api_key = '';
                                    if (!empty($encrypted_api_key)) {
                                        // Intentar desencriptar la clave API
                                        if (class_exists('Chatbot_IA')) {
                                            $plugin_instance = Chatbot_IA::get_instance();
                                            $display_api_key = $plugin_instance->decrypt_api_key($encrypted_api_key);
                                        }
                                    }
                                    ?>
                                    <div class="api-key-container" style="position: relative; display: inline-block; width: 100%;">
                                        <input type="password" 
                                               id="chatbot_ia_api_key" 
                                               name="chatbot_ia_api_key" 
                                               value="<?php echo esc_attr($display_api_key); ?>" 
                                               class="regular-text"
                                               placeholder="sk-..."
                                               style="padding-right: 40px;" />
                                        <button type="button" 
                                                id="toggle-api-key" 
                                                class="button button-secondary" 
                                                style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); padding: 5px 8px; min-height: auto;"
                                                title="<?php _e('Mostrar/Ocultar clave API', 'chatbot-ia'); ?>">
                                            <span class="dashicons dashicons-visibility" id="toggle-icon"></span>
                                        </button>
                                    </div>
                                    <p class="description">
                                        <?php _e('Ingresa tu clave API de DeepSeek. Puedes obtenerla en', 'chatbot-ia'); ?> 
                                        <a href="https://platform.deepseek.com/api_keys" target="_blank"><?php _e('DeepSeek Platform', 'chatbot-ia'); ?></a>
                                        <br>
                                        <small><?php _e('Haz clic en el ojo para mostrar/ocultar la clave API', 'chatbot-ia'); ?></small>
                                    </p>
                                    <button type="button" id="test-api-connection" class="button button-secondary">
                                        <?php _e('Probar Conexión', 'chatbot-ia'); ?>
                                    </button>
                                    <span id="api-test-result"></span>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="chatbot_ia_model"><?php _e('Modelo de IA', 'chatbot-ia'); ?></label>
                                </th>
                                <td>
                                    <select id="chatbot_ia_model" name="chatbot_ia_model">
                                        <option value="deepseek-chat" <?php selected(get_option('chatbot_ia_model'), 'deepseek-chat'); ?>>
                                            <?php _e('DeepSeek Chat (V3.2-Exp - Modo No-Pensamiento)', 'chatbot-ia'); ?>
                                        </option>
                                        <option value="deepseek-reasoner" <?php selected(get_option('chatbot_ia_model'), 'deepseek-reasoner'); ?>>
                                            <?php _e('DeepSeek Reasoner (V3.2-Exp - Modo Pensamiento)', 'chatbot-ia'); ?>
                                        </option>
                                    </select>
                                    <p class="description">
                                        <?php _e('DeepSeek Chat: Más rápido y económico (hasta 8K tokens). DeepSeek Reasoner: Más potente para tareas complejas (hasta 64K tokens).', 'chatbot-ia'); ?>
                                    </p>
                                    <div class="model-info">
                                        <h4><?php _e('Información de Modelos:', 'chatbot-ia'); ?></h4>
                                        <ul>
                                            <li><strong>DeepSeek Chat:</strong> <?php _e('Contexto: 128K, Salida: 4K-8K, Ideal para conversaciones generales', 'chatbot-ia'); ?></li>
                                            <li><strong>DeepSeek Reasoner:</strong> <?php _e('Contexto: 128K, Salida: 32K-64K, Ideal para análisis complejos', 'chatbot-ia'); ?></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php _e('Configuración del Comportamiento', 'chatbot-ia'); ?></h2>
                    </div>
                    <div class="inside">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="chatbot_ia_system_instructions"><?php _e('Instrucciones del Sistema', 'chatbot-ia'); ?></label>
                                </th>
                                <td>
                                    <textarea id="chatbot_ia_system_instructions" 
                                              name="chatbot_ia_system_instructions" 
                                              rows="4" 
                                              class="large-text"
                                              placeholder="<?php _e('Define cómo debe comportarse la IA...', 'chatbot-ia'); ?>"><?php echo esc_textarea(get_option('chatbot_ia_system_instructions')); ?></textarea>
                                    <p class="description">
                                        <?php _e('Instrucciones que definen el comportamiento general de la IA. Ejemplo: "Responde de manera útil y concisa como un agente de soporte al cliente. Siempre responde en español."', 'chatbot-ia'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="chatbot_ia_context"><?php _e('Contexto/Base de Conocimiento', 'chatbot-ia'); ?></label>
                                </th>
                                <td>
                                    <textarea id="chatbot_ia_context" 
                                              name="chatbot_ia_context" 
                                              rows="6" 
                                              class="large-text"
                                              placeholder="<?php _e('Información específica sobre tu sitio web, productos, servicios...', 'chatbot-ia'); ?>"><?php echo esc_textarea(get_option('chatbot_ia_context')); ?></textarea>
                                    <p class="description">
                                        <?php _e('Información específica sobre tu sitio web, productos, servicios o FAQs. La IA utilizará esta información para responder de manera más precisa. Puedes incluir instrucciones de idioma aquí.', 'chatbot-ia'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="chatbot_ia_default_language"><?php _e('Idioma Predeterminado', 'chatbot-ia'); ?></label>
                                </th>
                                <td>
                                    <select id="chatbot_ia_default_language" name="chatbot_ia_default_language">
                                        <option value="es" <?php selected(get_option('chatbot_ia_default_language'), 'es'); ?>>
                                            <?php _e('Español', 'chatbot-ia'); ?>
                                        </option>
                                        <option value="auto" <?php selected(get_option('chatbot_ia_default_language'), 'auto'); ?>>
                                            <?php _e('Auto-detectar basado en consulta', 'chatbot-ia'); ?>
                                        </option>
                                    </select>
                                    <p class="description">
                                        <?php _e('Idioma por defecto para las respuestas de la IA. Si seleccionas "Auto-detectar", la IA intentará responder en el mismo idioma de la consulta.', 'chatbot-ia'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Pestaña: Configuración Avanzada -->
            <div id="configuracion-avanzada" class="tab-content">
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php _e('Parámetros de la IA', 'chatbot-ia'); ?></h2>
                    </div>
                    <div class="inside">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="chatbot_ia_max_tokens"><?php _e('Máximo de Tokens', 'chatbot-ia'); ?></label>
                                </th>
                                <td>
                                    <input type="number" 
                                           id="chatbot_ia_max_tokens" 
                                           name="chatbot_ia_max_tokens" 
                                           value="<?php echo esc_attr(get_option('chatbot_ia_max_tokens', 1000)); ?>" 
                                           min="100" 
                                           max="8000" 
                                           class="small-text" />
                                    <p class="description">
                                        <?php _e('Número máximo de tokens en la respuesta. Límites: DeepSeek Chat (100-8K), DeepSeek Reasoner (100-64K).', 'chatbot-ia'); ?>
                                    </p>
                                    <div class="token-info">
                                        <p><strong><?php _e('Precios por 1M tokens:', 'chatbot-ia'); ?></strong></p>
                                        <ul>
                                            <li><?php _e('Entrada (Cache Hit): $0.028', 'chatbot-ia'); ?></li>
                                            <li><?php _e('Entrada (Cache Miss): $0.28', 'chatbot-ia'); ?></li>
                                            <li><?php _e('Salida: $0.42', 'chatbot-ia'); ?></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="chatbot_ia_temperature"><?php _e('Temperatura (Creatividad)', 'chatbot-ia'); ?></label>
                                </th>
                                <td>
                                    <input type="range" 
                                           id="chatbot_ia_temperature" 
                                           name="chatbot_ia_temperature" 
                                           value="<?php echo esc_attr(get_option('chatbot_ia_temperature', 1.0)); ?>" 
                                           min="0" 
                                           max="2" 
                                           step="0.1" 
                                           oninput="document.getElementById('temp-value').textContent = this.value; updateTempPresets(this.value);" />
                                    <span id="temp-value"><?php echo esc_attr(get_option('chatbot_ia_temperature', 1.0)); ?></span>
                                    <p class="description">
                                        <?php _e('Controla la creatividad de las respuestas (0 = más determinista, 2 = más creativo). Valor por defecto: 1.0', 'chatbot-ia'); ?>
                                    </p>
                                    
                                    <div class="temperature-presets">
                                        <h4><?php _e('Presets Recomendados:', 'chatbot-ia'); ?></h4>
                                        <div class="preset-buttons">
                                            <button type="button" class="preset-btn" data-temp="0.0" onclick="setTemperature(0.0)">
                                                <?php _e('Código/Math (0.0)', 'chatbot-ia'); ?>
                                            </button>
                                            <button type="button" class="preset-btn" data-temp="1.0" onclick="setTemperature(1.0)">
                                                <?php _e('Análisis de Datos (1.0)', 'chatbot-ia'); ?>
                                            </button>
                                            <button type="button" class="preset-btn" data-temp="1.3" onclick="setTemperature(1.3)">
                                                <?php _e('Conversación General (1.3)', 'chatbot-ia'); ?>
                                            </button>
                                            <button type="button" class="preset-btn" data-temp="1.5" onclick="setTemperature(1.5)">
                                                <?php _e('Escritura Creativa (1.5)', 'chatbot-ia'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php _e('Optimización y Rendimiento', 'chatbot-ia'); ?></h2>
                    </div>
                    <div class="inside">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="chatbot_ia_enable_caching"><?php _e('Habilitar Cache', 'chatbot-ia'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" 
                                           id="chatbot_ia_enable_caching" 
                                           name="chatbot_ia_enable_caching" 
                                           value="1" 
                                           <?php checked(get_option('chatbot_ia_enable_caching', true)); ?> />
                                    <label for="chatbot_ia_enable_caching"><?php _e('Activar cache para consultas repetidas', 'chatbot-ia'); ?></label>
                                    <p class="description">
                                        <?php _e('Mejora el rendimiento guardando respuestas de consultas similares.', 'chatbot-ia'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="chatbot_ia_cache_duration"><?php _e('Duración del Cache (segundos)', 'chatbot-ia'); ?></label>
                                </th>
                                <td>
                                    <input type="number" 
                                           id="chatbot_ia_cache_duration" 
                                           name="chatbot_ia_cache_duration" 
                                           value="<?php echo esc_attr(get_option('chatbot_ia_cache_duration', 3600)); ?>" 
                                           min="300" 
                                           max="86400" 
                                           class="small-text" />
                                    <p class="description">
                                        <?php _e('Tiempo que se mantienen las respuestas en cache (300-86400 segundos).', 'chatbot-ia'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="chatbot_ia_rate_limit"><?php _e('Límite de Mensajes por Usuario', 'chatbot-ia'); ?></label>
                                </th>
                                <td>
                                    <input type="number" 
                                           id="chatbot_ia_rate_limit" 
                                           name="chatbot_ia_rate_limit" 
                                           value="<?php echo esc_attr(get_option('chatbot_ia_rate_limit', 10)); ?>" 
                                           min="1" 
                                           max="100" 
                                           class="small-text" />
                                    <p class="description">
                                        <?php _e('Número máximo de mensajes que un usuario puede enviar en el período especificado.', 'chatbot-ia'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="chatbot_ia_rate_limit_window"><?php _e('Ventana de Límite (segundos)', 'chatbot-ia'); ?></label>
                                </th>
                                <td>
                                    <input type="number" 
                                           id="chatbot_ia_rate_limit_window" 
                                           name="chatbot_ia_rate_limit_window" 
                                           value="<?php echo esc_attr(get_option('chatbot_ia_rate_limit_window', 60)); ?>" 
                                           min="60" 
                                           max="3600" 
                                           class="small-text" />
                                    <p class="description">
                                        <?php _e('Período de tiempo para aplicar el límite de mensajes (60-3600 segundos).', 'chatbot-ia'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Pestaña: Información de la API -->
            <div id="informacion-api" class="tab-content">
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php _e('Modelos Disponibles', 'chatbot-ia'); ?></h2>
                    </div>
                    <div class="inside">
                        <div class="model-comparison">
                            <table class="widefat">
                                <thead>
                                    <tr>
                                        <th><?php _e('Modelo', 'chatbot-ia'); ?></th>
                                        <th><?php _e('Versión', 'chatbot-ia'); ?></th>
                                        <th><?php _e('Contexto', 'chatbot-ia'); ?></th>
                                        <th><?php _e('Salida Máxima', 'chatbot-ia'); ?></th>
                                        <th><?php _e('Características', 'chatbot-ia'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>DeepSeek Chat</strong></td>
                                        <td>V3.2-Exp (No-Pensamiento)</td>
                                        <td>128K tokens</td>
                                        <td>4K-8K tokens</td>
                                        <td>JSON Output, Function Calling, Chat Prefix, FIM Completion</td>
                                    </tr>
                                    <tr>
                                        <td><strong>DeepSeek Reasoner</strong></td>
                                        <td>V3.2-Exp (Pensamiento)</td>
                                        <td>128K tokens</td>
                                        <td>32K-64K tokens</td>
                                        <td>JSON Output, Chat Prefix</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php _e('Precios y Facturación', 'chatbot-ia'); ?></h2>
                    </div>
                    <div class="inside">
                        <div class="pricing-info">
                            <h3><?php _e('Precios por 1 Millón de Tokens:', 'chatbot-ia'); ?></h3>
                            <table class="widefat">
                                <thead>
                                    <tr>
                                        <th><?php _e('Tipo de Token', 'chatbot-ia'); ?></th>
                                        <th><?php _e('Precio (USD)', 'chatbot-ia'); ?></th>
                                        <th><?php _e('Descripción', 'chatbot-ia'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?php _e('Entrada (Cache Hit)', 'chatbot-ia'); ?></td>
                                        <td><strong>$0.028</strong></td>
                                        <td><?php _e('Cuando el contenido ya está en cache', 'chatbot-ia'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Entrada (Cache Miss)', 'chatbot-ia'); ?></td>
                                        <td><strong>$0.28</strong></td>
                                        <td><?php _e('Contenido nuevo que no está en cache', 'chatbot-ia'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php _e('Salida', 'chatbot-ia'); ?></td>
                                        <td><strong>$0.42</strong></td>
                                        <td><?php _e('Respuestas generadas por la IA', 'chatbot-ia'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <div class="billing-info">
                                <h4><?php _e('Información de Facturación:', 'chatbot-ia'); ?></h4>
                                <ul>
                                    <li><?php _e('Los precios pueden variar y DeepSeek se reserva el derecho de ajustarlos', 'chatbot-ia'); ?></li>
                                    <li><?php _e('Se factura basado en el número total de tokens de entrada y salida', 'chatbot-ia'); ?></li>
                                    <li><?php _e('Se usa primero el saldo otorgado, luego el saldo recargado', 'chatbot-ia'); ?></li>
                                    <li><?php _e('Recomendamos recargar según el uso real y revisar regularmente los precios', 'chatbot-ia'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php _e('Códigos de Error', 'chatbot-ia'); ?></h2>
                    </div>
                    <div class="inside">
                        <div class="error-codes">
                            <table class="widefat">
                                <thead>
                                    <tr>
                                        <th><?php _e('Código', 'chatbot-ia'); ?></th>
                                        <th><?php _e('Descripción', 'chatbot-ia'); ?></th>
                                        <th><?php _e('Solución', 'chatbot-ia'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>400</strong></td>
                                        <td><?php _e('Formato Inválido', 'chatbot-ia'); ?></td>
                                        <td><?php _e('Verificar formato del cuerpo de la solicitud', 'chatbot-ia'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>401</strong></td>
                                        <td><?php _e('Autenticación Fallida', 'chatbot-ia'); ?></td>
                                        <td><?php _e('Verificar clave API', 'chatbot-ia'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>402</strong></td>
                                        <td><?php _e('Saldo Insuficiente', 'chatbot-ia'); ?></td>
                                        <td><?php _e('Recargar cuenta en DeepSeek Platform', 'chatbot-ia'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>422</strong></td>
                                        <td><?php _e('Parámetros Inválidos', 'chatbot-ia'); ?></td>
                                        <td><?php _e('Verificar parámetros de la solicitud', 'chatbot-ia'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>429</strong></td>
                                        <td><?php _e('Límite de Tasa', 'chatbot-ia'); ?></td>
                                        <td><?php _e('Reducir frecuencia de solicitudes', 'chatbot-ia'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>500</strong></td>
                                        <td><?php _e('Error del Servidor', 'chatbot-ia'); ?></td>
                                        <td><?php _e('Reintentar después de un momento', 'chatbot-ia'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>503</strong></td>
                                        <td><?php _e('Servidor Sobrecargado', 'chatbot-ia'); ?></td>
                                        <td><?php _e('Reintentar más tarde', 'chatbot-ia'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php _e('Enlaces Útiles', 'chatbot-ia'); ?></h2>
                    </div>
                    <div class="inside">
                        <div class="useful-links">
                            <ul>
                                <li><a href="https://platform.deepseek.com/api_keys" target="_blank"><?php _e('Obtener Clave API', 'chatbot-ia'); ?></a></li>
                                <li><a href="https://platform.deepseek.com/balance" target="_blank"><?php _e('Verificar Saldo', 'chatbot-ia'); ?></a></li>
                                <li><a href="https://platform.deepseek.com/topup" target="_blank"><?php _e('Recargar Cuenta', 'chatbot-ia'); ?></a></li>
                                <li><a href="https://api-docs.deepseek.com/" target="_blank"><?php _e('Documentación de la API', 'chatbot-ia'); ?></a></li>
                                <li><a href="https://platform.deepseek.com/" target="_blank"><?php _e('DeepSeek Platform', 'chatbot-ia'); ?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pestaña: Pruebas y Logs -->
            <div id="pruebas" class="tab-content">
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php _e('Prueba del Chatbot', 'chatbot-ia'); ?></h2>
                    </div>
                    <div class="inside">
                        <p><?php _e('Prueba tu configuración del chatbot enviando un mensaje de prueba:', 'chatbot-ia'); ?></p>
                        
                        <div id="chat-test-container">
                            <div id="chat-test-messages"></div>
                            <div class="chat-test-input">
                                <input type="text" 
                                       id="test-message" 
                                       placeholder="<?php _e('Escribe tu mensaje de prueba aquí...', 'chatbot-ia'); ?>" 
                                       class="regular-text" />
                                <button type="button" id="send-test-message" class="button button-primary">
                                    <?php _e('Enviar Prueba', 'chatbot-ia'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="postbox">
                    <div class="postbox-header">
                        <h2 class="hndle"><?php _e('Información del Sistema', 'chatbot-ia'); ?></h2>
                    </div>
                    <div class="inside">
                        <table class="widefat">
                            <tr>
                                <td><strong><?php _e('Versión del Plugin:', 'chatbot-ia'); ?></strong></td>
                                <td><?php echo CHATBOT_IA_VERSION; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Versión de WordPress:', 'chatbot-ia'); ?></strong></td>
                                <td><?php echo get_bloginfo('version'); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Versión de PHP:', 'chatbot-ia'); ?></strong></td>
                                <td><?php echo PHP_VERSION; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Estado de la API:', 'chatbot-ia'); ?></strong></td>
                                <td id="api-status">
                                    <?php 
                                    if (get_option('chatbot_ia_api_key')) {
                                        echo '<span class="dashicons dashicons-yes-alt" style="color: green;"></span> ' . __('Clave API configurada', 'chatbot-ia');
                                    } else {
                                        echo '<span class="dashicons dashicons-warning" style="color: orange;"></span> ' . __('Clave API no configurada', 'chatbot-ia');
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <?php submit_button(__('Guardar Configuración', 'chatbot-ia')); ?>
        </form>
    </div>
</div>

<style>
.chatbot-ia-admin-container {
    max-width: 1200px;
}

.tab-content {
    display: none;
    margin-top: 20px;
}

.tab-content.active {
    display: block;
}

.nav-tab-wrapper {
    margin-bottom: 0;
}

#chat-test-container {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 15px;
    background: #f9f9f9;
}

#chat-test-messages {
    min-height: 200px;
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 10px;
    background: white;
    margin-bottom: 10px;
    border-radius: 3px;
}

.chat-test-input {
    display: flex;
    gap: 10px;
}

.chat-test-input input {
    flex: 1;
}

.message {
    margin-bottom: 10px;
    padding: 8px 12px;
    border-radius: 15px;
    max-width: 80%;
}

.message.user {
    background: #0073aa;
    color: white;
    margin-left: auto;
    text-align: right;
}

.message.bot {
    background: #f1f1f1;
    color: #333;
}

.message.error {
    background: #dc3232;
    color: white;
}

#api-test-result {
    margin-left: 10px;
    font-weight: bold;
}

#api-test-result.success {
    color: green;
}

#api-test-result.error {
    color: red;
}

input[type="range"] {
    width: 200px;
    margin-right: 10px;
}

#temp-value {
    font-weight: bold;
    color: #0073aa;
        }
        
        /* Estilos para información de modelos */
        .model-info {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-top: 10px;
        }
        
        .model-info h4 {
            margin-top: 0;
            color: #0073aa;
        }
        
        .model-info ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .model-info li {
            margin-bottom: 5px;
        }
        
        /* Estilos para información de tokens */
        .token-info {
            background: #f0f8ff;
            border: 1px solid #b3d9ff;
            border-radius: 4px;
            padding: 15px;
            margin-top: 10px;
        }
        
        .token-info p {
            margin-top: 0;
            color: #0073aa;
            font-weight: bold;
        }
        
        .token-info ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        /* Estilos para presets de temperatura */
        .temperature-presets {
            background: #fff8e1;
            border: 1px solid #ffcc02;
            border-radius: 4px;
            padding: 15px;
            margin-top: 10px;
        }
        
        .temperature-presets h4 {
            margin-top: 0;
            color: #f57c00;
        }
        
        .preset-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .preset-btn {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px 12px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s ease;
        }
        
        .preset-btn:hover {
            background: #f0f0f0;
            border-color: #0073aa;
        }
        
        .preset-btn.active {
            background: #0073aa;
            color: white;
            border-color: #0073aa;
        }
        
        /* Estilos para comparación de modelos */
        .model-comparison table {
            margin-top: 10px;
        }
        
        .model-comparison th {
            background: #f1f1f1;
            font-weight: bold;
        }
        
        /* Estilos para información de precios */
        .pricing-info h3 {
            color: #0073aa;
            margin-top: 0;
        }
        
        .billing-info {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .billing-info h4 {
            margin-top: 0;
            color: #0073aa;
        }
        
        .billing-info ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        /* Estilos para códigos de error */
        .error-codes table {
            margin-top: 10px;
        }
        
        .error-codes th {
            background: #f1f1f1;
            font-weight: bold;
        }
        
        .error-codes td:first-child {
            font-weight: bold;
            color: #d63638;
        }
        
        /* Estilos para enlaces útiles */
        .useful-links ul {
            list-style: none;
            padding: 0;
        }
        
        .useful-links li {
            margin-bottom: 10px;
        }
        
        .useful-links a {
            display: inline-block;
            padding: 8px 12px;
            background: #0073aa;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.2s ease;
        }
        
        .useful-links a:hover {
            background: #005a87;
        }
        
        /* Estilos para el botón del ojo */
        .api-key-container {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .api-key-container input {
            padding-right: 40px !important;
        }
        
        #toggle-api-key {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            padding: 5px 8px;
            min-height: auto;
            border: none;
            background: transparent;
            cursor: pointer;
            z-index: 10;
        }
        
        #toggle-api-key:hover {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 3px;
        }
        
        #toggle-api-key .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
            line-height: 16px;
        }
        
        /* Responsive para presets */
        @media (max-width: 768px) {
            .preset-buttons {
                flex-direction: column;
            }
            
            .preset-btn {
                width: 100%;
                text-align: center;
            }
        }
        </style>

<script>
jQuery(document).ready(function($) {
    // Manejo de pestañas
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        // Remover clase activa de todas las pestañas y contenidos
        $('.nav-tab').removeClass('nav-tab-active');
        $('.tab-content').removeClass('active');
        
        // Añadir clase activa a la pestaña clickeada
        $(this).addClass('nav-tab-active');
        
        // Mostrar contenido correspondiente
        var tab = $(this).data('tab');
        $('#' + tab).addClass('active');
    });
    
    // Probar conexión API
    $('#test-api-connection').on('click', function() {
        var button = $(this);
        var result = $('#api-test-result');
        
        button.prop('disabled', true).text(chatbotIaAdmin.strings.testing_connection);
        result.removeClass('success error').text('');
        
        $.ajax({
            url: chatbotIaAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'chatbot_ia_test_api',
                nonce: chatbotIaAdmin.nonce,
                api_key: $('#chatbot_ia_api_key').val()
            },
            success: function(response) {
                if (response && response.success) {
                    result.addClass('success').text(chatbotIaAdmin.strings.connection_success);
                } else {
                    var errorMessage = 'Error desconocido';
                    if (response && response.data && response.data.message) {
                        errorMessage = response.data.message;
                    } else if (response && response.message) {
                        errorMessage = response.message;
                    }
                    result.addClass('error').text(errorMessage);
                }
            },
            error: function() {
                result.addClass('error').text(chatbotIaAdmin.strings.connection_error);
            },
            complete: function() {
                button.prop('disabled', false).text('Probar Conexión');
            }
        });
    });
    
    // Enviar mensaje de prueba
    $('#send-test-message').on('click', function() {
        var message = $('#test-message').val().trim();
        if (!message) return;
        
        var button = $(this);
        var messagesContainer = $('#chat-test-messages');
        
        // Añadir mensaje del usuario
        messagesContainer.append('<div class="message user">' + message + '</div>');
        
        // Añadir indicador de carga
        messagesContainer.append('<div class="message bot">' + chatbotIaAdmin.strings.testing_connection + '</div>');
        
        button.prop('disabled', true);
        $('#test-message').val('');
        
        // Scroll al final
        messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
        
        $.ajax({
            url: chatbotIaAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'chatbot_ia_query',
                nonce: chatbotIaAdmin.nonce,
                message: message
            },
            success: function(response) {
                // Remover indicador de carga
                messagesContainer.find('.message.bot').last().remove();
                
                console.log('Respuesta del chat:', response); // Debug
                console.log('Tipo de respuesta:', typeof response); // Debug
                console.log('response.success:', response ? response.success : 'undefined'); // Debug
                console.log('response.data:', response ? response.data : 'undefined'); // Debug
                
                // Verificar si la respuesta es válida
                if (response && typeof response === 'object') {
                    if (response.success === true) {
                        var responseText = 'Respuesta recibida';
                        if (response.data && response.data.response) {
                            responseText = response.data.response;
                        } else if (response.data && typeof response.data === 'string') {
                            responseText = response.data;
                        }
                        messagesContainer.append('<div class="message bot">' + responseText + '</div>');
                    } else {
                        var errorMessage = 'Error desconocido';
                        if (response.data && response.data.message) {
                            errorMessage = response.data.message;
                        } else if (response.message) {
                            errorMessage = response.message;
                        } else if (response.data) {
                            errorMessage = 'Error: ' + JSON.stringify(response.data);
                        }
                        messagesContainer.append('<div class="message error">' + errorMessage + '</div>');
                    }
                } else {
                    console.error('Respuesta inválida:', response);
                    messagesContainer.append('<div class="message error">Error: Respuesta inválida del servidor</div>');
                }
            },
            error: function() {
                messagesContainer.find('.message.bot').last().remove();
                messagesContainer.append('<div class="message error">Error de conexión</div>');
            },
            complete: function() {
                button.prop('disabled', false);
                messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
            }
        });
    });
    
    // Enviar mensaje con Enter
    $('#test-message').on('keypress', function(e) {
        if (e.which === 13) {
            $('#send-test-message').click();
        }
    });

    // Funciones para presets de temperatura
    window.setTemperature = function(value) {
        const slider = document.getElementById('chatbot_ia_temperature');
        const display = document.getElementById('temp-value');
        
        slider.value = value;
        display.textContent = value;
        updateTempPresets(value);
    };

    function updateTempPresets(value) {
        const buttons = document.querySelectorAll('.preset-btn');
        buttons.forEach(btn => {
            btn.classList.remove('active');
            if (Math.abs(parseFloat(btn.dataset.temp) - parseFloat(value)) < 0.1) {
                btn.classList.add('active');
            }
        });
    }

    // Actualizar límites de tokens según el modelo
    $('#chatbot_ia_model').on('change', function() {
        const model = $(this).val();
        const tokenInput = $('#chatbot_ia_max_tokens');
        
        if (model === 'deepseek-reasoner') {
            tokenInput.attr('max', '64000');
            tokenInput.attr('placeholder', '100-64000 tokens');
        } else {
            tokenInput.attr('max', '8000');
            tokenInput.attr('placeholder', '100-8000 tokens');
        }
    });

    // Inicializar presets al cargar
    const currentTemp = document.getElementById('chatbot_ia_temperature').value;
    updateTempPresets(currentTemp);
    
    // Manejo del envío del formulario
    $('#chatbot-ia-form').on('submit', function(e) {
        e.preventDefault();
        console.log('Formulario enviado'); // Debug
        
        // Verificar que chatbotIaAdmin esté definido
        if (typeof chatbotIaAdmin === 'undefined') {
            console.error('chatbotIaAdmin no está definido');
            alert('Error: chatbotIaAdmin no está definido. Verifica que el script se esté cargando correctamente.');
            return;
        }
        
        var $form = $(this);
        var $submitBtn = $form.find('input[type="submit"]');
        var originalText = $submitBtn.val();
        
        // Mostrar estado de guardado
        $submitBtn.val('Guardando...').prop('disabled', true);
        
        // Recopilar datos del formulario
        var formData = {
            action: 'chatbot_ia_save_settings',
            nonce: chatbotIaAdmin.nonce,
            api_key: $('#chatbot_ia_api_key').val(),
            system_instructions: $('#chatbot_ia_system_instructions').val(),
            context: $('#chatbot_ia_context').val(),
            model: $('#chatbot_ia_model').val(),
            max_tokens: $('#chatbot_ia_max_tokens').val(),
            temperature: $('#chatbot_ia_temperature').val(),
            default_language: $('#chatbot_ia_default_language').val(),
            enable_caching: $('#chatbot_ia_enable_caching').is(':checked'),
            cache_duration: $('#chatbot_ia_cache_duration').val(),
            rate_limit: $('#chatbot_ia_rate_limit').val(),
            rate_limit_window: $('#chatbot_ia_rate_limit_window').val()
        };
        
        console.log('Enviando datos:', formData); // Debug
        console.log('URL AJAX:', chatbotIaAdmin.ajax_url); // Debug
        
        $.ajax({
            url: chatbotIaAdmin.ajax_url,
            type: 'POST',
            data: formData,
            timeout: 30000,
            success: function(response) {
                console.log('Respuesta del servidor:', response); // Debug
                if (response.success) {
                    alert('Configuraciones guardadas correctamente');
                } else {
                    alert('Error al guardar: ' + (response.data.message || 'Error desconocido'));
                }
            },
            error: function(xhr, status, error) {
                console.log('Error AJAX:', xhr, status, error); // Debug
                console.log('Status:', xhr.status); // Debug
                console.log('Response Text:', xhr.responseText); // Debug
                var errorMessage = 'Error al guardar configuraciones';
                if (status === 'timeout') {
                    errorMessage = 'Tiempo de espera agotado. Intenta de nuevo.';
                } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMessage = xhr.responseJSON.data.message;
                }
                alert('Error: ' + errorMessage);
            },
            complete: function() {
                $submitBtn.val(originalText).prop('disabled', false);
            }
        });
    });
    
    // Toggle para mostrar/ocultar clave API (manejado por admin.js)
    
}); // Cierre del jQuery(document).ready()
</script>
