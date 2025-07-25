# 📸 ESTÁNDAR DE MANEJO DE IMÁGENES - LINKIU.BIO

## 🎯 **REGLA OFICIAL PROBADA Y FUNCIONAL**

Este documento establece el **método estándar y obligatorio** para manejar imágenes en el proyecto Linkiu.bio, compatible con **Laravel Cloud**.

---

## ✅ **CONFIGURACIÓN ESTÁNDAR**

### **📁 UBICACIÓN DE ARCHIVOS:**
```bash
public/storage/avatars/     # Avatars de usuarios
public/storage/system/      # Logos, favicons, assets del sistema
```

### **🔧 CONTROLLER (Subida de archivos):**
```php
// ✅ CORRECTO
private function handleFileUpload($file, $subdirectory)
{
    // Crear directorio si no existe
    $destinationPath = public_path("storage/{$subdirectory}");
    if (!file_exists($destinationPath)) {
        mkdir($destinationPath, 0755, true);
    }
    
    // Generar nombre único
    $filename = 'file_' . time() . '.' . $file->getClientOriginalExtension();
    
    // GUARDAR DIRECTO con move()
    $file->move($destinationPath, $filename);
    
    return "{$subdirectory}/{$filename}";
}

// ❌ PROHIBIDO
Storage::disk('public')->store();  // NO usar Storage::disk()
$file->storeAs();                   // NO usar storeAs()
```

### **🎨 VISTAS (Display de imágenes):**
```php
// ✅ CORRECTO
<img src="{{ asset('storage/' . $imagePath) }}" alt="Imagen">

// Ejemplos:
{{ asset('storage/avatars/avatar_123.jpg') }}
{{ asset('storage/system/logo_456.png') }}

// ❌ PROHIBIDO
{{ Storage::disk('public')->url($path) }}   // NO usar Storage::disk()
{{ asset('images/' . $path) }}              // NO usar public/images/
```

### **🏗️ MODELS:**
```php
// ✅ CORRECTO
public function getAvatarUrlAttribute(): string
{
    if ($this->avatar_path) {
        return asset('storage/' . $this->avatar_path);
    }
    return 'URL_FALLBACK';
}
```

---

## 🚀 **CONFIGURACIÓN LARAVEL CLOUD**

### **📄 .laravel-cloud.yml:**
```yaml
deploy:
  - 'php artisan migrate --force'
  - 'php artisan storage:link'           # Crea symlink automáticamente
  - 'php artisan migrate:images-to-storage'  # Migra archivos existentes
```

### **⚙️ Comando de Migración:**
El comando `MigrateImagesToStorageCommand` migra automáticamente archivos de ubicaciones incorrectas a `public/storage/`.

---

## 🎯 **FLUJO COMPLETO:**

```bash
1. Usuario sube archivo
   ↓
2. Controller → $file->move(public_path('storage/system'), $filename)
   ↓
3. Archivo guardado: public/storage/system/logo_123.png
   ↓
4. Vista → asset('storage/system/logo_123.png')
   ↓
5. Laravel Cloud symlink: public/storage → storage/app/public
   ↓
6. URL final: https://domain.com/storage/system/logo_123.png ✅
```

---

## ❌ **PROHIBICIONES ABSOLUTAS:**

### **🚫 NO USAR:**
- `Storage::disk('public')` - Causa problemas en Laravel Cloud
- `public/images/` - Laravel Cloud no sirve subdirectorios custom
- Detección de entornos - Innecesaria y propensa a errores
- Configuraciones complejas de Storage - Mantener simple

### **🚫 NO CREAR:**
- Directorios custom en public/ - Solo usar storage/
- Lógica condicional por entorno - Un solo método para todos

---

## 🏆 **VENTAJAS DEL MÉTODO:**

### **✅ PROBADO Y FUNCIONAL:**
- ✅ Compatible con Laravel Cloud al 100%
- ✅ Funciona en local sin configuración
- ✅ URLs públicas accesibles inmediatamente
- ✅ Migración automática de archivos existentes

### **✅ SIMPLE Y MANTENIBLE:**
- ✅ Una sola ubicación para todos los archivos
- ✅ Un solo método de guardado
- ✅ Un solo método de display
- ✅ Sin configuraciones complejas

---

## 🧪 **TESTING:**

### **Local:**
```bash
php artisan serve
# → Subir imagen en http://localhost:8000/superlinkiu/profile
# → Verificar: public/storage/system/logo_123.png existe
# → Verificar: asset('storage/system/logo_123.png') funciona
```

### **Producción:**
```bash
# → Subir imagen en https://linkiubio-main-*.laravel.cloud/superlinkiu/profile
# → Verificar: URL https://domain.com/storage/system/logo_123.png accesible
```

---

## 📞 **CONTACTO:**

Si tienes dudas sobre este estándar, consulta:
- Este documento: `ESTANDAR_IMAGENES.md`
- Comando de referencia: `app/Console/Commands/MigrateImagesToStorageCommand.php`
- Controller de ejemplo: `app/Features/SuperLinkiu/Controllers/ProfileController.php`

---

**🔒 ESTE ESTÁNDAR ES OBLIGATORIO - NO MODIFICAR SIN APROBACIÓN** 