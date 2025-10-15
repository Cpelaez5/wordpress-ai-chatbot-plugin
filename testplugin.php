<?php
/*
Plugin Name: Chatbot IA DeepSeek
Plugin URI: https://ejemplo.com/chatbot-ia
Description: Plugin de WordPress para integrar un chatbot de IA utilizando la API de DeepSeek. Interfaz completamente en español con configuración flexible del idioma de respuesta.
Version: 1.0.0
Author: Tu Nombre
Text Domain: chatbot-ia
Domain Path: /languages
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Verificar versión mínima de PHP
if (version_compare(PHP_VERSION, '7.4', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo __('Chatbot IA DeepSeek requiere PHP 7.4 o superior. Tu versión actual es: ', 'chatbot-ia') . PHP_VERSION;
        echo '</p></div>';
    });
    return;
}

// Verificar versión mínima de WordPress
if (version_compare(get_bloginfo('version'), '6.0', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo __('Chatbot IA DeepSeek requiere WordPress 6.0 o superior.', 'chatbot-ia');
        echo '</p></div>';
    });
    return;
}

// Definir constantes del plugin
define('CHATBOT_IA_VERSION', '1.0.0');
define('CHATBOT_IA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CHATBOT_IA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CHATBOT_IA_PLUGIN_FILE', __FILE__);

/**
 * Clase principal del plugin Chatbot IA
 * Maneja la inicialización, configuración y funcionalidad del chatbot
 */
class Chatbot_IA {
    
    /**
     * Instancia única del plugin (Singleton)
     */
    private static $instance = null;
    
    /**
     * Configuración del plugin
     */
    private $options;
    
