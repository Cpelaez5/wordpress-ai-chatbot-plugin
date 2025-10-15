# Solución de Problemas - Chatbot IA DeepSeek

## Problema 1: Plugin no visible en el panel de administración

### ✅ **SOLUCIONADO**
El plugin ahora aparece como un menú principal en el panel de WordPress con el icono de chat.

**Ubicación:** 
- Menú principal: **"Chatbot IA"** (con icono de chat)
- También disponible en: **Ajustes > Chatbot IA**

## Problema 2: Error "Clave API no configurada"

### ✅ **SOLUCIONADO**
La clave API ahora está preconfigurada y la función de prueba funciona correctamente.

## Pasos para Solucionar Completamente:

### Opción 1: Ejecutar Script de Activación (Recomendado)
1. Ve a: `http://tu-sitio.com/wp-content/plugins/testplugin/activar-plugin.php`
2. Ejecuta el script (debe estar logueado como administrador)
3. El script configurará automáticamente todo

### Opción 2: Configuración Manual
1. **Activar el Plugin:**
   - Ve a `Plugins > Plugins instalados`
   - Busca "Chatbot IA DeepSeek"
   - Haz clic en "Activar"

2. **Acceder a la Configuración:**
   - Busca **"Chatbot IA"** en el menú principal del admin
   - O ve a `Ajustes > Chatbot IA`

3. **Verificar la Clave API:**
   - La clave API ya debería estar configurada: `sk-a42f9205e7ba4a338c80bc8c1f1fd88e`
   - Si no aparece, pégala manualmente

4. **Probar la Conexión:**
   - Haz clic en "Probar Conexión"
   - Debería mostrar "Conexión exitosa"

## Verificación Final:

### ✅ Checklist de Verificación:
- [ ] Plugin activado en `Plugins > Plugins instalados`
- [ ] Menú "Chatbot IA" visible en el panel principal
- [ ] Clave API configurada correctamente
- [ ] Prueba de conexión exitosa
- [ ] Shortcode `[chatbot-ia]` añadido a una página
- [ ] Botón flotante del chatbot visible en el frontend

## Si Aún Hay Problemas:

### 1. Limpiar Cache
```php
// Añadir temporalmente en wp-config.php
define('WP_DEBUG', true);
```

### 2. Verificar Permisos
- Asegúrate de estar logueado como administrador
- Verifica que tienes permisos `manage_options`

### 3. Revisar Logs de Error
- Ve a `Herramientas > Salud del sitio`
- Revisa si hay errores relacionados con el plugin

### 4. Desactivar y Reactivar
1. Desactiva el plugin
2. Actívalo nuevamente
3. Ejecuta el script de activación

## Contacto de Soporte:
Si los problemas persisten, revisa:
1. Versión de WordPress (6.0+)
2. Versión de PHP (7.4+)
3. Conexión a internet
4. Logs de error del servidor

---

**¡El plugin debería funcionar perfectamente después de seguir estos pasos!** 🚀
