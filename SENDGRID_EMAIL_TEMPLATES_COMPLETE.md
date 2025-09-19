# 📧 PLANTILLAS EMAIL COMPLETAS PARA SENDGRID - LINKIU.BIO

## 🎯 **RESUMEN EJECUTIVO**

**Total plantillas requeridas:** 25 plantillas
**Categorías:** 5 contextos principales
**Variables totales:** 45+ variables únicas

---

## 📋 **1. GESTIÓN DE TIENDAS (store_management)**

### 🎉 **1.1 STORE_WELCOME - Bienvenida Nueva Tienda**
**Trigger:** Al crear una nueva tienda
**SendGrid Template ID:** `[TU_ID_1]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda", 
  "app_name": "Linkiu.bio",
  "plan_name": "Plan Explorer/Master/Legend",
  "store_url": "https://linkiu.bio/nombre-tienda",
  "support_email": "soporte@linkiu.bio",
  "current_year": "2025"
}
```

### 🔑 **1.2 STORE_CREDENTIALS - Credenciales de Acceso**
**Trigger:** Al crear tienda + botón "Reenviar credenciales"
**SendGrid Template ID:** `[TU_ID_2]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "admin_email": "email@administrador.com",
  "password": "ContraseñaGenerada123",
  "admin_url": "https://linkiu.bio/nombre-tienda/admin",
  "store_url": "https://linkiu.bio/nombre-tienda",
  "support_email": "soporte@linkiu.bio",
  "app_name": "Linkiu.bio"
}
```

### 🔄 **1.3 STORE_STATUS_CHANGED - Cambio de Estado**
**Trigger:** Cambio Active/Suspended/Maintenance
**SendGrid Template ID:** `[TU_ID_3]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "old_status": "Activa/Suspendida/Mantenimiento",
  "new_status": "Activa/Suspendida/Mantenimiento", 
  "change_reason": "Razón del cambio",
  "changed_by": "Nombre del super admin",
  "change_date": "18/09/2025 14:30",
  "store_url": "https://linkiu.bio/nombre-tienda",
  "admin_url": "https://linkiu.bio/nombre-tienda/admin",
  "support_email": "soporte@linkiu.bio"
}
```

### 📦 **1.4 STORE_PLAN_CHANGED - Cambio de Plan**
**Trigger:** Cambio de plan de suscripción
**SendGrid Template ID:** `[TU_ID_4]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "old_plan": "Plan Explorer",
  "new_plan": "Plan Master",
  "upgrade_type": "Upgrade/Downgrade",
  "effective_date": "25/09/2025",
  "new_features": "Lista de nuevas características",
  "billing_impact": "Cambios en facturación",
  "admin_url": "https://linkiu.bio/nombre-tienda/admin",
  "support_email": "soporte@linkiu.bio"
}
```

### ✅ **1.5 STORE_VERIFIED - Tienda Verificada**
**Trigger:** Tienda marcada como verificada
**SendGrid Template ID:** `[TU_ID_5]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "verification_date": "18/09/2025",
  "verification_benefits": "Lista de beneficios",
  "store_url": "https://linkiu.bio/nombre-tienda",
  "admin_url": "https://linkiu.bio/nombre-tienda/admin",
  "support_email": "soporte@linkiu.bio"
}
```

### ⚠️ **1.6 STORE_UNVERIFIED - Tienda Des-verificada**
**Trigger:** Tienda des-verificada
**SendGrid Template ID:** `[TU_ID_6]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "unverification_reason": "Razón de la des-verificación",
  "unverification_date": "18/09/2025",
  "reactivation_steps": "Pasos para re-verificación",
  "admin_url": "https://linkiu.bio/nombre-tienda/admin",
  "support_email": "soporte@linkiu.bio"
}
```

### 🔐 **1.7 PASSWORD_CHANGED - Contraseña Cambiada**
**Trigger:** Cambio de contraseña de admin
**SendGrid Template ID:** `[TU_ID_7]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "change_date": "18/09/2025 14:30",
  "change_ip": "192.168.1.1",
  "admin_url": "https://linkiu.bio/nombre-tienda/admin",
  "support_email": "soporte@linkiu.bio"
}
```

---

## 🎫 **2. SOPORTE Y TICKETS (support)**

### 📝 **2.1 TICKET_CREATED - Ticket Creado**
**Trigger:** Creación de nuevo ticket
**SendGrid Template ID:** `[TU_ID_8]`
**Variables requeridas:**
```json
{
  "customer_name": "Nombre del cliente",
  "store_name": "Nombre de la tienda",
  "ticket_id": "TK-2025-001",
  "ticket_subject": "Problema con productos",
  "ticket_description": "Descripción del problema",
  "priority": "Alta/Media/Baja",
  "status": "Abierto",
  "created_date": "18/09/2025 14:30",
  "ticket_url": "https://linkiu.bio/admin/tickets/1",
  "support_email": "soporte@linkiu.bio"
}
```

