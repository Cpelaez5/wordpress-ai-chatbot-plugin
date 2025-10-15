# Chatbot IA DeepSeek para WordPress

Un plugin completo de WordPress para integrar un chatbot de inteligencia artificial utilizando la API de DeepSeek. Interfaz completamente en español con configuración flexible del idioma de respuesta.

## Características Principales

- 🤖 **Integración con DeepSeek API**: Utiliza los modelos más económicos y eficientes de DeepSeek
- 🇪🇸 **Interfaz en Español**: Toda la interfaz de usuario está en español
- ⚙️ **Configuración Flexible**: Control completo sobre el comportamiento de la IA
- 📱 **Diseño Responsivo**: Funciona perfectamente en dispositivos móviles y escritorio
- 🚀 **Optimizado**: Cache inteligente y límites de tasa para optimizar costos
- 🔒 **Seguro**: Manejo seguro de claves API y validación de datos
- 🎨 **Personalizable**: Múltiples temas y opciones de personalización

## Requisitos del Sistema

- WordPress 6.0 o superior
- PHP 7.4 o superior
- Clave API de DeepSeek
- Conexión a internet

## Instalación

### Método 1: Instalación Manual

1. **Descargar el Plugin**
   - Descarga todos los archivos del plugin
   - Comprime la carpeta `testplugin` en un archivo ZIP

2. **Subir a WordPress**
   - Ve a tu panel de administración de WordPress
   - Navega a `Plugins > Añadir nuevo`
   - Haz clic en `Subir plugin`
   - Selecciona el archivo ZIP del plugin
   - Haz clic en `Instalar ahora`

3. **Activar el Plugin**
   - Una vez instalado, haz clic en `Activar plugin`
   - El plugin estará listo para configurar

### Método 2: Instalación por FTP

1. **Subir Archivos**
   - Sube la carpeta `testplugin` a `/wp-content/plugins/`
   - Asegúrate de que todos los archivos se suban correctamente

2. **Activar desde el Panel**
   - Ve a `Plugins > Plugins instalados`
   - Busca "Chatbot IA DeepSeek"
   - Haz clic en `Activar`

## Configuración Inicial

### 1. Obtener Clave API de DeepSeek