    /**
     * Constructor privado para implementar patrón Singleton
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Obtener instancia única del plugin
     * @return Chatbot_IA
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Inicializar el plugin
     */
    private function init() {
        // Cargar opciones del plugin
        $this->load_options();
        
        // Cargar traducciones
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Hooks de activación y desactivación
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Hooks de WordPress
        add_action('init', array($this, 'init_hooks'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Agregar headers de seguridad
        add_action('send_headers', array($this, 'add_security_headers'));
        
        // Shortcode para mostrar el chatbot
        add_shortcode('chatbot-ia', array($this, 'render_chatbot_shortcode'));
        
        // AJAX handlers para comunicación con la API
        add_action('wp_ajax_chatbot_ia_query', array($this, 'handle_chat_query'));
        add_action('wp_ajax_nopriv_chatbot_ia_query', array($this, 'handle_chat_query'));
        add_action('wp_ajax_chatbot_ia_test_api', array($this, 'test_api_connection'));
        add_action('wp_ajax_chatbot_ia_save_settings', array($this, 'save_settings_ajax'));
    }
    
    /**
     * Obtener opciones del plugin
     */
    public function get_options() {
        return $this->options;
    }
    
    /**
     * Cargar opciones del plugin desde la base de datos
     */
    public function load_options() {
        // Obtener clave API encriptada y desencriptarla
        $encrypted_api_key = get_option('chatbot_ia_api_key', '');
        $api_key = '';
        if (!empty($encrypted_api_key)) {
            $api_key = $this->decrypt_api_key($encrypted_api_key);
            // Log temporal para debug
            error_log("Chatbot IA Debug: load_options - Clave encriptada: " . strlen($encrypted_api_key) . " chars, Desencriptada: " . (empty($api_key) ? 'VACÍA' : 'OK (' . strlen($api_key) . ' chars)'));
        } else {
            error_log("Chatbot IA Debug: load_options - No hay clave API encriptada en la BD");
        }
        
        // NO usar clave por defecto - solo usar la que esté guardada
        
        $this->options = array(
            'api_key' => $api_key,
            'system_instructions' => get_option('chatbot_ia_system_instructions', 'Responde de manera útil y concisa como un agente de soporte al cliente. Siempre responde en español.'),
            'context' => get_option('chatbot_ia_context', 'Eres un asistente de IA especializado en ayudar a los usuarios. Responde siempre en español independientemente del idioma de la consulta del usuario.'),
            'model' => get_option('chatbot_ia_model', 'deepseek-chat'),
            'max_tokens' => get_option('chatbot_ia_max_tokens', 1000),
            'temperature' => get_option('chatbot_ia_temperature', 1.0),
            'default_language' => get_option('chatbot_ia_default_language', 'es'),
            'enable_caching' => get_option('chatbot_ia_enable_caching', true),
            'cache_duration' => get_option('chatbot_ia_cache_duration', 3600),
            'rate_limit' => get_option('chatbot_ia_rate_limit', 10),
            'rate_limit_window' => get_option('chatbot_ia_rate_limit_window', 60)
        );
    }
    
    /**
     * Cargar archivos de traducción
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'chatbot-ia',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
    /**
     * Inicializar hooks adicionales
     */
    public function init_hooks() {
        // Verificar si el shortcode está presente en la página actual
        add_action('wp_footer', array($this, 'maybe_enqueue_chatbot_assets'));
    }
    
    /**
     * Activar el plugin
     */
    public function activate() {
        // Crear opciones por defecto si no existen
        $default_options = array(
            'chatbot_ia_api_key' => '',
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
        );
        
        foreach ($default_options as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
        
        // Crear tabla para logs si es necesario (opcional)
        $this->create_logs_table();
        
        // Limpiar cache
        $this->clear_cache();
    }
    
    /**
     * Desactivar el plugin
     */
    public function deactivate() {
        // Limpiar cache al desactivar
        $this->clear_cache();
    }
    
    /**
     * Crear tabla para logs (opcional, para debugging)
     */
    private function create_logs_table() {
    global $wpdb;

        $table_name = $wpdb->prefix . 'chatbot_ia_logs';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            user_ip varchar(45),
            query text,
            response text,
            api_response_time int,
            error_message text,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Limpiar cache del plugin
     */
    private function clear_cache() {
        // Limpiar transients relacionados con el chatbot
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_chatbot_ia_%' 
             OR option_name LIKE '_transient_timeout_chatbot_ia_%'"
        );
    }
    
    /**
     * Añadir menú de administración
     */
    public function add_admin_menu() {
        // Añadir menú principal más visible
        add_menu_page(
            __('Chatbot IA DeepSeek', 'chatbot-ia'),
            __('Chatbot IA', 'chatbot-ia'),
            'manage_options',
            'chatbot-ia',
            array($this, 'admin_page'),
            'dashicons-format-chat',
            30
        );
        
        // Añadir también en Ajustes como submenú
        add_options_page(
            __('Configuración del Chatbot IA', 'chatbot-ia'),
            __('Chatbot IA', 'chatbot-ia'),
            'manage_options',
            'chatbot-ia-settings',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Registrar configuraciones del plugin
     */
    public function register_settings() {
        // Registrar grupo de opciones con sanitización segura
        register_setting('chatbot_ia_options', 'chatbot_ia_api_key', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ));
        register_setting('chatbot_ia_options', 'chatbot_ia_system_instructions', array(
            'sanitize_callback' => array($this, 'sanitize_system_instructions'),
            'default' => 'Responde de manera útil y concisa como un agente de soporte al cliente. Siempre responde en español.'
        ));
        register_setting('chatbot_ia_options', 'chatbot_ia_context', array(
            'sanitize_callback' => array($this, 'sanitize_context'),
            'default' => 'Eres un asistente de IA especializado en ayudar a los usuarios. Responde siempre en español independientemente del idioma de la consulta del usuario.'
        ));
        register_setting('chatbot_ia_options', 'chatbot_ia_model', array(
            'sanitize_callback' => array($this, 'sanitize_model'),
            'default' => 'deepseek-chat'
        ));
        register_setting('chatbot_ia_options', 'chatbot_ia_max_tokens', array(
            'sanitize_callback' => array($this, 'sanitize_max_tokens'),
            'default' => 1000
        ));
        register_setting('chatbot_ia_options', 'chatbot_ia_temperature', array(
            'sanitize_callback' => array($this, 'sanitize_temperature'),
            'default' => 1.0
        ));
        register_setting('chatbot_ia_options', 'chatbot_ia_default_language', array(
            'sanitize_callback' => array($this, 'sanitize_default_language'),
            'default' => 'es'
        ));
        register_setting('chatbot_ia_options', 'chatbot_ia_enable_caching', array(
            'sanitize_callback' => array($this, 'sanitize_boolean'),
            'default' => true
        ));
        register_setting('chatbot_ia_options', 'chatbot_ia_cache_duration', array(
            'sanitize_callback' => array($this, 'sanitize_cache_duration'),
            'default' => 3600
        ));
        register_setting('chatbot_ia_options', 'chatbot_ia_rate_limit', array(
            'sanitize_callback' => array($this, 'sanitize_rate_limit'),
            'default' => 10
        ));
        register_setting('chatbot_ia_options', 'chatbot_ia_rate_limit_window', array(
            'sanitize_callback' => array($this, 'sanitize_rate_limit_window'),
            'default' => 60
        ));
    }
    
    /**
     * Sanitizar clave API
     */
    public function sanitize_api_key($value) {
        // Log temporal para debug
        error_log("Chatbot IA Debug: sanitize_api_key - Valor recibido: " . (empty($value) ? 'VACÍO' : 'PRESENTE (' . strlen($value) . ' chars)'));
        
        $value = sanitize_text_field($value);
        
        // Si está vacío, devolver valor vacío (no encriptar)
        if (empty($value)) {
            error_log("Chatbot IA Debug: sanitize_api_key - Valor vacío después de sanitizar");
            return '';
        }
        
        // Validar formato de clave API de DeepSeek (permite guiones y guiones bajos)
        if (!preg_match('/^sk-[a-zA-Z0-9\-_]{32,}$/', $value)) {
            error_log("Chatbot IA Debug: sanitize_api_key - Formato inválido: " . substr($value, 0, 10) . "...");
            add_settings_error('chatbot_ia_api_key', 'invalid_api_key', __('Formato de clave API inválido. Debe comenzar con "sk-" seguido de al menos 32 caracteres alfanuméricos, guiones o guiones bajos.', 'chatbot-ia'));
            return ''; // Devolver vacío si es inválido
        }
        
        // Encriptar la clave API antes de guardarla
        $encrypted_value = $this->encrypt_api_key($value);
        error_log("Chatbot IA Debug: sanitize_api_key - Encriptación: " . ($encrypted_value === false ? 'FALLÓ' : 'EXITOSA (' . strlen($encrypted_value) . ' chars)'));
        
        return $encrypted_value;
    }
    
    /**
     * Encriptar clave API
     */
    public function encrypt_api_key($api_key) {
        if (empty($api_key)) {
            error_log("Chatbot IA Debug: encrypt_api_key - Clave vacía");
            return '';
        }
        
        if (function_exists('openssl_encrypt') && function_exists('random_bytes')) {
            try {
                $key = $this->get_encryption_key();
                $iv = random_bytes(16);
                $encrypted = openssl_encrypt($api_key, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
                if ($encrypted === false) {
                    error_log("Chatbot IA Debug: encrypt_api_key - openssl_encrypt falló, usando fallback");
                    // Si falla la encriptación, usar fallback
                    return base64_encode($api_key);
                }
                $result = base64_encode($iv . $encrypted);
                error_log("Chatbot IA Debug: encrypt_api_key - Encriptación exitosa: " . strlen($result) . " chars");
                return $result;
            } catch (Exception $e) {
                error_log("Chatbot IA Debug: encrypt_api_key - Excepción: " . $e->getMessage());
                // Si hay cualquier error, usar fallback
                return base64_encode($api_key);
            }
        }
        // Fallback: usar base64 si OpenSSL no está disponible
        error_log("Chatbot IA Debug: encrypt_api_key - OpenSSL no disponible, usando fallback");
        return base64_encode($api_key);
    }
    
    /**
     * Desencriptar clave API
     */
    public function decrypt_api_key($encrypted_api_key) {
        if (empty($encrypted_api_key)) {
            error_log("Chatbot IA Debug: decrypt_api_key - Clave encriptada vacía");
            return '';
        }
        
        if (function_exists('openssl_decrypt')) {
            $key = $this->get_encryption_key();
            $data = base64_decode($encrypted_api_key);
            
            // Verificar que tenemos suficientes datos
            if (strlen($data) < 16) {
                error_log("Chatbot IA Debug: decrypt_api_key - Datos insuficientes: " . strlen($data) . " bytes");
                return '';
            }
            
            $iv = substr($data, 0, 16);
            $encrypted = substr($data, 16);
            
            // Asegurar que el IV tenga exactamente 16 bytes
            if (strlen($iv) < 16) {
                $iv = str_pad($iv, 16, "\0");
            }
            
            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
            
            if ($decrypted === false) {
                error_log("Chatbot IA Debug: decrypt_api_key - Error en openssl_decrypt");
                return '';
            }
            
            error_log("Chatbot IA Debug: decrypt_api_key - Desencriptación exitosa: " . strlen($decrypted) . " chars");
            return $decrypted;
        }
        // Fallback: usar base64 si OpenSSL no está disponible
        error_log("Chatbot IA Debug: decrypt_api_key - Usando fallback base64");
        return base64_decode($encrypted_api_key);
    }
    
    /**
     * Obtener clave de encriptación
     */
    private function get_encryption_key() {
        $key = get_option('chatbot_ia_encryption_key', '');
        if (empty($key)) {
            $key = wp_generate_password(32, false);
            update_option('chatbot_ia_encryption_key', $key);
        }
        return hash('sha256', $key . AUTH_SALT);
    }
    
    /**
     * Sanitizar instrucciones del sistema
     */
    public function sanitize_system_instructions($value) {
        return sanitize_textarea_field($value);
    }
    
    /**
     * Sanitizar contexto
     */
    public function sanitize_context($value) {
        return sanitize_textarea_field($value);
    }
    
    /**
     * Sanitizar modelo
     */
    public function sanitize_model($value) {
        $allowed_models = array('deepseek-chat', 'deepseek-reasoner');
        $value = sanitize_text_field($value);
        if (!in_array($value, $allowed_models)) {
            return 'deepseek-chat'; // Valor por defecto si no es válido
        }
        return $value;
    }
    
    /**
     * Sanitizar máximo de tokens
     */
    public function sanitize_max_tokens($value) {
        $value = absint($value);
        $model = get_option('chatbot_ia_model', 'deepseek-chat');
        
        // Límites según el modelo
        if ($model === 'deepseek-reasoner') {
            return max(100, min(64000, $value)); // Hasta 64K para reasoner
        } else {
            return max(100, min(8000, $value)); // Hasta 8K para chat
        }
    }
    
    /**
     * Sanitizar temperatura (debe estar entre 0 y 2)
     */
    public function sanitize_temperature($value) {
        $value = floatval($value);
        return max(0, min(2, $value));
    }
    
    /**
     * Sanitizar idioma por defecto
     */
    public function sanitize_default_language($value) {
        $allowed_languages = array('es', 'auto');
        $value = sanitize_text_field($value);
        if (!in_array($value, $allowed_languages)) {
            return 'es'; // Valor por defecto
        }
        return $value;
    }
    
    /**
     * Sanitizar valores booleanos
     */
    public function sanitize_boolean($value) {
        return (bool) $value;
    }
    
    /**
     * Sanitizar duración de cache
     */
    public function sanitize_cache_duration($value) {
        $value = absint($value);
        return max(300, min(86400, $value)); // Entre 5 minutos y 24 horas
    }
    
    /**
     * Sanitizar límite de tasa
     */
    public function sanitize_rate_limit($value) {
        $value = absint($value);
        return max(1, min(100, $value)); // Entre 1 y 100
    }
    
    /**
     * Sanitizar ventana de límite de tasa
     */
    public function sanitize_rate_limit_window($value) {
        $value = absint($value);
        return max(60, min(3600, $value)); // Entre 1 minuto y 1 hora
    }
    
    /**
     * Página de administración
     */
    public function admin_page() {
        // Mostrar errores de configuración si los hay
        settings_errors('chatbot_ia_api_key');
        settings_errors('chatbot_ia_options');
        
        // Incluir archivo de la página de administración
        include CHATBOT_IA_PLUGIN_DIR . 'admin/admin-page.php';
    }
    
    /**
     * Encolar scripts del frontend
     */
    public function enqueue_frontend_scripts() {
        // Solo cargar en páginas que contengan el shortcode
        if ($this->is_shortcode_present()) {
            wp_enqueue_style(
                'chatbot-ia-style',
                CHATBOT_IA_PLUGIN_URL . 'assets/css/chatbot-ia.css',
                array(),
                CHATBOT_IA_VERSION
            );
            
            wp_enqueue_script(
                'chatbot-ia-script',
                CHATBOT_IA_PLUGIN_URL . 'assets/js/chatbot-ia.js',
                array('jquery'),
                CHATBOT_IA_VERSION,
                true
            );
            
            // Localizar script con datos necesarios
            wp_localize_script('chatbot-ia-script', 'chatbotIa', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('chatbot_ia_nonce'),
                'strings' => array(
                    'loading' => __('Cargando...', 'chatbot-ia'),
                    'error' => __('Lo siento, ha ocurrido un error. Intenta de nuevo.', 'chatbot-ia'),
                    'empty_message' => __('Por favor, escribe un mensaje.', 'chatbot-ia'),
                    'rate_limit' => __('Has alcanzado el límite de mensajes. Espera un momento.', 'chatbot-ia'),
                    'api_error' => __('Error en la conexión con la IA. Verifica la configuración.', 'chatbot-ia')
                )
            ));
        }
    }
    
    /**
     * Encolar scripts del administrador
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook === 'settings_page_chatbot-ia' || $hook === 'toplevel_page_chatbot-ia') {
            wp_enqueue_style(
                'chatbot-ia-admin-style',
                CHATBOT_IA_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                CHATBOT_IA_VERSION
            );
            
            wp_enqueue_script(
                'chatbot-ia-admin-script',
                CHATBOT_IA_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                CHATBOT_IA_VERSION,
                true
            );
            
            wp_localize_script('chatbot-ia-admin-script', 'chatbotIaAdmin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('chatbot_ia_admin_nonce'),
                'strings' => array(
                    'testing_connection' => __('Probando conexión...', 'chatbot-ia'),
                    'connection_success' => __('Conexión exitosa', 'chatbot-ia'),
                    'connection_error' => __('Error en la conexión', 'chatbot-ia'),
                    'saving' => __('Guardando...', 'chatbot-ia'),
                    'saved' => __('Configuración guardada', 'chatbot-ia')
                )
            ));
        }
    }
    
    /**
     * Verificar si el shortcode está presente en la página actual
     */
    private function is_shortcode_present() {
        global $post;
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'chatbot-ia')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Encolar assets del chatbot si es necesario
     */
    public function maybe_enqueue_chatbot_assets() {
        if ($this->is_shortcode_present()) {
            $this->enqueue_frontend_scripts();
        }
    }
    
    /**
     * Renderizar shortcode del chatbot
     */
    public function render_chatbot_shortcode($atts) {
        $atts = shortcode_atts(array(
            'position' => 'bottom-right',
            'theme' => 'default',
            'size' => 'medium'
        ), $atts);
        
        ob_start();
        include CHATBOT_IA_PLUGIN_DIR . 'templates/chatbot-widget.php';
        return ob_get_clean();
    }
    
    /**
     * Manejar consultas del chat via AJAX
     */
    public function handle_chat_query() {
        // Verificar que es una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_die(__('Método no permitido', 'chatbot-ia'));
        }
        
        // Verificar nonce (aceptar tanto chatbot_ia_nonce como chatbot_ia_admin_nonce)
        $nonce_valid = wp_verify_nonce($_POST['nonce'], 'chatbot_ia_nonce') || 
                       wp_verify_nonce($_POST['nonce'], 'chatbot_ia_admin_nonce');
        
        if (!$nonce_valid) {
            wp_die(__('Error de seguridad', 'chatbot-ia'));
        }
        
        // Verificar límite de tasa
        if (!$this->check_rate_limit()) {
            wp_send_json_error(array(
                'message' => __('Has alcanzado el límite de mensajes. Espera un momento.', 'chatbot-ia')
            ));
        }
        
        // Sanitizar entrada
        $user_message = sanitize_textarea_field($_POST['message']);
        
        if (empty($user_message)) {
            wp_send_json_error(array(
                'message' => __('Por favor, escribe un mensaje.', 'chatbot-ia')
            ));
        }
        
        // Validar longitud del mensaje (máximo 2000 caracteres)
        if (strlen($user_message) > 2000) {
            wp_send_json_error(array(
                'message' => __('El mensaje es demasiado largo. Máximo 2000 caracteres.', 'chatbot-ia')
            ));
        }
        
        // Verificar cache si está habilitado
        if ($this->options['enable_caching']) {
            $cache_key = 'chatbot_ia_' . md5($user_message);
            $cached_response = get_transient($cache_key);
            
            if ($cached_response !== false) {
                wp_send_json_success(array(
                    'response' => $cached_response,
                    'cached' => true
                ));
            }
        }
        
        // Consultar API de DeepSeek
        $response = $this->query_deepseek_api($user_message);
        
        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => $response->get_error_message()
            ));
        }
        
        // Guardar en cache si está habilitado
        if ($this->options['enable_caching'] && !empty($response)) {
            set_transient($cache_key, $response, $this->options['cache_duration']);
        }
        
        // Log de la consulta (opcional)
        $this->log_query($user_message, $response);
        
        wp_send_json_success(array(
            'response' => $response,
            'cached' => false
        ));
    }
    
