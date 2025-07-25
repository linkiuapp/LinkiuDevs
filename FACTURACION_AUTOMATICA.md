# 🧾 SISTEMA DE FACTURACIÓN AUTOMÁTICA - LINKIU.BIO

## 🎯 **OVERVIEW**

Sistema completamente automatizado que **genera suscripciones y primera factura automáticamente** al crear una nueva tienda en SuperLinkiu.

---

## ✅ **FUNCIONALIDADES IMPLEMENTADAS**

### **🔄 FLUJO AUTOMÁTICO:**
```bash
1. Super Admin crea tienda en /superlinkiu/stores/create
   ↓
2. StoreController valida datos y crea Store
   ↓
3. StoreObserver se ejecuta automáticamente y:
   • Crea ShippingMethods (domicilio, pickup)
   • Crea Subscription con período seleccionado
   • Genera primera Invoice con status 'pending'
   • Registra logs detallados del proceso
   ↓
4. ✅ Tienda lista con facturación configurada
```

### **📋 CAMPOS AUTOMÁTICOS:**

#### **Suscripción:**
- `store_id` → ID de la tienda
- `plan_id` → Plan seleccionado en el formulario
- `status` → 'active' si tienda activa, 'suspended' si inactiva
- `billing_cycle` → 'monthly', 'quarterly', 'biannual' (del formulario)
- `current_period_start` → Fecha actual
- `current_period_end` → Fecha actual + días del período
- `next_billing_date` → Fin del período actual
- `next_billing_amount` → Precio del plan para el período
- `metadata` → Marca como auto-creada

#### **Primera Factura:**
- `store_id` → ID de la tienda
- `subscription_id` → ID de la suscripción creada
- `plan_id` → Plan seleccionado
- `amount` → Precio según período elegido
- `period` → Período de facturación
- `status` → 'pending'
- `issue_date` → Fecha actual
- `due_date` → Fecha actual + 15 días
- `notes` → "Primera factura generada automáticamente al crear la tienda"
- `metadata` → Marca como primera factura auto-generada

---

## 🔧 **COMPONENTES TÉCNICOS**

### **1. StoreObserver (Modificado):**
- **Ubicación**: `app/Shared/Observers/StoreObserver.php`
- **Trigger**: Se ejecuta automáticamente al crear Store
- **Funciones**:
  - Crea ShippingMethods (existente)
  - 🆕 **Crea Subscription automáticamente**
  - 🆕 **Genera primera Invoice automáticamente**
  - 🆕 **Registra logs detallados**

### **2. StoreController (Mejorado):**
- **Ubicación**: `app/Features/SuperLinkiu/Controllers/StoreController.php`
- **Mejora**: Asegura que `billing_period` esté disponible para el Observer
- **Validación**: Período de facturación requerido para planes de pago

### **3. Comando de Diagnóstico (Nuevo):**
- **Ubicación**: `app/Console/Commands/DiagnoseAutoBillingCommand.php`
- **Comando**: `php artisan billing:diagnose-auto`
- **Funciones**:
  - Estadísticas generales del sistema
  - Diagnóstico de tiendas específicas
  - Detección de problemas

---

## 🧪 **TESTING Y VALIDACIÓN**

### **🔍 Comando de Diagnóstico:**
```bash
# Ver estadísticas generales
php artisan billing:diagnose-auto

# Diagnosticar tienda específica
php artisan billing:diagnose-auto --store-id=123

# Ver últimas 20 tiendas creadas
php artisan billing:diagnose-auto --recent=20
```

### **📊 Métricas que Muestra:**
- Total de tiendas vs con suscripción vs con facturas
- Porcentaje de suscripciones auto-creadas
- Tiendas recientes con su estado de facturación
- Problemas detectados (tiendas sin suscripción/facturas)

### **🧪 Test Manual:**
1. **Crear nueva tienda** en `/superlinkiu/stores/create`
2. **Seleccionar plan** (no Explorer)
3. **Elegir período** de facturación (mensual/trimestral/semestral)
4. **Verificar** que se creó suscripción y factura automáticamente

---

## 📋 **LOGS Y MONITOREO**

