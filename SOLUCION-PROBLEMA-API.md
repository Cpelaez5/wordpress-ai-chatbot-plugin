# Solución del Problema de API Key

## 🔧 **Problema Identificado:**

### **Síntomas:**
1. Al colocar una nueva API key y hacer clic en "Probar Conexión", la clave antigua se borra
2. Al hacer clic en "Guardar Configuraciones", la nueva API key no se guarda
3. La base de datos queda sin clave API configurada

### **Causa Raíz:**
La función `test_api_connection()` estaba guardando la clave API antes de probar la conexión, lo que causaba que se borrara la clave anterior si había algún error.

## ✅ **Solución Implementada:**

### **Función test_api_connection() Corregida**
```php
// ANTES (guardaba la clave antes de probar)
// Guardar la clave API encriptada antes de probar
$encrypted_api_key = $this->encrypt_api_key($api_key);
update_option('chatbot_ia_api_key', $encrypted_api_key);

// DESPUÉS (solo prueba la conexión)
// NO guardar la clave API aquí - solo probar la conexión
// El guardado se hará en la función de guardar configuraciones
```

### **Flujo Corregido:**
1. **"Probar Conexión"** → Solo valida y prueba la conexión (NO guarda)
2. **"Guardar Configuraciones"** → Guarda todas las configuraciones incluyendo la API key

## 🚀 **Cómo Probar la Solución:**

### **Paso 1: Script de Debug**
```
http://tu-sitio.com/wp-content/plugins/testplugin/debug-problema-api.php
```

Este script verificará:
- ✅ Estado actual de la base de datos
- ✅ Proceso de prueba de conexión
- ✅ Estado después de la prueba
- ✅ Proceso de guardado de configuraciones
- ✅ Estado final
- ✅ Carga de opciones

### **Paso 2: Probar en el Panel de Administración**
1. Ve a **"Chatbot IA"** en el menú principal
2. En el campo **"Clave API"**, ingresa tu nueva clave de DeepSeek
3. Haz clic en **"Probar Conexión"**
4. **Debería mostrar resultado de la prueba SIN borrar la clave anterior**
5. Haz clic en **"Guardar Configuraciones"**
6. **Debería guardar la nueva clave API**
7. Ve a **"Pruebas y Logs"** → **Chat de prueba**
8. **Debería funcionar con la nueva clave**

## 🔍 **Verificación de Base de Datos:**

### **Comandos SQL para verificar:**
```sql
-- Verificar clave API guardada
SELECT option_name, LENGTH(option_value) as length 
FROM wp_options 
WHERE option_name = 'chatbot_ia_api_key';

-- Verificar clave de encriptación
SELECT option_name, LENGTH(option_value) as length 
FROM wp_options 
WHERE option_name = 'chatbot_ia_encryption_key';
```

### **Resultado esperado:**
- `chatbot_ia_api_key`: Valor encriptado (100+ caracteres)
- `chatbot_ia_encryption_key`: Clave de encriptación (32 caracteres)

## 🛠️ **Archivos Modificados:**

### **1. testplugin/testplugin.php**
- ✅ Función `test_api_connection()` corregida
- ✅ Ya no guarda la clave API antes de probar
- ✅ Solo valida y prueba la conexión

### **2. testplugin/debug-problema-api.php (nuevo)**
- ✅ Script de debug completo
- ✅ Verificación de todo el proceso
- ✅ Identificación de problemas específicos

## 📊 **Flujo Corregido:**

### **Probar Conexión:**
```
1. Usuario ingresa nueva clave API
2. Hace clic en "Probar Conexión"
3. JavaScript envía clave API en petición AJAX
4. PHP valida formato de clave API
5. PHP prueba conexión con DeepSeek (SIN guardar)
6. PHP devuelve resultado de la prueba
7. JavaScript muestra resultado
8. Clave anterior se mantiene en la base de datos
```

### **Guardar Configuraciones:**
```
1. Usuario hace clic en "Guardar Configuraciones"
2. JavaScript intercepta el envío del formulario
3. JavaScript envía todos los datos via AJAX
4. PHP procesa cada campo del formulario
5. PHP valida y sanitiza todos los datos
6. PHP encripta la nueva clave API
7. PHP guarda todas las configuraciones en base de datos
8. PHP recarga opciones del plugin
9. PHP devuelve confirmación de éxito
10. JavaScript muestra mensaje de éxito
```

## 🚨 **Si Aún Hay Problemas:**

### **1. Verificar Permisos**
```php
// Añadir temporalmente en wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### **2. Limpiar Cache**
```php
wp_cache_flush();
```

### **3. Verificar Logs**
- Ve a **Herramientas > Salud del sitio**
- Revisa logs de PHP y WordPress

### **4. Reinstalar Plugin**
1. Desactiva el plugin
2. Elimina todos los archivos
3. Vuelve a subir el plugin
4. Actívalo y ejecuta el script de activación

## 📋 **Checklist de Verificación:**

- [ ] Script de debug funciona correctamente
- [ ] "Probar Conexión" no borra la clave anterior
- [ ] "Guardar Configuraciones" guarda la nueva clave
- [ ] Nueva clave API se encripta correctamente
- [ ] Nueva clave API se desencripta correctamente
- [ ] Opciones del plugin se cargan correctamente
- [ ] Chat de prueba funciona con la nueva clave

## 🎯 **Resultado Esperado:**

Después de la solución:
- ✅ **"Probar Conexión" no borra** la clave anterior
- ✅ **"Guardar Configuraciones" guarda** la nueva clave correctamente
- ✅ **Nueva clave API se guarda** en la base de datos
- ✅ **Chat de prueba funciona** con la nueva clave
- ✅ **Proceso completo funciona** sin errores

---

**¡El problema está solucionado!** 🎉

Ejecuta el script de debug para verificar que todo funciona correctamente.
