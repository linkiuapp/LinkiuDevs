# Documento de Diseño - Gestión de Cuentas Bancarias

## Visión General

El sistema de gestión de cuentas bancarias permite a los administradores de tienda configurar métodos de pago por transferencia bancaria. El diseño se enfoca en resolver el error actual de validación del campo `is_active` y mejorar la robustez general del sistema.

## Arquitectura

### Componentes Principales

1. **Controlador de Cuentas Bancarias** (`BankAccountController`)
2. **Modelo de Cuenta Bancaria** (`BankAccount`)
3. **Validador de Formularios** (`BankAccountRequest`)
4. **Vistas Blade** (index, create, edit)
5. **Middleware de Validación de Límites**

### Flujo de Datos

```
Usuario → Formulario → Request Validation → Controller → Model → Database
                                    ↓
                              Error Handling → Response → Vista
```

## Componentes e Interfaces

### 1. Validación de Formularios

**Problema Identificado:** El campo `is_active` no se procesa correctamente como booleano desde el checkbox HTML.

**Solución:**
- Implementar `BankAccountRequest` con validación personalizada
- Convertir valores de checkbox a booleano antes de la validación
- Manejar casos donde el checkbox no está marcado (valor ausente)

```php
// Reglas de validación mejoradas
public function rules(): array
{
    return [
        'bank_name' => 'required|string|min:2|max:100',
        'account_type' => 'required|in:savings,checking,business',
        'account_number' => 'required|string|regex:/^[0-9]{10,20}$/',
        'account_holder' => 'required|string|min:3|max:100',
        'document_number' => 'nullable|string|max:20',
        'is_active' => 'boolean'
    ];
}

// Preparación de datos antes de validación
protected function prepareForValidation()
{
    $this->merge([
        'is_active' => $this->boolean('is_active')
    ]);
}
```

### 2. Controlador Mejorado

**Funcionalidades:**
- Manejo robusto de errores
- Validación de límites de plan
- Procesamiento correcto de datos booleanos
- Respuestas consistentes

```php
public function store(BankAccountRequest $request)
{
    // Validar límites del plan
    $this->validatePlanLimits($paymentMethod);
    
    // Crear cuenta con datos validados
    $bankAccount = BankAccount::create([
        'payment_method_id' => $paymentMethod->id,
        'bank_name' => $request->bank_name,
        'account_type' => $request->account_type,
        'account_number' => $request->account_number,
        'account_holder' => $request->account_holder,
        'document_number' => $request->document_number,
        'is_active' => $request->boolean('is_active', true)
    ]);
    
    return redirect()->route('...')->with('success', 'Cuenta creada exitosamente');
}
```

### 3. Modelo de Datos

**Atributos:**
- `id`: Identificador único
- `payment_method_id`: Relación con método de pago
- `bank_name`: Nombre del banco
- `account_type`: Tipo de cuenta (enum)
- `account_number`: Número de cuenta (encriptado)
- `account_holder`: Titular de la cuenta
- `document_number`: Número de documento (opcional)
- `is_active`: Estado activo (booleano)
- `created_at`, `updated_at`: Timestamps

**Mutadores y Accesores:**
```php
// Encriptar número de cuenta
protected function setAccountNumberAttribute($value)
{
    $this->attributes['account_number'] = encrypt($value);
}

// Desencriptar para mostrar
protected function getAccountNumberAttribute($value)
{
    return decrypt($value);
}

// Formatear número para mostrar (parcialmente oculto)
public function getFormattedAccountNumber()
{
    $number = $this->account_number;
    return '****' . substr($number, -4);
}
```

## Modelos de Datos

### Tabla: bank_accounts

```sql
CREATE TABLE bank_accounts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    payment_method_id BIGINT UNSIGNED NOT NULL,
    bank_name VARCHAR(100) NOT NULL,
    account_type ENUM('savings', 'checking', 'business') NOT NULL,
    account_number TEXT NOT NULL, -- Encriptado
    account_holder VARCHAR(100) NOT NULL,
    document_number VARCHAR(20) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE CASCADE,
    INDEX idx_payment_method_active (payment_method_id, is_active)
);
```

## Manejo de Errores

### Tipos de Errores

1. **Errores de Validación**
   - Campos requeridos faltantes
   - Formato incorrecto de datos
   - Violación de reglas de negocio

2. **Errores de Límites**
   - Exceso de cuentas permitidas por plan
   - Restricciones de funcionalidad

3. **Errores del Sistema**
   - Fallos de base de datos
   - Problemas de encriptación

### Estrategia de Manejo

```php
// En el controlador
try {
    $bankAccount = BankAccount::create($validatedData);
    return redirect()->back()->with('success', 'Cuenta creada exitosamente');
} catch (ValidationException $e) {
    return redirect()->back()
        ->withErrors($e->errors())
        ->withInput();
} catch (PlanLimitException $e) {
    return redirect()->back()
        ->with('error', 'Has alcanzado el límite de cuentas para tu plan')
        ->withInput();
} catch (Exception $e) {
    Log::error('Error creating bank account', ['error' => $e->getMessage()]);
    return redirect()->back()
        ->with('error', 'Ocurrió un error inesperado. Inténtalo de nuevo.')
        ->withInput();
}
```

## Estrategia de Pruebas

### Pruebas Unitarias

1. **Validación de Formularios**
   - Campos requeridos
   - Formatos de datos
   - Conversión de booleanos

2. **Modelo de Datos**
   - Encriptación/desencriptación
   - Mutadores y accesores
   - Relaciones

3. **Lógica de Negocio**
   - Límites de plan
   - Estados de cuenta
   - Validaciones personalizadas

### Pruebas de Integración

1. **Flujo Completo de Creación**
   - Formulario → Validación → Almacenamiento
   - Manejo de errores
   - Redirecciones correctas

2. **Operaciones CRUD**
   - Crear, leer, actualizar, eliminar
   - Validaciones en cada operación
   - Integridad de datos

### Casos de Prueba Específicos

```php
// Prueba del error actual
public function test_checkbox_is_active_validation()
{
    $data = [
        'bank_name' => 'Banco Test',
        'account_type' => 'savings',
        'account_number' => '1234567890',
        'account_holder' => 'Test User',
        // is_active no enviado (checkbox no marcado)
    ];
    
    $response = $this->post(route('bank-accounts.store'), $data);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('bank_accounts', [
        'bank_name' => 'Banco Test',
        'is_active' => false // Debe ser false por defecto
    ]);
}
```

## Consideraciones de Seguridad

1. **Encriptación de Datos Sensibles**
   - Números de cuenta encriptados en base de datos
   - Claves de encriptación seguras

2. **Validación de Entrada**
   - Sanitización de todos los inputs
   - Validación estricta de formatos

3. **Control de Acceso**
   - Verificación de permisos por tienda
   - Middleware de autenticación

4. **Auditoría**
   - Log de operaciones críticas
   - Seguimiento de cambios

## Optimizaciones de Rendimiento

1. **Índices de Base de Datos**
   - Índice compuesto en (payment_method_id, is_active)
   - Índices en campos de búsqueda frecuente

2. **Caché**
   - Caché de límites de plan
   - Caché de configuraciones frecuentes

3. **Consultas Optimizadas**
   - Eager loading de relaciones
   - Consultas específicas sin over-fetching