    /**
     * Consultar API de DeepSeek
     */
    private function query_deepseek_api($user_message) {
        $api_key = $this->options['api_key'];
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('Clave API no configurada', 'chatbot-ia'));
        }
        
        return $this->query_deepseek_api_with_key($user_message, $api_key);
    }
    
    /**
     * Consultar API de DeepSeek con clave específica
     */
    private function query_deepseek_api_with_key($user_message, $api_key) {
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('Clave API no configurada', 'chatbot-ia'));
        }
        
        // Preparar mensajes para la API
        $messages = array();
        
        // Añadir instrucciones del sistema si están configuradas
        if (!empty($this->options['system_instructions'])) {
            $messages[] = array(
                'role' => 'system',
                'content' => $this->options['system_instructions']
            );
        }
        
        // Añadir contexto si está configurado
        if (!empty($this->options['context'])) {
            $messages[] = array(
                'role' => 'system',
                'content' => $this->options['context']
            );
        }
        
        // Añadir mensaje del usuario
        $messages[] = array(
            'role' => 'user',
            'content' => $user_message
        );
        
        // Preparar datos para la API
        $data = array(
            'model' => $this->options['model'],
            'messages' => $messages,
            'max_tokens' => (int) $this->options['max_tokens'],
            'temperature' => (float) $this->options['temperature'],
            'stream' => false
        );
        
        // Realizar petición a la API
        $response = wp_remote_post('https://api.deepseek.com/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return new WP_Error('api_error', __('Error en la conexión con la API', 'chatbot-ia'));
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Manejar códigos de error específicos de DeepSeek
        if ($response_code !== 200) {
            $error_message = $this->get_deepseek_error_message($response_code, $data);
            return new WP_Error('api_error', $error_message);
        }
        
        if (isset($data['error'])) {
            return new WP_Error('api_error', $data['error']['message']);
        }
        
        if (!isset($data['choices'][0]['message']['content'])) {
            return new WP_Error('api_error', __('Respuesta inválida de la API', 'chatbot-ia'));
        }
        
        return $data['choices'][0]['message']['content'];
    }
    
    /**
     * Obtener mensaje de error específico de DeepSeek
     */
    private function get_deepseek_error_message($response_code, $data) {
        $error_messages = array(
            400 => __('Formato de solicitud inválido. Verifica los parámetros enviados.', 'chatbot-ia'),
            401 => __('Autenticación fallida. Verifica tu clave API de DeepSeek.', 'chatbot-ia'),
            402 => __('Saldo insuficiente. La clave API es válida pero no tiene fondos. Recarga tu cuenta en DeepSeek Platform o usa una clave con saldo disponible.', 'chatbot-ia'),
            422 => __('Parámetros inválidos. Verifica la configuración del modelo y tokens.', 'chatbot-ia'),
            429 => __('Límite de tasa alcanzado. Reduce la frecuencia de solicitudes.', 'chatbot-ia'),
            500 => __('Error del servidor. Intenta de nuevo en unos momentos.', 'chatbot-ia'),
            503 => __('Servidor sobrecargado. Intenta de nuevo más tarde.', 'chatbot-ia')
        );
        
        $base_message = isset($error_messages[$response_code]) ? $error_messages[$response_code] : __('Error desconocido de la API', 'chatbot-ia');
        
        // Añadir detalles específicos si están disponibles
        if (isset($data['error']['message'])) {
            $base_message .= ' Detalles: ' . $data['error']['message'];
        }
        
        return $base_message;
    }
    
    /**
     * Verificar límite de tasa
     */
    private function check_rate_limit() {
        $user_ip = $this->get_user_ip();
        $cache_key = 'chatbot_ia_rate_' . md5($user_ip);
        
        $requests = get_transient($cache_key);
        
        if ($requests === false) {
            $requests = 0;
        }
        
        if ($requests >= $this->options['rate_limit']) {
            return false;
        }
        
        set_transient($cache_key, $requests + 1, $this->options['rate_limit_window']);
        
        return true;
    }
    
    /**
     * Obtener IP del usuario
     */
    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
    
    /**
     * Probar conexión con la API
     */
    public function test_api_connection() {
        // Verificar que es una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_die(__('Método no permitido', 'chatbot-ia'));
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'chatbot_ia_admin_nonce')) {
            wp_die(__('Error de seguridad', 'chatbot-ia'));
        }
        
        // Obtener la clave API del formulario
        $api_key = '';
        if (isset($_POST['api_key']) && !empty($_POST['api_key'])) {
            $api_key = sanitize_text_field($_POST['api_key']);
            
            // Validar formato de clave API (permite guiones y guiones bajos)
            if (!preg_match('/^sk-[a-zA-Z0-9\-_]{32,}$/', $api_key)) {
                wp_send_json_error(array(
                    'message' => __('Formato de clave API inválido. Debe comenzar con "sk-" seguido de al menos 32 caracteres alfanuméricos, guiones o guiones bajos.', 'chatbot-ia')
                ));
            }
            
            // NO guardar la clave API aquí - solo probar la conexión
            // El guardado se hará en la función de guardar configuraciones
            
        } else {
            // Desencriptar la clave API guardada
            $encrypted_api_key = get_option('chatbot_ia_api_key', '');
            if (!empty($encrypted_api_key)) {
                $api_key = $this->decrypt_api_key($encrypted_api_key);
            }
        }
        
        if (empty($api_key)) {
            wp_send_json_error(array(
                'message' => __('Clave API no configurada. Por favor, ingresa tu clave API de DeepSeek.', 'chatbot-ia')
            ));
        }
        
        $test_message = 'Hola, ¿puedes responder con "Conexión exitosa"?';
        $response = $this->query_deepseek_api_with_key($test_message, $api_key);
        
        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => $response->get_error_message()
            ));
        }
        
        wp_send_json_success(array(
            'message' => __('Conexión exitosa', 'chatbot-ia'),
            'response' => $response
        ));
    }
    
    /**
     * Guardar configuraciones via AJAX
     */
    public function save_settings_ajax() {
        // Verificar que es una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_die(__('Método no permitido', 'chatbot-ia'));
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'chatbot_ia_admin_nonce')) {
            wp_die(__('Error de seguridad', 'chatbot-ia'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos para realizar esta acción', 'chatbot-ia'));
        }
        
        // Log temporal para debug (remover en producción)
        error_log("Chatbot IA Debug: POST data recibido - api_key: " . (isset($_POST['api_key']) ? 'SÍ (' . strlen($_POST['api_key']) . ' chars)' : 'NO'));
        
        // Procesar cada campo del formulario
        $settings = array(
            'chatbot_ia_api_key' => isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '',
            'chatbot_ia_system_instructions' => isset($_POST['system_instructions']) ? sanitize_textarea_field($_POST['system_instructions']) : '',
            'chatbot_ia_context' => isset($_POST['context']) ? sanitize_textarea_field($_POST['context']) : '',
            'chatbot_ia_model' => isset($_POST['model']) ? sanitize_text_field($_POST['model']) : 'deepseek-chat',
            'chatbot_ia_max_tokens' => isset($_POST['max_tokens']) ? absint($_POST['max_tokens']) : 1000,
            'chatbot_ia_temperature' => isset($_POST['temperature']) ? floatval($_POST['temperature']) : 1.0,
            'chatbot_ia_default_language' => isset($_POST['default_language']) ? sanitize_text_field($_POST['default_language']) : 'es',
            'chatbot_ia_enable_caching' => isset($_POST['enable_caching']) ? (bool) $_POST['enable_caching'] : true,
            'chatbot_ia_cache_duration' => isset($_POST['cache_duration']) ? absint($_POST['cache_duration']) : 3600,
            'chatbot_ia_rate_limit' => isset($_POST['rate_limit']) ? absint($_POST['rate_limit']) : 10,
            'chatbot_ia_rate_limit_window' => isset($_POST['rate_limit_window']) ? absint($_POST['rate_limit_window']) : 60
        );
        
        // Validar y sanitizar cada configuración
        $errors = array();
        
        // Validar y encriptar clave API
        if (!empty($settings['chatbot_ia_api_key'])) {
            // Patrón más flexible que permite guiones y otros caracteres comunes en claves API
            if (!preg_match('/^sk-[a-zA-Z0-9\-_]{32,}$/', $settings['chatbot_ia_api_key'])) {
                $errors[] = __('Formato de clave API inválido. Debe comenzar con "sk-" seguido de al menos 32 caracteres alfanuméricos, guiones o guiones bajos.', 'chatbot-ia');
            } else {
                // Encriptar la clave API
                $encrypted_key = $this->encrypt_api_key($settings['chatbot_ia_api_key']);
                if ($encrypted_key === false) {
                    $errors[] = __('Error al encriptar la clave API. Verifica la configuración del servidor.', 'chatbot-ia');
                } else {
                    $settings['chatbot_ia_api_key'] = $encrypted_key;
                }
            }
        }
        
        // Validar límites de tokens según el modelo
        if ($settings['chatbot_ia_model'] === 'deepseek-reasoner') {
            $settings['chatbot_ia_max_tokens'] = max(100, min(64000, $settings['chatbot_ia_max_tokens']));
        } else {
            $settings['chatbot_ia_max_tokens'] = max(100, min(8000, $settings['chatbot_ia_max_tokens']));
        }
        
        // Validar temperatura
        $settings['chatbot_ia_temperature'] = max(0, min(2, $settings['chatbot_ia_temperature']));
        
        // Validar modelo
        $allowed_models = array('deepseek-chat', 'deepseek-reasoner');
        if (!in_array($settings['chatbot_ia_model'], $allowed_models)) {
            $settings['chatbot_ia_model'] = 'deepseek-chat';
        }
        
        // Validar idioma
        $allowed_languages = array('es', 'auto');
        if (!in_array($settings['chatbot_ia_default_language'], $allowed_languages)) {
            $settings['chatbot_ia_default_language'] = 'es';
        }
        
        // Si hay errores, devolverlos
        if (!empty($errors)) {
            wp_send_json_error(array(
                'message' => implode(', ', $errors)
            ));
        }
        
        // Guardar todas las configuraciones
        foreach ($settings as $option => $value) {
            // Usar update_option que respeta los callbacks de sanitización
            $result = update_option($option, $value);
            
            // Log temporal para debug (remover en producción)
            if ($option === 'chatbot_ia_api_key') {
                error_log("Chatbot IA Debug: Guardando API key - Valor: " . (empty($value) ? 'VACÍO' : 'ENCRIPTADO (' . strlen($value) . ' chars)') . " - Resultado: " . ($result ? 'EXITOSO' : 'FALLÓ'));
            }
        }
        
        // Recargar opciones
        $this->load_options();
        
        wp_send_json_success(array(
            'message' => __('Configuraciones guardadas correctamente', 'chatbot-ia')
        ));
    }
    
    /**
     * Registrar consulta en logs (opcional)
     */
    private function log_query($query, $response) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'chatbot_ia_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'user_ip' => $this->get_user_ip(),
                'query' => $query,
                'response' => $response,
                'timestamp' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Agregar headers de seguridad
     */
    public function add_security_headers() {
        // Solo agregar headers en páginas que usan el chatbot
        if (is_singular() && has_shortcode(get_post()->post_content, 'chatbot-ia')) {
            // Content Security Policy para el chatbot
            header("Content-Security-Policy: script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';");
        }
    }
}

// Inicializar el plugin
function chatbot_ia_deepseek_init() {
    return Chatbot_IA::get_instance();
}

// Iniciar el plugin
chatbot_ia_deepseek_init();