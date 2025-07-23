# Proceso de Implementación y Despliegue

Este documento detalla todo el proceso seguido para configurar, implementar y desplegar la aplicación Linkiu Bio.

## Índice
1. [Configuración Inicial](#configuración-inicial)
2. [Conexión con GitHub](#conexión-con-github)
3. [Configuración de Laravel Cloud](#configuración-de-laravel-cloud)
4. [Gestión de Base de Datos](#gestión-de-base-datos)
5. [Implementación de Roles de Usuario](#implementación-de-roles-de-usuario)
6. [Configuración de Almacenamiento](#configuración-de-almacenamiento)
7. [Comandos Personalizados](#comandos-personalizados)
8. [Flujo de Trabajo de Desarrollo](#flujo-de-trabajo-de-desarrollo)
9. [Solución de Problemas Comunes](#solución-de-problemas-comunes)

## Configuración Inicial

### Estructura del Proyecto
El proyecto sigue una arquitectura basada en características (Feature-based Architecture), con la siguiente estructura:
- `app/Features/`: Contiene las características organizadas por módulos
- `app/Shared/`: Contiene componentes compartidos entre características
- `resources/`: Contiene vistas, assets y otros recursos

### Archivos de Configuración
- `.env`: Configuración local (no se sube a GitHub)
- `.env.example`: Plantilla de configuración (se sube a GitHub)
- `.laravel-cloud.yml`: Configuración para Laravel Cloud

## Conexión con GitHub

### Configuración de Git
```bash
git init
git add .
git commit -m "Commit inicial"
git remote add origin git@github.com:linkiuapp/linkiubio.git
git push -u origin main
```

### Flujo de Trabajo con Git
1. Desarrollo local en ramas de características
2. Fusión con la rama principal
3. Despliegue automático o manual en Laravel Cloud

## Configuración de Laravel Cloud

### Creación del Proyecto
1. Acceder a [Laravel Cloud](https://cloud.laravel.com/)
2. Crear nuevo proyecto y conectarlo al repositorio de GitHub
3. Configurar región: US East (Ohio)

### Configuración de Recursos
1. **App**: Configurada con 1vCPU, modo Web
2. **Base de Datos**: MySQL 8, configuración Dev (1vCPU, 1GB RAM)
3. **Bucket**: Para almacenamiento de archivos

### Archivo de Configuración
```yaml
id: linkiubio
name: Linkiu Bio
environments:
  production:
    build:
      - 'composer install --no-dev --optimize-autoloader'
      - 'npm ci'
      - 'npm run build'
      - 'php artisan config:cache'
      - 'php artisan route:cache'
      - 'php artisan view:cache'
    deploy:
      - 'php artisan migrate --force'
      - 'php artisan storage:link'
    variables:
      - APP_ENV=production
      - APP_DEBUG=false
      - FILESYSTEM_DISK=s3
```

## Gestión de Base de Datos

### Configuración Local
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=linkiudb_new
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

### Migraciones
Se crearon migraciones para:
- Usuarios y autenticación
- Tiendas y configuraciones
- Productos y categorías
- Planes y suscripciones
- Tickets y soporte

### Solución de Problemas
- Se modificaron migraciones para verificar si las columnas existen antes de crearlas
- Se cambió el motor de base de datos a MyISAM para evitar problemas con tablespaces

## Implementación de Roles de Usuario

### Estructura de Roles
- `super_admin`: Administrador de la plataforma
- `admin`: Administrador de tienda
- `user`: Usuario regular

### Migraciones para Roles
```php
Schema::table('users', function (Blueprint $table) {
    if (!Schema::hasColumn('users', 'role')) {
        $table->string('role')->default('user')->after('password');
    }
});
```

### Métodos de Verificación
```php
public function isSuperAdmin(): bool
{
    return $this->role === 'super_admin';
}

public function isAdmin(): bool
{
    return $this->role === 'admin' || $this->role === 'super_admin';
}
```

## Configuración de Almacenamiento

### Almacenamiento Local
- Ubicación: `storage/app/public/`
- Acceso: `public/storage/` (mediante enlace simbólico)
- Comando para crear enlace: `php artisan storage:link`

### Almacenamiento en la Nube (Cloudflare R2)
```
AWS_ACCESS_KEY_ID=366e75ca6bd8aa32a217ec4a29429f3b
AWS_SECRET_ACCESS_KEY=8141539779a06ecacb7430c144585f7922179aae8571c59e02ceaa5aba41420d
AWS_DEFAULT_REGION=auto
AWS_BUCKET=fls-9f760c61-af2d-4e14-b2b0-f6ec229eec2f
AWS_ENDPOINT=https://367be3a2035528943240074d0096e0cd.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=false
FILESYSTEM_DISK=s3
```

### Migración de Imágenes
Se creó un comando para migrar imágenes del almacenamiento local al almacenamiento en la nube:
```bash
php artisan app:migrate-images-to-cloud
```

## Comandos Personalizados

### Crear Super Admin
```php
php artisan app:create-super-admin [email] [password]
```
Este comando crea un usuario con rol de super_admin.

### Migrar Imágenes a la Nube
```php
php artisan app:migrate-images-to-cloud
```
Este comando migra todas las imágenes del almacenamiento local al almacenamiento en la nube.

## Flujo de Trabajo de Desarrollo

### Entorno Local
1. Desarrollo y pruebas en localhost
2. Uso de base de datos local
3. Almacenamiento local o en la nube

### Despliegue
1. Commit y push a GitHub
2. Despliegue automático o manual en Laravel Cloud
3. Ejecución de migraciones y comandos necesarios

### Mantenimiento
1. Monitoreo de logs y errores
2. Actualización de dependencias
3. Backups de base de datos

## Solución de Problemas Comunes

### Problemas de Base de Datos
- **Error de tablespace**: Usar MyISAM en lugar de InnoDB
- **Columnas duplicadas**: Verificar si las columnas existen antes de crearlas
- **Migraciones fallidas**: Ejecutar migraciones manualmente o modificarlas

### Problemas de Almacenamiento
- **Imágenes no visibles**: Verificar configuración de almacenamiento y ejecutar migración de imágenes
- **Errores de permisos**: Verificar permisos en el bucket de almacenamiento
- **Enlaces simbólicos**: Asegurarse de que `storage:link` se ejecute en el despliegue

### Problemas de Autenticación
- **Errores de inicio de sesión**: Verificar roles y permisos
- **Usuarios no encontrados**: Crear usuarios manualmente con el comando personalizado