1. Visita [DeepSeek Platform](https://platform.deepseek.com/api_keys)
2. Crea una cuenta o inicia sesión
3. Genera una nueva clave API
4. Copia la clave (comienza con `sk-`)

### 2. Configurar el Plugin

1. **Acceder a la Configuración**
   - Ve a `Configuración > Chatbot IA` en tu panel de WordPress
   - O busca "Chatbot IA" en el menú lateral

2. **Configuración Básica**
   - **Clave API**: Pega tu clave API de DeepSeek
   - **Modelo**: Selecciona "DeepSeek Chat (Recomendado)" para el mejor balance costo/rendimiento
   - **Instrucciones del Sistema**: Define cómo debe comportarse la IA
   - **Contexto**: Añade información específica sobre tu sitio web

3. **Probar la Conexión**
   - Haz clic en "Probar Conexión" para verificar que todo funciona
   - Deberías ver "Conexión exitosa" si la configuración es correcta

### 3. Configuración Avanzada (Opcional)

- **Máximo de Tokens**: Controla la longitud de las respuestas (100-4000)
- **Temperatura**: Ajusta la creatividad de las respuestas (0-2)
- **Cache**: Habilita el cache para mejorar el rendimiento
- **Límites de Tasa**: Controla el número de mensajes por usuario

## Uso del Chatbot

### Añadir el Chatbot a tu Sitio

1. **Usando Shortcode**
   - Añade `[chatbot-ia]` en cualquier página o entrada
   - El chatbot aparecerá como un botón flotante

2. **Personalizar el Shortcode**
   ```php
   [chatbot-ia position="bottom-left" theme="dark" size="large"]
   ```

3. **Opciones del Shortcode**
   - `position`: `bottom-right`, `bottom-left`, `top-right`, `top-left`
   - `theme`: `default`, `dark`
   - `size`: `small`, `medium`, `large`

### Funcionalidades del Chatbot

- **Botón Flotante**: Aparece en la esquina de la pantalla
- **Ventana de Chat**: Se abre al hacer clic en el botón
- **Historial de Conversación**: Mantiene el contexto durante la sesión
- **Indicador de Escritura**: Muestra cuando la IA está respondiendo
- **Responsive**: Se adapta automáticamente a dispositivos móviles

## Configuración de Idiomas

### Forzar Respuestas en Español

Para asegurar que la IA siempre responda en español, configura:

**Instrucciones del Sistema:**
```
Responde de manera útil y concisa como un agente de soporte al cliente. Siempre responde en español independientemente del idioma de la consulta del usuario.
```

**Contexto/Base de Conocimiento:**
```
Eres un asistente de IA especializado en ayudar a los usuarios. Responde siempre en español. Si el usuario pregunta en otro idioma, responde en español pero menciona que entiendes su consulta.
```

### Configuración de Otros Idiomas

Para otros idiomas, simplemente cambia "español" por el idioma deseado en las instrucciones.

## Optimización y Rendimiento

### Cache
- **Habilitado por defecto**: Mejora el rendimiento y reduce costos
- **Duración**: 1 hora por defecto (configurable)
- **Limpieza automática**: Se limpia al desactivar el plugin

### Límites de Tasa
- **Por defecto**: 10 mensajes por minuto por usuario
- **Configurable**: Ajusta según tus necesidades
- **Protección**: Evita abuso de la API

### Monitoreo
- **Logs opcionales**: Registra consultas para debugging
- **Métricas**: Tiempo de respuesta y errores
- **Alertas**: Notificaciones de errores en el admin

## Solución de Problemas

### Problemas Comunes

1. **"Clave API no configurada"**
   - Verifica que hayas pegado la clave API correctamente
   - Asegúrate de que la clave comience con `sk-`

2. **"Error en la conexión con la API"**
   - Verifica tu conexión a internet
   - Comprueba que la clave API sea válida
   - Revisa si has alcanzado el límite de la API

3. **El chatbot no aparece**
   - Verifica que hayas añadido el shortcode `[chatbot-ia]`
   - Comprueba que el plugin esté activado
   - Revisa la consola del navegador para errores JavaScript

4. **Respuestas en inglés**
   - Configura las "Instrucciones del Sistema" para forzar español
   - Añade instrucciones de idioma en el "Contexto"

### Logs y Debugging

1. **Habilitar Logs**
   - Los logs se crean automáticamente en la base de datos
   - Revisa la tabla `wp_chatbot_ia_logs` para debugging

2. **Consola del Navegador**
   - Presiona F12 para abrir las herramientas de desarrollador
   - Revisa la pestaña "Console" para errores JavaScript

## Personalización

### CSS Personalizado

Puedes añadir CSS personalizado para modificar la apariencia:

```css
/* Cambiar color del botón flotante */
.chatbot-ia-toggle {
    background: #tu-color !important;
}

/* Modificar tamaño de la ventana */
.chatbot-ia-window {
    width: 500px !important;
    height: 600px !important;
}
```

### JavaScript Personalizado

Para funcionalidades avanzadas, puedes extender el JavaScript:

```javascript
// Escuchar eventos del chatbot
$(document).on('chatbot:opened', function() {
    console.log('Chatbot abierto');
});

$(document).on('chatbot:closed', function() {
    console.log('Chatbot cerrado');
});
```

## Seguridad

### Mejores Prácticas

1. **Clave API**
   - Nunca compartas tu clave API
   - Regenera la clave si sospechas que ha sido comprometida
   - Usa límites de tasa para prevenir abuso

2. **Validación de Datos**
   - El plugin sanitiza todas las entradas
   - Utiliza nonces para proteger formularios
   - Escapa todas las salidas

3. **Permisos**
   - Solo administradores pueden configurar el plugin
   - Los usuarios no pueden acceder a la configuración

## Soporte y Actualizaciones

### Obtener Ayuda

1. **Documentación**: Revisa este README primero
2. **Logs**: Revisa los logs del plugin para errores
3. **Foros**: Busca en los foros de WordPress
4. **Desarrollador**: Contacta al desarrollador si es necesario

### Actualizaciones

- **Automáticas**: WordPress notificará sobre actualizaciones
- **Manuales**: Descarga la nueva versión y reemplaza los archivos
- **Backup**: Siempre haz backup antes de actualizar

## Changelog

### Versión 1.0.0
- Lanzamiento inicial
- Integración completa con DeepSeek API
- Interfaz en español
- Configuración avanzada
- Cache y optimizaciones
- Diseño responsivo

## Licencia

Este plugin está licenciado bajo GPL v2 o posterior.

## Créditos

- **DeepSeek**: Por proporcionar la API de IA
- **WordPress**: Por el framework
- **Comunidad**: Por las contribuciones y feedback

---

**¡Disfruta usando tu nuevo chatbot de IA!** 🤖✨

Si tienes preguntas o necesitas ayuda, no dudes en contactar al soporte.