### 💬 **2.2 TICKET_RESPONSE - Respuesta de Ticket**
**Trigger:** Respuesta a ticket por parte de soporte
**SendGrid Template ID:** `[TU_ID_9]`
**Variables requeridas:**
```json
{
  "customer_name": "Nombre del cliente",
  "store_name": "Nombre de la tienda",
  "ticket_id": "TK-2025-001",
  "ticket_subject": "Problema con productos",
  "response_message": "Mensaje de respuesta del soporte",
  "responder_name": "Nombre del agente",
  "response_date": "18/09/2025 15:45",
  "status": "En progreso",
  "ticket_url": "https://linkiu.bio/admin/tickets/1",
  "support_email": "soporte@linkiu.bio"
}
```

### 🔄 **2.3 TICKET_STATUS_CHANGED - Cambio Estado Ticket**
**Trigger:** Cambio de estado del ticket
**SendGrid Template ID:** `[TU_ID_10]`
**Variables requeridas:**
```json
{
  "customer_name": "Nombre del cliente",
  "store_name": "Nombre de la tienda",
  "ticket_id": "TK-2025-001",
  "ticket_subject": "Problema con productos",
  "old_status": "Abierto",
  "new_status": "Resuelto",
  "status_reason": "Problema solucionado",
  "changed_by": "Nombre del agente",
  "change_date": "18/09/2025 16:00",
  "ticket_url": "https://linkiu.bio/admin/tickets/1",
  "support_email": "soporte@linkiu.bio"
}
```

### ✅ **2.4 TICKET_RESOLVED - Ticket Resuelto**
**Trigger:** Ticket marcado como resuelto
**SendGrid Template ID:** `[TU_ID_11]`
**Variables requeridas:**
```json
{
  "customer_name": "Nombre del cliente",
  "store_name": "Nombre de la tienda",
  "ticket_id": "TK-2025-001",
  "ticket_subject": "Problema con productos",
  "resolution_summary": "Resumen de la solución",
  "resolved_by": "Nombre del agente",
  "resolution_date": "18/09/2025 16:30",
  "satisfaction_survey_url": "https://linkiu.bio/survey/123",
  "support_email": "soporte@linkiu.bio"
}
```

---

## 💰 **3. FACTURACIÓN Y PAGOS (billing)**

### 📄 **3.1 INVOICE_CREATED - Factura Creada**
**Trigger:** Generación de nueva factura
**SendGrid Template ID:** `[TU_ID_12]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "invoice_number": "INV-2025-0001",
  "amount": "$150.000",
  "currency": "COP",
  "due_date": "25/09/2025",
  "issue_date": "18/09/2025",
  "plan_name": "Plan Master",
  "billing_period": "Mensual/Anual",
  "days_to_pay": "7",
  "payment_instructions": "Instrucciones de pago",
  "invoice_url": "https://linkiu.bio/admin/invoices/1",
  "dashboard_url": "https://linkiu.bio/tienda/admin/billing",
  "support_email": "facturacion@linkiu.bio"
}
```

### 💳 **3.2 INVOICE_PAID - Factura Pagada**
**Trigger:** Confirmación de pago de factura
**SendGrid Template ID:** `[TU_ID_13]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "invoice_number": "INV-2025-0001",
  "amount": "$150.000",
  "payment_date": "20/09/2025",
  "payment_method": "Transferencia bancaria",
  "receipt_number": "REC-2025-0001",
  "plan_name": "Plan Master",
  "next_billing_date": "20/10/2025",
  "invoice_url": "https://linkiu.bio/admin/invoices/1",
  "dashboard_url": "https://linkiu.bio/tienda/admin/billing",
  "support_email": "facturacion@linkiu.bio"
}
```

### ⏰ **3.3 INVOICE_REMINDER - Recordatorio de Pago**
**Trigger:** 7, 3, 1 día antes del vencimiento
**SendGrid Template ID:** `[TU_ID_14]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "invoice_number": "INV-2025-0001",
  "amount": "$150.000",
  "due_date": "25/09/2025",
  "days_remaining": "3",
  "urgency_level": "Alta/Media/Baja",
  "payment_instructions": "Instrucciones de pago",
  "late_fee_warning": "Advertencia de recargo",
  "invoice_url": "https://linkiu.bio/admin/invoices/1",
  "support_email": "facturacion@linkiu.bio"
}
```

