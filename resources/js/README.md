# JavaScript Architecture - Linkiu.bio

## Estructura de Archivos

```
resources/js/
├── app.js          # Archivo principal (Alpine.js + imports)
├── components.js   # Componentes UI (Image Upload, Forms, etc.)
├── navbar.js       # Funcionalidad de navegación
├── sidebar.js      # Funcionalidad de sidebar
└── README.md       # Esta documentación
```

## Archivo Principal (app.js)

Este archivo es el punto de entrada donde se configura Alpine.js y se importan todos los módulos:

```javascript
import Alpine from 'alpinejs'
import collapse from '@alpinejs/collapse'

// Importar archivos de componentes y funcionalidades
import './components.js'
import './navbar.js'
import './sidebar.js'

Alpine.plugin(collapse)
window.Alpine = Alpine
Alpine.start()
```

## Archivo de Componentes (components.js)

Contiene todas las funcionalidades de los componentes UI. Estructura:

### Clases Principales:
- `ImageUploadManager`: Maneja todas las funcionalidades de subida de imágenes
- Futuras clases para otros componentes

### Utilidades Globales:
- `formatFileSize()`: Formatea tamaños de archivo
- `createSVGIcon()`: Crea iconos SVG dinámicamente

### Inicialización Automática:
```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Detección automática y inicialización de componentes
    if (document.querySelector('[id*="upload-file"]')) {
        new ImageUploadManager();
    }
});
```

## Cómo Agregar Nuevos Componentes

### 1. Crear la Clase del Componente

```javascript
/**
 * Nuevo Componente
 */
class NuevoComponenteManager {
    constructor() {
        this.init();
    }

    init() {
        this.initFuncionalidad1();
        this.initFuncionalidad2();
    }

    initFuncionalidad1() {
        // Lógica específica
    }

    initFuncionalidad2() {
        // Lógica específica
    }
}
```

### 2. Agregar Inicialización Automática

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Componentes existentes...
    
    // Nuevo componente
    if (document.querySelector('.nuevo-componente')) {
        new NuevoComponenteManager();
    }
});
```

### 3. Exportar para Uso Global

```javascript
// Exportar para uso global
window.NuevoComponenteManager = NuevoComponenteManager;
```

## Mejores Prácticas

### 1. Detección de Elementos
Siempre verifica que los elementos existan antes de agregar event listeners:

```javascript
const elemento = document.getElementById("mi-elemento");
if (!elemento) return; // Salir si no existe
```

### 2. Limpieza de Memoria
Para elementos creados dinámicamente, limpia los Object URLs:

```javascript
// Crear
const src = URL.createObjectURL(file);

// Limpiar cuando se elimine
URL.revokeObjectURL(src);
```

### 3. Manejo de Errores
Agrega validaciones para tipos de archivo y tamaños:

```javascript
Array.from(files).forEach(file => {
    if (!file.type.startsWith('image/')) return;
    if (file.size > 5 * 1024 * 1024) return; // 5MB max
    
    // Procesar archivo...
});
```

### 4. Clases CSS Consistentes
Usa siempre las clases del sistema de diseño:

```javascript
element.classList.add("bg-primary-50", "text-primary-400", "body-base");
```

## Ventajas de esta Estructura

1. **Separación de Responsabilidades**: Cada archivo tiene un propósito específico
2. **Reutilización**: Los componentes pueden ser reutilizados en diferentes páginas
3. **Mantenibilidad**: Es fácil encontrar y modificar código específico
4. **Escalabilidad**: Fácil agregar nuevos componentes sin afectar los existentes
5. **Rendimiento**: Compilación optimizada con Vite

## Compilación

Después de hacer cambios en cualquier archivo JavaScript:

```bash
# Desarrollo (watch mode)
npm run dev

# Producción
npm run build
```

Los archivos compilados se generan en `public/build/` y se referencian automáticamente en las vistas Blade.

## Ejemplo de Uso en Blade

```blade
{{-- No necesitas incluir scripts inline --}}
{{-- El JavaScript se maneja automáticamente en resources/js/components.js --}}

<div class="image-upload-container">
    <input type="file" id="upload-file" accept="image/*">
    <div class="uploaded-img hidden">
        <img id="uploaded-img__preview" src="#" alt="Preview">
        <button class="uploaded-img__remove">Remove</button>
    </div>
</div>
```

La detección e inicialización es automática basada en los IDs y clases de los elementos. 