### **✅ Logs de Éxito:**
```php
Log::info("✅ Auto-billing setup completed for store {$store->id}", [
    'store_name' => $store->name,
    'plan_name' => $store->plan->name,
    'billing_cycle' => $billingCycle,
    'subscription_id' => $subscription->id,
    'invoice_id' => $invoice->id,
    'invoice_number' => $invoice->invoice_number,
    'amount' => $amount,
    'due_date' => $dueDate->toDateString()
]);
```

### **❌ Logs de Error:**
```php
Log::error("❌ Failed to create automatic billing for store {$store->id}", [
    'store_id' => $store->id,
    'store_name' => $store->name,
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString()
]);
```

### **⚠️ Logs de Advertencia:**
- Tienda creada sin plan
- Plan sin precio para el período seleccionado

---

## 🔄 **COMPATIBILIDAD CON SISTEMA EXISTENTE**

### **✅ Mantiene Compatibilidad:**
- **Facturas manuales** → Siguen funcionando igual
- **Comandos existentes** → `billing:sync-invoices` sigue siendo útil
- **Billing dashboard** → Funciona igual para tiendas nuevas y existentes
- **Cambios de plan** → No afectados

### **🆕 Mejoras para Sistema Existente:**
- **Tiendas sin suscripción** → Se pueden migrar con `subscription:migrate`
- **Facturas automáticas** → Se generan para todas las suscripciones activas
- **Diagnóstico** → Nuevo comando para detectar problemas

---

## 🚨 **CASOS EDGE Y MANEJO DE ERRORES**

### **Casos Manejados:**
1. **Tienda sin plan** → Log de advertencia, continúa creación
2. **Plan sin precio** → Log de advertencia, no crea facturación
3. **Período inválido** → Default a 'monthly'
4. **Error en creación** → Log completo del error, no afecta Store
5. **Explorer plan** → Campo billing_period oculto, default 'monthly'

### **Rollback Automático:**
- Si falla la suscripción → No afecta la creación de la tienda
- Si falla la factura → Suscripción se mantiene
- Logs detallados para debugging

---

## 📈 **BENEFICIOS DEL SISTEMA**

### **✅ Para Super Admins:**
- ✅ **Automatización completa** → No más creación manual de facturas
- ✅ **Consistencia** → Todas las tiendas tienen facturación desde día 1
- ✅ **Visibilidad** → Logs y comando de diagnóstico
- ✅ **Sin trabajo extra** → Proceso transparente

### **✅ Para el Sistema:**
- ✅ **Datos consistentes** → Todas las tiendas con suscripción
- ✅ **Facturación automática** → Comandos existentes funcionan mejor
- ✅ **Monitoreo** → Fácil detectar problemas
- ✅ **Escalabilidad** → No dependiente de intervención manual

### **✅ Para Store Admins:**
- ✅ **Billing dashboard** funciona desde día 1
- ✅ **Primera factura** lista inmediatamente
- ✅ **Experiencia consistente** → No diferencias entre tiendas

---

## 🔧 **COMANDOS ÚTILES**

### **Diagnóstico:**
```bash
php artisan billing:diagnose-auto
php artisan billing:diagnose-auto --store-id=123
```

### **Migración (tiendas existentes):**
```bash
php artisan subscription:migrate
php artisan billing:sync-invoices
```

### **Monitoreo logs:**
```bash
tail -f storage/logs/laravel.log | grep "Auto-billing"
```

---

## 📞 **SOPORTE Y TROUBLESHOOTING**

### **Si una tienda no tiene suscripción:**
1. Verificar logs del día de creación
2. Ejecutar diagnóstico: `php artisan billing:diagnose-auto --store-id=X`
3. Crear manualmente o usar comando de migración

### **Si falló la primera factura:**
1. Verificar que la suscripción existe
2. Generar manualmente desde SuperLinkiu
3. Verificar precios del plan en base de datos

### **Para debugging:**
1. Revisar logs con filtro "Auto-billing"
2. Usar comando de diagnóstico
3. Verificar que StoreObserver esté registrado correctamente

---

**🔒 ESTE SISTEMA ES AUTOMÁTICO Y NO REQUIERE CONFIGURACIÓN ADICIONAL** 