### 🔴 **3.4 INVOICE_OVERDUE - Factura Vencida**
**Trigger:** Factura con pago vencido
**SendGrid Template ID:** `[TU_ID_15]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "invoice_number": "INV-2025-0001",
  "amount": "$150.000",
  "due_date": "25/09/2025",
  "days_overdue": "5",
  "late_fee": "$15.000",
  "total_amount": "$165.000",
  "suspension_warning": "Advertencia de suspensión",
  "suspension_date": "02/10/2025",
  "payment_instructions": "Instrucciones urgentes de pago",
  "invoice_url": "https://linkiu.bio/admin/invoices/1",
  "support_phone": "+57 300 123 4567",
  "support_email": "facturacion@linkiu.bio"
}
```

### 🔄 **3.5 SUBSCRIPTION_RENEWAL_REMINDER - Recordatorio Renovación**
**Trigger:** 7, 3, 1 día antes de la renovación
**SendGrid Template ID:** `[TU_ID_16]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "plan_name": "Plan Master",
  "renewal_date": "01/10/2025",
  "renewal_amount": "$150.000",
  "days_remaining": "3",
  "current_features": "Lista de características actuales",
  "auto_renewal_status": "Activado/Desactivado",
  "dashboard_url": "https://linkiu.bio/tienda/admin/billing",
  "support_email": "facturacion@linkiu.bio"
}
```

### ⚡ **3.6 SUBSCRIPTION_EXPIRED - Suscripción Expirada**
**Trigger:** Suscripción vencida
**SendGrid Template ID:** `[TU_ID_17]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "plan_name": "Plan Master",
  "expiration_date": "01/10/2025",
  "grace_period_days": "7",
  "grace_period_end": "08/10/2025",
  "reactivation_instructions": "Pasos para reactivar",
  "data_preservation": "Información sobre preservación de datos",
  "support_phone": "+57 300 123 4567",
  "support_email": "facturacion@linkiu.bio"
}
```

### ⚠️ **3.7 SUSPENSION_WARNING - Advertencia de Suspensión**
**Trigger:** Previo a suspensión por impago
**SendGrid Template ID:** `[TU_ID_18]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "suspension_date": "10/10/2025",
  "suspension_reason": "Falta de pago",
  "outstanding_amount": "$165.000",
  "hours_remaining": "48",
  "urgent_payment_instructions": "Instrucciones urgentes",
  "data_backup_info": "Información sobre respaldo",
  "support_phone": "+57 300 123 4567",
  "support_email": "urgente@linkiu.bio"
}
```

---

## 🛒 **4. TIENDA Y PEDIDOS (store_orders)**

### 🛍️ **4.1 NEW_ORDER - Nuevo Pedido**
**Trigger:** Cliente hace un pedido
**SendGrid Template ID:** `[TU_ID_19]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "order_number": "ORD-2025-0001",
  "customer_name": "Nombre del cliente",
  "customer_email": "cliente@email.com",
  "order_total": "$75.000",
  "order_items": "Lista de productos",
  "delivery_type": "Domicilio/Pickup",
  "delivery_address": "Dirección de entrega",
  "payment_method": "Transferencia/Efectivo",
  "order_date": "18/09/2025 14:30",
  "order_url": "https://linkiu.bio/tienda/admin/orders/1",
  "dashboard_url": "https://linkiu.bio/tienda/admin"
}
```

### 📦 **4.2 ORDER_STATUS_CHANGED - Cambio Estado Pedido**
**Trigger:** Cambio estado del pedido
**SendGrid Template ID:** `[TU_ID_20]`
**Variables requeridas:**
```json
{
  "customer_name": "Nombre del cliente",
  "store_name": "Nombre de la tienda",
  "order_number": "ORD-2025-0001",
  "old_status": "Pendiente",
  "new_status": "En preparación",
  "status_message": "Tu pedido está siendo preparado",
  "estimated_delivery": "20/09/2025",
  "tracking_info": "Información de seguimiento",
  "store_contact": "Contacto de la tienda"
}
```

### 🎯 **4.3 LOW_STOCK_ALERT - Alerta Stock Bajo**
**Trigger:** Producto con stock bajo
**SendGrid Template ID:** `[TU_ID_21]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "product_name": "Nombre del producto",
  "current_stock": "2",
  "minimum_stock": "5",
  "product_sku": "SKU-123",
  "restock_suggestion": "Sugerencia de reabastecimiento",
  "product_url": "https://linkiu.bio/tienda/admin/products/1",
  "dashboard_url": "https://linkiu.bio/tienda/admin"
}
```

---

## 🔧 **5. SISTEMA Y SEGURIDAD (system)**

