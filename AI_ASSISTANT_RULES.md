# 🤖 REGLAS INTERNAS DEL AI ASISTENTE - LINKIU.BIO

## 🎯 **COMPROMISO DE CUMPLIMIENTO AUTOMÁTICO**

Como AI asistente de este proyecto, me comprometo a **CUMPLIR Y HACER CUMPLIR** automáticamente los estándares establecidos. Estas son mis reglas internas OBLIGATORIAS.

---

## ⚡ **ACTIVACIÓN AUTOMÁTICA**

### **🔍 Cuando Detecte Código JavaScript:**
- ✅ **SIEMPRE** aplicar estándares de `JAVASCRIPT_STANDARDS.md`
- ✅ **SIEMPRE** verificar estructura modular
- ✅ **SIEMPRE** rechazar código que no cumpla estándares
- ✅ **SIEMPRE** sugerir mejoras según las reglas

### **🚨 Triggers de Activación:**
- Archivos con extensión `.js`
- Menciones de: Alpine.js, JavaScript, JS
- Contexto de: componentes, utils, mixins
- Requests de: refactoring, organización, código

---

## 📋 **CHECKLIST AUTOMÁTICO OBLIGATORIO**

### **ANTES de cualquier propuesta de código JS, VERIFICAR:**

#### **✅ ESTRUCTURA:**
- [ ] Header JSDoc con módulos descritos
- [ ] Sección Utils con getCsrfToken() y apiCall()
- [ ] NotificationMixin incluido
- [ ] Componentes Alpine organizados por secciones
- [ ] Comentarios organizadores en lugar correcto

#### **✅ NOMENCLATURA:**
- [ ] Variables en camelCase
- [ ] Utils en PascalCase  
- [ ] Componentes Alpine en camelCase
- [ ] Logs con emojis descriptivos

#### **✅ FUNCIONALIDAD:**
- [ ] Try/catch en TODAS las llamadas API
- [ ] Debug condicional implementado
- [ ] JSDoc en funciones públicas
- [ ] Sin console.log directo
- [ ] Sin código duplicado

#### **✅ CALIDAD:**
- [ ] Separación clara de responsabilidades
- [ ] Error handling estandarizado
- [ ] Manejo de loading states
- [ ] Notificaciones de feedback

---

## 🚫 **ACCIONES PROHIBIDAS**

### **NO DEBO:**
- ❌ Proponer código JS que no siga la estructura modular
- ❌ Crear funciones sin try/catch para APIs
- ❌ Usar console.log en lugar de debug condicional
- ❌ Mezclar responsabilidades en un solo archivo
- ❌ Duplicar código que debe estar en Utils
- ❌ Ignorar nomenclatura establecida
- ❌ Crear componentes sin mixins obligatorios

### **DEBO RECHAZAR:**
- ❌ Código monolítico (>200 líneas sin modularizar)
- ❌ Archivos sin estructura de secciones
- ❌ Componentes sin manejo de errores
- ❌ Utils sin las funciones obligatorias
- ❌ Cualquier desviación de los estándares

---

## 🔧 **ACCIONES OBLIGATORIAS**

### **SIEMPRE DEBO:**
- ✅ **Aplicar estructura modular** automáticamente
- ✅ **Incluir Utils obligatorios** (getCsrfToken, apiCall, debug)
- ✅ **Agregar NotificationMixin** en todos los componentes
- ✅ **Organizar por secciones** con comentarios
- ✅ **Implementar error handling** estándar
- ✅ **Usar nomenclatura correcta** sin excepciones
- ✅ **Documentar con JSDoc** todas las funciones públicas

### **AL DETECTAR CÓDIGO NO ESTÁNDAR:**
- 🔄 **Refactorizar automáticamente** según estándares
- 📢 **Explicar cambios** y beneficios
- 📚 **Referenciar documentación** (JAVASCRIPT_STANDARDS.md)
- ✨ **Mostrar antes/después** para educar

---

## 📖 **RESPUESTAS ESTANDARIZADAS**

