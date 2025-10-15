# Seguridad del Plugin Chatbot IA DeepSeek

## 🔒 Características de Seguridad Implementadas

### 1. **Encriptación de Clave API**
- ✅ **Clave API encriptada** en la base de datos usando AES-256-CBC
- ✅ **Clave de encriptación única** generada automáticamente
- ✅ **Fallback a Base64** si OpenSSL no está disponible
- ✅ **Uso de AUTH_SALT** de WordPress para mayor seguridad

### 2. **Sanitización y Validación de Datos**
- ✅ **Validación de formato** de clave API (sk- + 32+ caracteres)
- ✅ **Sanitización completa** de todas las entradas
- ✅ **Validación de rangos** para números (tokens, temperatura, etc.)
- ✅ **Lista blanca** para modelos y idiomas permitidos
- ✅ **Escape de salidas** para prevenir XSS

### 3. **Protección de Formularios**
- ✅ **Nonces de WordPress** para todos los formularios
- ✅ **Verificación de permisos** (manage_options)
- ✅ **Validación de AJAX** con nonces específicos
- ✅ **Protección CSRF** en todas las acciones

### 4. **Manejo Seguro de Errores**
- ✅ **Mensajes de error** sin información sensible
- ✅ **Logs seguros** sin exponer claves API
- ✅ **Validación de respuestas** de la API
- ✅ **Timeouts configurados** para evitar bloqueos

### 5. **Límites y Controles**
- ✅ **Rate limiting** por IP para prevenir abuso
- ✅ **Límites de tokens** configurable (100-4000)
- ✅ **Cache con expiración** para optimizar rendimiento
- ✅ **Validación de temperatura** (0-2)

## 🛡️ Medidas de Seguridad Adicionales

### Encriptación de Datos
```php
// La clave API se encripta antes de guardarse
$encrypted_key = $this->encrypt_api_key($api_key);

// Se desencripta solo cuando se necesita
$api_key = $this->decrypt_api_key($encrypted_key);
```

### Validación de Entrada
```php
// Validación de formato de clave API
if (!preg_match('/^sk-[a-zA-Z0-9]{32,}$/', $value)) {
    // Error de validación
}

// Validación de rangos
$max_tokens = max(100, min(4000, $value));
```

### Protección de Formularios
```php
// Verificación de nonce
if (!wp_verify_nonce($_POST['nonce'], 'chatbot_ia_admin_nonce')) {
    wp_die('Error de seguridad');
}
```

## 🔐 Configuración de Seguridad

### Clave de Encriptación
- Se genera automáticamente al activar el plugin
- Se almacena en `chatbot_ia_encryption_key`
- Se combina con `AUTH_SALT` de WordPress
- Se elimina al desinstalar el plugin

### Permisos Requeridos
- **Administrador:** `manage_options` para configurar
- **Usuario:** Sin permisos especiales para usar el chat
- **API:** Solo accesible desde el servidor

### Límites de Seguridad
- **Rate Limit:** 10 mensajes por minuto por IP (configurable)
- **Tokens:** Máximo 4000 por respuesta
- **Cache:** 1 hora por defecto
- **Timeout:** 30 segundos para API

## 🚨 Mejores Prácticas de Seguridad

### Para Administradores
1. **Mantener WordPress actualizado**
2. **Usar HTTPS** en producción
3. **Cambiar la clave API** si se sospecha compromiso
4. **Revisar logs** regularmente
5. **Hacer backups** de la configuración

### Para Desarrolladores
1. **No hardcodear** claves API en el código
2. **Usar variables de entorno** en producción
3. **Implementar logging** de seguridad
4. **Validar todas las entradas**
5. **Usar HTTPS** para todas las comunicaciones

## 🔍 Auditoría de Seguridad

### Verificaciones Automáticas
- ✅ Validación de formato de clave API
- ✅ Verificación de rangos numéricos
- ✅ Sanitización de texto
- ✅ Escape de salidas HTML
- ✅ Verificación de nonces

### Verificaciones Manuales
- [ ] Revisar logs de acceso
- [ ] Verificar configuración de HTTPS
- [ ] Comprobar permisos de archivos
- [ ] Validar respuestas de la API
- [ ] Probar límites de tasa

## 📋 Checklist de Seguridad

### Instalación
- [ ] Plugin activado correctamente
- [ ] Clave de encriptación generada
- [ ] Permisos de archivos correctos (644/755)
- [ ] HTTPS configurado

### Configuración
- [ ] Clave API válida configurada
- [ ] Límites de tasa apropiados
- [ ] Cache habilitado
- [ ] Logs configurados

### Monitoreo
- [ ] Revisar logs regularmente
- [ ] Monitorear uso de API
- [ ] Verificar respuestas del chatbot
- [ ] Comprobar rendimiento

## 🆘 En Caso de Problemas de Seguridad

### Si se Compromete la Clave API
1. **Cambiar inmediatamente** la clave en DeepSeek Platform
2. **Actualizar** la configuración del plugin
3. **Revisar logs** para actividad sospechosa
4. **Regenerar** clave de encriptación si es necesario

### Si hay Actividad Sospechosa
1. **Revisar logs** del plugin
2. **Verificar rate limiting**
3. **Comprobar** configuración de seguridad
4. **Contactar** soporte si es necesario

---

**El plugin implementa las mejores prácticas de seguridad de WordPress y está diseñado para ser seguro en entornos de producción.** 🛡️
