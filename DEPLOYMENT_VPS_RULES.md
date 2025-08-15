# 🚀 REGLAS DE DEPLOYMENT AL VPS

## 📡 **DATOS DE CONEXIÓN VPS**
- **Host:** `162.240.163.188`
- **Puerto SSH:** `22022`
- **Usuario:** `root`
- **Dominio:** `linkiu.bio`
- **Ruta App:** `/home/wwlink/linkiubio_app/`
- **Ruta Web:** `/home/wwlink/public_html/`

## 📂 **RUTAS CRÍTICAS**
```bash
# Aplicación principal
/home/wwlink/linkiubio_app/

# Web pública (donde apunta el dominio)
/home/wwlink/public_html/

# Storage enlazado
/home/wwlink/public_html/storage -> /home/wwlink/linkiubio_app/storage/app/public
```

## 🔧 **COMANDOS ESTÁNDAR DE DEPLOYMENT**

### **1. 📤 SUBIR ARCHIVOS (SCP)**
```bash
# Archivo único
scp -P 22022 "ruta/local/archivo.php" root@162.240.163.188:/home/wwlink/linkiubio_app/ruta/destino/

# Carpeta completa
scp -P 22022 -r "carpeta/local/" root@162.240.163.188:/home/wwlink/linkiubio_app/ruta/destino/

# Ejemplo Controllers
scp -P 22022 "app/Features/TenantAdmin/Controllers/OrderController.php" root@162.240.163.188:/home/wwlink/linkiubio_app/app/Features/TenantAdmin/Controllers/

# Ejemplo Views
scp -P 22022 "app/Features/TenantAdmin/Views/orders/show.blade.php" root@162.240.163.188:/home/wwlink/linkiubio_app/app/Features/TenantAdmin/Views/orders/

# Assets (build)
scp -P 22022 -r "public/build/" root@162.240.163.188:/home/wwlink/linkiubio_app/public/
scp -P 22022 -r "public/build/" root@162.240.163.188:/home/wwlink/public_html/
```

### **2. 🧹 LIMPIAR CACHÉ (OBLIGATORIO DESPUÉS DE CAMBIOS)**
```bash
ssh -p 22022 root@162.240.163.188 "cd /home/wwlink/linkiubio_app && php artisan config:clear && php artisan view:clear && php artisan cache:clear && php artisan route:clear && echo '✅ Cache cleared'"
```

### **3. 🛠️ BUILD ASSETS (CUANDO HAY CAMBIOS EN JS/CSS)**
```bash
# En local primero
npm run build

# Subir assets compilados
scp -P 22022 -r "public/build/" root@162.240.163.188:/home/wwlink/linkiubio_app/public/
scp -P 22022 -r "public/build/" root@162.240.163.188:/home/wwlink/public_html/
```

### **4. 📊 VERIFICAR LOGS (PARA DEBUGGEAR)**
```bash
ssh -p 22022 root@162.240.163.188 "cd /home/wwlink/linkiubio_app && tail -50 storage/logs/laravel.log"

# Buscar errores específicos
ssh -p 22022 root@162.240.163.188 "cd /home/wwlink/linkiubio_app && tail -100 storage/logs/laravel.log | grep -B 10 -A 5 'Exception\|Error\|CRITICAL'"
```

## 📁 **RUTAS DE ARCHIVOS POR FEATURE**

### **🏢 SuperLinkiu (Panel Superadmin)**
```bash
# Controllers
app/Features/SuperLinkiu/Controllers/
# Views
app/Features/SuperLinkiu/Views/
# Routes
app/Features/SuperLinkiu/Routes/web.php
```

### **🏪 TenantAdmin (Panel Admin Tienda)**
```bash
# Controllers
app/Features/TenantAdmin/Controllers/
# Views
app/Features/TenantAdmin/Views/
# Routes
app/Features/TenantAdmin/Routes/web.php
# Models
app/Features/TenantAdmin/Models/
```

### **🛒 Tenant (Frontend Tienda)**
```bash
# Controllers
app/Features/Tenant/Controllers/
# Views
app/Features/Tenant/Views/
# Routes
app/Features/Tenant/Routes/web.php
```

### **🌐 Web (Landing)**
```bash
# Controllers
app/Features/Web/Controllers/
# Views
app/Features/Web/Views/
# Routes
app/Features/Web/Routes/
```

### **🔗 Shared (Compartido)**
```bash
# Models
app/Shared/Models/
# Views compartidas
app/Shared/Views/
# Middleware
app/Shared/Middleware/
```

## 📋 **CHECKLIST ANTES DE DEPLOY**

### **✅ Pre-Deploy Local**
- [ ] Verificar que todo funciona en local
- [ ] Ejecutar `npm run build` si hay cambios en JS/CSS
- [ ] Probar las rutas afectadas
- [ ] Verificar que no hay errores de sintaxis

### **✅ Deploy Process**
- [ ] Subir archivos con `scp`
- [ ] Limpiar caché con `php artisan`
- [ ] Verificar logs para errores
- [ ] Probar funcionalidad en producción

### **✅ Post-Deploy**
- [ ] Verificar que las rutas funcionan
- [ ] Probar funcionalidades críticas
- [ ] Revisar logs de errores
- [ ] Confirmar que assets cargan correctamente

## 🚨 **COMANDOS DE EMERGENCIA**

### **🔄 Rollback rápido**
```bash
# Restaurar archivo desde backup
ssh -p 22022 root@162.240.163.188 "cd /home/wwlink/linkiubio_app && cp backup/archivo.php ruta/actual/"

# Ver versiones anteriores con git
ssh -p 22022 root@162.240.163.188 "cd /home/wwlink/linkiubio_app && git log --oneline -10"
```

### **🩺 Diagnóstico**
```bash
# Verificar permisos
ssh -p 22022 root@162.240.163.188 "ls -la /home/wwlink/linkiubio_app/storage/"

# Verificar enlace simbólico
ssh -p 22022 root@162.240.163.188 "ls -la /home/wwlink/public_html/storage"

# Verificar sintaxis PHP
ssh -p 22022 root@162.240.163.188 "cd /home/wwlink/linkiubio_app && php -l app/ruta/archivo.php"
```

## 📌 **NOTAS IMPORTANTES**

1. **🔗 Dual DocumentRoot:** El dominio apunta a `/home/wwlink/public_html/` pero la app está en `/home/wwlink/linkiubio_app/`
2. **📂 Assets duplicados:** Build va a ambas carpetas public
3. **🗄️ Storage:** Enlace simbólico desde public_html a linkiubio_app
4. **⚡ Caché:** SIEMPRE limpiar después de cambios
5. **🔍 Debug:** Logs están en `/home/wwlink/linkiubio_app/storage/logs/`

## 🎯 **TEMPLATE COMANDO COMPLETO**
```bash
# 1. Subir archivo
scp -P 22022 "archivo.php" root@162.240.163.188:/home/wwlink/linkiubio_app/ruta/

# 2. Limpiar caché
ssh -p 22022 root@162.240.163.188 "cd /home/wwlink/linkiubio_app && php artisan config:clear && php artisan cache:clear && echo '✅ Deployed'"
```
