# Estructura del Plugin Chatbot IA DeepSeek

## Archivos Creados

### Archivos Principales
- `testplugin.php` - Archivo principal del plugin con la clase Chatbot_IA
- `uninstall.php` - Script de limpieza al desinstalar el plugin

### Administración
- `admin/admin-page.php` - Página de configuración del plugin en el admin

### Frontend
- `templates/chatbot-widget.php` - Template del widget del chatbot
- `assets/css/chatbot-ia.css` - Estilos del frontend
- `assets/css/admin.css` - Estilos del panel de administración
- `assets/js/chatbot-ia.js` - JavaScript del frontend
- `assets/js/admin.js` - JavaScript del panel de administración

### Traducciones
- `languages/chatbot-ia.pot` - Archivo de plantilla de traducción
- `languages/chatbot-ia-es_ES.po` - Traducción al español
- `languages/chatbot-ia-es_ES.mo` - Archivo compilado de traducción

### Documentación
- `README.md` - Documentación completa del plugin
- `INSTALACION.md` - Instrucciones de instalación rápida
- `ESTRUCTURA.md` - Este archivo

## Características Implementadas

### ✅ Funcionalidades Core
- [x] Integración con DeepSeek API
- [x] Clase principal con patrón Singleton
- [x] Manejo seguro de claves API
- [x] Sistema de cache con transients
- [x] Límites de tasa por IP
- [x] Logging opcional de consultas

### ✅ Panel de Administración
- [x] Página de configuración con pestañas
- [x] Validación de formularios
- [x] Prueba de conexión API
- [x] Chat de prueba integrado
- [x] Información del sistema
- [x] Interfaz completamente en español

### ✅ Frontend
- [x] Shortcode `[chatbot-ia]`
- [x] Botón flotante animado
- [x] Ventana de chat responsiva
- [x] Historial de conversación
- [x] Indicador de escritura
- [x] Manejo de errores
- [x] Accesibilidad (ARIA labels)

### ✅ Optimizaciones
- [x] Carga condicional de assets
- [x] Cache de respuestas
- [x] Límites de tasa
- [x] Sanitización de datos
- [x] Validación de nonces
- [x] Escape de salidas

### ✅ Internacionalización
- [x] Archivos de traducción (.pot, .po, .mo)
- [x] Funciones __() y _e()
- [x] Textdomain configurado
- [x] Traducción completa al español

### ✅ Seguridad
- [x] Verificación de nonces
- [x] Sanitización de entradas
- [x] Escape de salidas
- [x] Validación de permisos
- [x] Protección contra acceso directo

## Configuración por Defecto

### Clave API
- Proporcionada: `sk-a42f9205e7ba4a338c80bc8c1f1fd88e`

### Modelo
- Por defecto: `deepseek-chat` (más económico)

### Instrucciones del Sistema
```
Responde de manera útil y concisa como un agente de soporte al cliente. Siempre responde en español.
```

### Contexto
```
Eres un asistente de IA especializado en ayudar a los usuarios. Responde siempre en español independientemente del idioma de la consulta del usuario.
```

## Uso del Plugin

### Instalación
1. Subir archivos a `/wp-content/plugins/testplugin/`
2. Activar el plugin
3. Configurar en `Configuración > Chatbot IA`

### Uso en Páginas
```php
[chatbot-ia]
[chatbot-ia position="bottom-left" theme="dark" size="large"]
```

### Personalización
- CSS personalizable con variables CSS
- JavaScript extensible con eventos
- Múltiples opciones de configuración

## Compatibilidad

- WordPress 6.0+
- PHP 7.4+
- Navegadores modernos
- Dispositivos móviles
- Temas de WordPress

## Próximos Pasos

1. **Instalar el plugin** siguiendo las instrucciones
2. **Configurar la API** con la clave proporcionada
3. **Probar la conexión** en el panel de admin
4. **Añadir el shortcode** a una página
5. **Personalizar** según necesidades

¡El plugin está listo para usar! 🚀