### 🔒 **5.1 SECURITY_ALERT - Alerta de Seguridad**
**Trigger:** Intento de acceso sospechoso
**SendGrid Template ID:** `[TU_ID_22]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "alert_type": "Acceso sospechoso",
  "ip_address": "192.168.1.100",
  "location": "Bogotá, Colombia",
  "attempt_time": "18/09/2025 14:30",
  "browser": "Chrome 118",
  "action_taken": "Acceso bloqueado",
  "security_recommendations": "Recomendaciones de seguridad",
  "admin_url": "https://linkiu.bio/tienda/admin/security"
}
```

### 📊 **5.2 WEEKLY_REPORT - Reporte Semanal**
**Trigger:** Cada lunes (automático)
**SendGrid Template ID:** `[TU_ID_23]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "week_period": "11-17 Septiembre 2025",
  "total_orders": "15",
  "total_revenue": "$450.000",
  "top_product": "Producto más vendido",
  "new_customers": "5",
  "conversion_rate": "3.2%",
  "key_metrics": "Métricas principales",
  "dashboard_url": "https://linkiu.bio/tienda/admin/analytics"
}
```

### 💾 **5.3 BACKUP_COMPLETED - Respaldo Completado**
**Trigger:** Respaldo automático de datos
**SendGrid Template ID:** `[TU_ID_24]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "backup_date": "18/09/2025 02:00",
  "backup_size": "45.2 MB",
  "backup_status": "Exitoso",
  "data_included": "Productos, pedidos, clientes",
  "retention_period": "30 días",
  "restore_instructions": "Instrucciones de restauración",
  "support_email": "soporte@linkiu.bio"
}
```

### 🚨 **5.4 MAINTENANCE_NOTICE - Aviso de Mantenimiento**
**Trigger:** Mantenimiento programado
**SendGrid Template ID:** `[TU_ID_25]`
**Variables requeridas:**
```json
{
  "admin_name": "Nombre del administrador",
  "store_name": "Nombre de la tienda",
  "maintenance_date": "25/09/2025",
  "maintenance_time": "02:00 - 04:00",
  "maintenance_duration": "2 horas",
  "affected_services": "Panel admin, procesamiento pagos",
  "maintenance_reason": "Actualización de seguridad",
  "alternative_contact": "soporte@linkiu.bio",
  "status_page": "https://status.linkiu.bio"
}
```

---

## 🎨 **VARIABLES COMUNES GLOBALES**

Estas variables están disponibles en **TODAS** las plantillas:

```json
{
  "app_name": "Linkiu.bio",
  "app_logo": "https://linkiu.bio/assets/logo.png",
  "company_name": "Linkiu Technologies",
  "support_email": "soporte@linkiu.bio",
  "billing_email": "facturacion@linkiu.bio",
  "no_reply_email": "no-responder@linkiu.bio",
  "current_year": "2025",
  "current_date": "18/09/2025",
  "timezone": "America/Bogota",
  "website_url": "https://linkiu.com.co",
  "privacy_policy_url": "https://linkiu.com.co/privacy",
  "terms_url": "https://linkiu.com.co/terms",
  "unsubscribe_url": "https://linkiu.bio/unsubscribe/{{contact_id}}"
}
```

---

## 📋 **CHECKLIST PARA SENDGRID**

### ✅ **Lo que necesitas hacer:**

1. **Crear 25 plantillas** en SendGrid Dashboard
2. **Obtener Template ID** de cada una
3. **Configurar variables** exactas como se muestran arriba
4. **Probar cada plantilla** con datos de ejemplo
5. **Entregarme los 25 Template IDs** para mapear en Laravel

### 📝 **Formato de entrega:**

```
STORE_WELCOME: d-1234567890abcdef
STORE_CREDENTIALS: d-2345678901bcdefg
STORE_STATUS_CHANGED: d-3456789012cdefgh
... (continuar con los 25)
```

### 🎯 **Beneficios de usar SendGrid:**

- ✅ **Editor visual** más fácil que HTML embebido
- ✅ **Responsive automático** para móviles
- ✅ **Estadísticas** de apertura, clicks, etc.
- ✅ **Deliverability** mejorado
- ✅ **A/B Testing** disponible
- ✅ **Plantillas reutilizables** y versionadas

---

## 🚀 **SIGUIENTE PASO**

**Una vez tengas los 25 Template IDs de SendGrid, podré:**

1. Configurar la integración SendGrid en Laravel
2. Mapear cada plantilla con su Template ID
3. Actualizar todos los servicios de email
4. Hacer pruebas de envío
5. Documentar el nuevo sistema

**¿Estás listo para crear estas plantillas en SendGrid?** 🎯📧
