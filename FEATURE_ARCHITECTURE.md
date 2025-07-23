# 🏗️ Feature-based Architecture - Linkiu.bio

## 📋 Estructura General

El proyecto ha sido reorganizado siguiendo una **Feature-based Architecture** que separa las funcionalidades en módulos independientes según la arquitectura establecida.

## 🌐 Arquitectura de URLs

```
linkiu.bio                    = Web principal
linkiu.bio/super-linkiu      = Panel super administrador  
linkiu.bio/{tienda}          = Frontend tienda
linkiu.bio/{tienda}/admin    = Panel admin tienda
```

## 📁 Estructura de Carpetas

```
app/
├── Features/
│   ├── Web/                 # linkiu.bio - Landing page
│   │   ├── Controllers/
│   │   ├── Views/
│   │   ├── Routes/
│   │   └── Assets/
│   ├── SuperLinkiu/         # linkiu.bio/super-linkiu - Panel super admin
│   │   ├── Controllers/
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Views/
│   │   ├── Routes/
│   │   └── Assets/
│   ├── Tenant/              # linkiu.bio/{tienda} - Frontend tienda
│   │   ├── Controllers/
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Views/
│   │   ├── Routes/
│   │   └── Assets/
│   └── TenantAdmin/         # linkiu.bio/{tienda}/admin - Panel admin tienda
│       ├── Controllers/
│       ├── Models/
│       ├── Services/
│       ├── Views/
│       ├── Routes/
│       └── Assets/
├── Shared/                  # Componentes compartidos
│   ├── Models/
│   ├── Services/
│   ├── Traits/
│   ├── Scopes/
│   └── Middleware/
└── Core/                    # Núcleo del sistema
    ├── Providers/
    ├── Config/
    └── Helpers/
```

## 🚀 Archivos Migrados

### ✅ SuperLinkiu Feature
- **Controllers**: `AuthController`, `StoreController`
- **Views**: Todas las vistas de `superlinkiu/`
- **Routes**: Archivo dedicado `SuperLinkiu/Routes/web.php`
- **Service Provider**: `SuperLinkiuServiceProvider`

### ✅ Shared Components
- **Models**: `User`, `Store`, `Plan`, `StorePlanExtension`
- **Middleware**: `SuperAdminMiddleware`, `StoreAdminMiddleware`

### ✅ Core Components
- **Providers**: `AppServiceProvider`, `ComponentsServiceProvider`

## 📦 Namespaces Actualizados

```php
// Controllers
App\Features\SuperLinkiu\Controllers\AuthController
App\Features\SuperLinkiu\Controllers\StoreController

// Models
App\Shared\Models\User
App\Shared\Models\Store
App\Shared\Models\Plan
App\Shared\Models\StorePlanExtension

// Middleware
App\Shared\Middleware\SuperAdminMiddleware
App\Shared\Middleware\StoreAdminMiddleware

// Providers
App\Core\Providers\AppServiceProvider
App\Core\Providers\ComponentsServiceProvider
```

## 🔧 Service Providers

Cada feature tiene su propio Service Provider que maneja:
- Carga automática de rutas
- Registro de vistas
- Configuración de componentes

### SuperLinkiuServiceProvider
```php
// Carga automática de rutas
$this->loadRoutesFrom(__DIR__ . '/Routes/web.php');

// Registro de vistas con namespace
$this->loadViewsFrom(__DIR__ . '/Views', 'superlinkiu');
```

## 📝 Archivos de Configuración Actualizados

### bootstrap/providers.php
```php
return [
    App\Core\Providers\AppServiceProvider::class,
    App\Core\Providers\ComponentsServiceProvider::class,
    App\Features\SuperLinkiu\SuperLinkiuServiceProvider::class,
];
```

### config/auth.php
```php
'model' => App\Shared\Models\User::class,
```

### app/Http/Kernel.php
```php
'super.admin' => App\Shared\Middleware\SuperAdminMiddleware::class,
'store.admin' => App\Shared\Middleware\StoreAdminMiddleware::class,
```

## 🎯 Ventajas de esta Arquitectura

### ✅ **Modularidad**
- Cada feature es independiente
- Fácil mantenimiento y escalabilidad
- Separación clara de responsabilidades

### ✅ **Organización**
- Código agrupado por contexto de negocio
- Estructura predecible y navegable
- Componentes compartidos centralizados

### ✅ **Escalabilidad**
- Fácil agregar nuevas features
- Posible migración futura a microservicios
- Equipos pueden trabajar independientemente

### ✅ **Mantenibilidad**
- Cambios localizados por feature
- Menos conflictos entre desarrolladores
- Testing más enfocado y específico

## 🔄 Próximos Pasos

1. **Implementar Feature Web** para la landing page
2. **Crear Feature Tenant** para el frontend de tiendas
3. **Desarrollar Feature TenantAdmin** para el panel de administración
4. **Añadir services específicos** por feature
5. **Implementar tests** organizados por feature

## 📚 Convenciones

- **Namespaces**: Seguir la estructura de carpetas
- **Rutas**: Cada feature maneja sus propias rutas
- **Vistas**: Usar namespace de feature (`superlinkiu::dashboard`)
- **Servicios**: Crear services específicos por feature
- **Modelos**: Modelos compartidos en `Shared/Models`
- **Middleware**: Middleware compartido en `Shared/Middleware` 