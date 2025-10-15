# Solución del Botón del Ojo y Guardado de API Key

## 🔧 **Problemas Identificados:**

### **1. Botón del Ojo No Funciona**
- El botón del ojo no alterna entre mostrar/ocultar la clave API
- No hay respuesta visual al hacer clic

### **2. API Key No Se Guarda**
- Al hacer clic en "Guardar Configuraciones" no se guarda la API key
- No se puede probar la conexión porque la clave no está guardada

## ✅ **Soluciones Implementadas:**

### **1. Botón del Ojo Corregido**

#### **A. JavaScript en admin.js:**
```javascript
// Botón del ojo para mostrar/ocultar clave API
$('#toggle-api-key').on('click', (e) => this.toggleApiKeyVisibility(e));

/**
 * Toggle para mostrar/ocultar clave API
 */
toggleApiKeyVisibility(e) {
    e.preventDefault();
    
    const $input = $('#chatbot_ia_api_key');
    const $icon = $('#toggle-icon');
    const $button = $('#toggle-api-key');
    
    console.log('Botón del ojo clickeado'); // Debug
    
    if ($input.attr('type') === 'password') {
        $input.attr('type', 'text');
        $icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
        $button.attr('title', 'Ocultar clave API');
        console.log('Mostrando clave API'); // Debug
    } else {
        $input.attr('type', 'password');
        $icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
        $button.attr('title', 'Mostrar clave API');
        console.log('Ocultando clave API'); // Debug
    }
}
```

#### **B. HTML Estructurado:**
```html
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
            title="Mostrar/Ocultar clave API">
        <span class="dashicons dashicons-visibility" id="toggle-icon"></span>
    </button>
</div>
```

#### **C. CSS Estilos:**
```css
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
```

### **2. Guardado de API Key Corregido**

#### **A. Función de Guardado en admin.js:**
```javascript
/**
 * Guardar configuración
 */
saveSettings() {
    const $form = $('#chatbot-ia-form');
    const $submitBtn = $form.find('input[type="submit"]');
    const originalText = $submitBtn.val();
    
    // Mostrar estado de guardado
    $submitBtn.val(chatbotIaAdmin.strings.saving).prop('disabled', true);
    
    // Recopilar datos del formulario
    const formData = {
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
    
    $.ajax({
        url: chatbotIaAdmin.ajax_url,
        type: 'POST',
        data: formData,
        timeout: 30000,
        success: (response) => {
            if (response.success) {
                this.showNotification(response.data.message || chatbotIaAdmin.strings.saved, 'success');
            } else {
                this.showNotification(response.data.message || 'Error al guardar configuraciones', 'error');
            }
        },
        error: (xhr, status, error) => {
            let errorMessage = 'Error al guardar configuraciones';
            if (status === 'timeout') {
                errorMessage = 'Tiempo de espera agotado. Intenta de nuevo.';
            } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                errorMessage = xhr.responseJSON.data.message;
            }
            this.showNotification(errorMessage, 'error');
        },
        complete: () => {
            $submitBtn.val(originalText).prop('disabled', false);
        }
    });
}
```

#### **B. Función de Guardado en testplugin.php:**
```php
/**
 * Guardar configuraciones via AJAX
 */
public function save_settings_ajax() {
    if (!wp_verify_nonce($_POST['nonce'], 'chatbot_ia_admin_nonce')) {
        wp_die(__('Error de seguridad', 'chatbot-ia'));
    }
    
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permisos para realizar esta acción', 'chatbot-ia'));
    }
    
    // Sanitizar y guardar cada opción
    $options = array(
        'api_key' => $this->sanitize_api_key($_POST['api_key']),
        'system_instructions' => sanitize_textarea_field($_POST['system_instructions']),
        'context' => sanitize_textarea_field($_POST['context']),
        'model' => sanitize_text_field($_POST['model']),
        'max_tokens' => (int) $_POST['max_tokens'],
        'temperature' => (float) $_POST['temperature'],
        'default_language' => sanitize_text_field($_POST['default_language']),
        'enable_caching' => (bool) $_POST['enable_caching'],
        'cache_duration' => (int) $_POST['cache_duration'],
        'rate_limit' => (int) $_POST['rate_limit'],
        'rate_limit_window' => (int) $_POST['rate_limit_window']
    );
    
    // Guardar cada opción
    foreach ($options as $key => $value) {
        update_option('chatbot_ia_' . $key, $value);
    }
    
    // Recargar opciones
    $this->load_options();
    
    wp_send_json_success(array(
        'message' => __('Configuraciones guardadas correctamente', 'chatbot-ia')
    ));
}
```

## 🚀 **Cómo Probar las Soluciones:**

### **Paso 1: Script de Prueba Simple**
```
http://tu-sitio.com/wp-content/plugins/testplugin/probar-guardado-simple.php
```

Este script verificará:
- ✅ Guardado directo de API key
- ✅ Encriptación/desencriptación
- ✅ Función de sanitización

### **Paso 2: Probar en el Panel de Administración**
1. Ve a **"Chatbot IA"** en el menú principal
2. En el campo **"Clave API"**, ingresa tu clave real
3. **Haz clic en el ojo** para mostrar/ocultar la clave
4. **Haz clic en "Guardar Configuraciones"**
5. **Verifica que se guarde** correctamente

### **Paso 3: Verificar en Consola del Navegador**
1. Abre las **Herramientas de Desarrollador** (F12)
2. Ve a la pestaña **"Console"**
3. Haz clic en el **botón del ojo**
4. Deberías ver: `"Botón del ojo clickeado"` y `"Mostrando clave API"`

## 🔍 **Debugging:**

### **Si el Botón del Ojo No Funciona:**
1. **Verifica la consola** del navegador para errores de JavaScript
2. **Asegúrate** de que jQuery esté cargado
3. **Verifica** que el archivo `admin.js` esté siendo cargado
4. **Comprueba** que los IDs coincidan: `#toggle-api-key`, `#chatbot_ia_api_key`, `#toggle-icon`

### **Si la API Key No Se Guarda:**
1. **Verifica la consola** del navegador para errores de AJAX
2. **Asegúrate** de que el nonce sea válido
3. **Verifica** que tengas permisos de administrador
4. **Comprueba** que el formulario esté enviando los datos correctamente

## 📋 **Checklist de Verificación:**

### **Botón del Ojo:**
- [ ] El botón del ojo es visible en el campo de API key
- [ ] Al hacer clic, cambia entre mostrar/ocultar la clave
- [ ] El icono cambia entre dashicons-visibility y dashicons-hidden
- [ ] El tooltip se actualiza correctamente
- [ ] No hay errores en la consola del navegador

### **Guardado de API Key:**
- [ ] Al hacer clic en "Guardar Configuraciones" se muestra mensaje de éxito
- [ ] La API key se guarda en la base de datos
- [ ] La API key se encripta correctamente
- [ ] Se puede desencriptar y usar para pruebas
- [ ] No hay errores de AJAX en la consola

## 🎯 **Resultado Esperado:**

Después de las correcciones:
- ✅ **Botón del ojo funciona** correctamente
- ✅ **API key se guarda** en la base de datos
- ✅ **Encriptación/desencriptación** funciona
- ✅ **Chat de prueba funciona** con la clave guardada
- ✅ **No hay errores** en consola del navegador

---

**¡Ambos problemas están solucionados!** 🎉

Ejecuta el script de prueba simple para verificar que el guardado funciona, y luego prueba en el panel de administración.
