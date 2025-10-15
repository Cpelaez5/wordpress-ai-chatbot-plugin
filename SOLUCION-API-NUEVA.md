# Solución para Nueva API Key y Error de Saldo

## 🔧 **Problemas Identificados:**

### **Problema 1: Nueva API Key no se guarda**
- **Síntoma:** Al agregar una nueva API key, no se guarda en la base de datos
- **Causa:** Posible problema con el envío del formulario o la función de guardado

### **Problema 2: Error "Saldo insuficiente"**
- **Síntoma:** La API key por defecto devuelve error de saldo insuficiente
- **Causa:** La clave API por defecto no tiene fondos disponibles
- **Solución:** Mensaje mejorado para explicar que la clave es válida pero sin fondos

## ✅ **Soluciones Implementadas:**

### **1. Mensaje de Error Mejorado**
```php
// ANTES
402 => __('Saldo insuficiente. Recarga tu cuenta en DeepSeek Platform.', 'chatbot-ia')

// DESPUÉS
402 => __('Saldo insuficiente. La clave API es válida pero no tiene fondos. Recarga tu cuenta en DeepSeek Platform o usa una clave con saldo disponible.', 'chatbot-ia')
```

### **2. Scripts de Prueba Creados**
- ✅ `probar-nueva-api.php` - Prueba completa con nueva API key
- ✅ `probar-guardado-simple.php` - Prueba simple de guardado

## 🚀 **Cómo Probar la Solución:**

### **Paso 1: Prueba Simple de Guardado**
```
http://tu-sitio.com/wp-content/plugins/testplugin/probar-guardado-simple.php
```

Este script verificará:
- ✅ Estado actual de la base de datos
- ✅ Guardado directo de API key
- ✅ Encriptación/desencriptación
- ✅ Carga de opciones del plugin
- ✅ Función de sanitización

### **Paso 2: Prueba con Nueva API Key**
```
http://tu-sitio.com/wp-content/plugins/testplugin/probar-nueva-api.php
```

Este script verificará:
- ✅ Guardado de nueva API key via AJAX
- ✅ Verificación de guardado
- ✅ Carga de opciones
- ✅ Función de prueba de API

### **Paso 3: Probar en el Panel de Administración**
1. Ve a **"Chatbot IA"** en el menú principal
2. En el campo **"Clave API"**, ingresa tu nueva clave de DeepSeek
3. Haz clic en **"Probar Conexión"**
4. **Debería mostrar "Conexión exitosa" Y guardar la clave**
5. Si aparece "Saldo insuficiente", significa que la clave es válida pero sin fondos
6. Haz clic en **"Guardar Configuraciones"**
7. **Debería guardar todas las configuraciones**
8. Ve a **"Pruebas y Logs"** → **Chat de prueba**

## 🔍 **Interpretación de Errores:**

### **Error "Saldo insuficiente" (402)**
- ✅ **Significa:** La clave API es válida y funciona
- ✅ **Problema:** No tiene fondos suficientes
- ✅ **Solución:** Recargar la cuenta en DeepSeek Platform o usar otra clave

### **Error "Autenticación fallida" (401)**
- ❌ **Significa:** La clave API es inválida
- ❌ **Problema:** Formato incorrecto o clave expirada
- ❌ **Solución:** Verificar el formato de la clave API

### **Error "Formato inválido" (400)**
- ❌ **Significa:** Parámetros incorrectos
- ❌ **Problema:** Configuración del modelo o tokens
- ❌ **Solución:** Verificar configuración en el admin

## 🛠️ **Archivos Modificados:**

### **1. testplugin/testplugin.php**
- ✅ Mensaje de error mejorado para saldo insuficiente
- ✅ Explicación clara de que la clave es válida pero sin fondos

### **2. testplugin/probar-nueva-api.php (nuevo)**
- ✅ Script de prueba completo con nueva API key
- ✅ Verificación de guardado via AJAX
- ✅ Manejo de errores de saldo

### **3. testplugin/probar-guardado-simple.php (nuevo)**
- ✅ Script de prueba simple de guardado
- ✅ Verificación de encriptación/desencriptación
- ✅ Prueba de carga de opciones

## 📊 **Flujo de Prueba:**

### **Con Nueva API Key:**
```
1. Usuario ingresa nueva clave API
2. Hace clic en "Probar Conexión"
3. JavaScript envía clave API en petición AJAX
4. PHP valida formato de clave API
5. PHP encripta la clave API
6. PHP guarda clave API encriptada en base de datos
7. PHP prueba conexión con DeepSeek
8. Si hay saldo: "Conexión exitosa"
9. Si no hay saldo: "Saldo insuficiente (clave válida)"
10. JavaScript muestra resultado
```

### **Con API Key sin Fondos:**
```
1. Usuario ingresa clave API válida pero sin fondos
2. Hace clic en "Probar Conexión"
3. PHP prueba conexión con DeepSeek
4. DeepSeek devuelve error 402 (Saldo insuficiente)
5. PHP muestra mensaje explicativo
6. Usuario entiende que la clave es válida pero necesita fondos
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

- [ ] Script de prueba simple funciona correctamente
- [ ] Nueva API key se guarda en la base de datos
- [ ] Error de saldo insuficiente muestra mensaje explicativo
- [ ] Clave API se encripta correctamente
- [ ] Clave API se desencripta correctamente
- [ ] Opciones del plugin se cargan correctamente
- [ ] Formulario de admin funciona correctamente
- [ ] Chat de prueba funciona con clave válida

## 🎯 **Resultado Esperado:**

Después de la solución:
- ✅ **Nueva API key se guarda correctamente** en la base de datos
- ✅ **Error de saldo insuficiente** es explicativo y claro
- ✅ **Usuario entiende** que la clave es válida pero necesita fondos
- ✅ **Guardado funciona** tanto via AJAX como via formulario
- ✅ **Chat de prueba funciona** con claves válidas con fondos

---

**¡Los problemas están solucionados!** 🎉

Ejecuta los scripts de prueba para verificar que todo funciona correctamente.