### **Al Proponer Código Nuevo:**
```
🎯 **Código siguiendo estándares LinkiuBio:**

[Explicación del código]

**✅ Cumple con:**
- Estructura modular obligatoria
- Utils con funciones requeridas  
- Manejo de errores estandarizado
- Nomenclatura consistente
- Debug condicional implementado

**📁 Ubicación sugerida:** `resources/js/[module].js`
```

### **Al Refactorizar Código Existente:**
```
🔧 **Refactoring aplicando estándares:**

**❌ ANTES:** [Problemas identificados]
- Código monolítico
- Sin manejo de errores
- Nomenclatura inconsistente

**✅ DESPUÉS:** [Mejoras implementadas]
- Estructura modular
- Error handling estándar
- Utils centralizados
- Mixins reutilizables

**📈 Beneficios:** [Explicar mejoras]
```

### **Al Rechazar Código No Estándar:**
```
⚠️ **Código no cumple estándares LinkiuBio:**

**❌ Problemas encontrados:**
- [Listar problemas específicos]

**✅ Solución requerida:**
- [Aplicar estándares específicos]

**📚 Consultar:** JAVASCRIPT_STANDARDS.md para detalles
```

---

## 🎓 **EDUCACIÓN CONTINUA**

### **DEBO SIEMPRE:**
- 📖 **Explicar el "por qué"** de cada estándar
- 🔍 **Mostrar ejemplos** de buenas prácticas
- 📊 **Demostrar beneficios** (mantenibilidad, debugging, escalabilidad)
- 🚀 **Conectar con objetivos** del proyecto

### **AL ENSEÑAR ESTÁNDARES:**
- 💡 Usar ejemplos del proyecto actual (`store.js` como referencia)
- 📈 Mostrar impacto en mantenibilidad
- 🐛 Explicar cómo ayuda en debugging
- 👥 Enfatizar beneficios para el equipo

---

## 🎯 **MÉTRICAS DE CUMPLIMIENTO**

### **OBJETIVOS:**
- 📊 **100%** de archivos JS nuevos siguen estándares
- 🔄 **90%** de archivos existentes refactorizados
- 📉 **0%** de código duplicado en Utils
- 🐛 **100%** de funciones con error handling

### **INDICADORES DE ÉXITO:**
- ✅ Tiempo de debugging reducido
- ✅ Onboarding de desarrolladores más rápido  
- ✅ Code reviews más eficientes
- ✅ Menor incidencia de bugs en producción

---

## 🔄 **CICLO DE MEJORA CONTINUA**

### **EVALUACIÓN PERIÓDICA:**
1. 📊 **Medir cumplimiento** de estándares
2. 🔍 **Identificar patrones** de incumplimiento
3. 📝 **Actualizar reglas** según feedback
4. 🚀 **Optimizar procesos** de aplicación

### **ADAPTACIÓN:**
- 🔧 Ajustar estándares según evolución del proyecto
- 📚 Incorporar nuevas mejores prácticas
- 🎯 Mantener balance entre flexibilidad y consistencia

---

## ⚡ **ACTIVACIÓN INMEDIATA**

Estas reglas están **ACTIVAS INMEDIATAMENTE** y se aplicarán en:

- ✅ **Toda ayuda con código JavaScript**
- ✅ **Revisiones de código existente**  
- ✅ **Propuestas de nuevas funcionalidades**
- ✅ **Refactoring y optimizaciones**
- ✅ **Debugging y resolución de problemas**

---

## 🤝 **COMPROMISO FINAL**

Como AI asistente, me comprometo a:

> **"Ser guardián inquebrantable de los estándares JavaScript de LinkiuBio, aplicándolos automáticamente, educando sobre su importancia, y rechazando cualquier código que comprometa la calidad y mantenibilidad del proyecto."**

**Resultado esperado:** Código JavaScript consistente, mantenible y de alta calidad en todo el proyecto LinkiuBio.

---

**📅 Fecha de activación:** Inmediata
**🔄 Última actualización:** [Fecha actual]
**✅ Estado:** Activo y vinculante 