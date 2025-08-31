# Design Document

## Overview

El sistema de configuración de emails será implementado como una extensión del módulo SuperLinkiu existente, proporcionando una interfaz web para gestionar direcciones de email por contexto y plantillas personalizables. La arquitectura seguirá el patrón MVC de Laravel y se integrará transparentemente con el sistema de envío de emails existente.

## Architecture

### High-Level Architecture

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   SuperLinkiu   │    │   Email Service  │    │   Mail System   │
│   Interface     │───▶│   Layer          │───▶│   (Laravel)     │
└─────────────────┘    └──────────────────┘    └─────────────────┘
         │                        │
         ▼                        ▼
┌─────────────────┐    ┌──────────────────┐
│   Controllers   │    │   Models         │
│   - EmailConfig │    │   - EmailSetting │
│   - Templates   │    │   - EmailTemplate│
└─────────────────┘    └──────────────────┘
         │                        │
         ▼                        ▼
┌─────────────────┐    ┌──────────────────┐
│   Views         │    │   Database       │
│   - Config Form │    │   - email_settings│
│   - Template    │    │   - email_templates│
│     Editor      │    └──────────────────┘
└─────────────────┘
```

### Integration Points

1. **Existing Mail System**: Se integrará con el sistema de Mail de Laravel existente
2. **SuperLinkiu Module**: Nuevas rutas y vistas dentro del módulo SuperLinkiu
3. **Authentication**: Usará el middleware super.admin existente
4. **Database**: Nuevas tablas que coexistirán con las existentes

## Components and Interfaces

### Database Schema

#### email_settings Table
```sql
CREATE TABLE email_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    context VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_context (context),
    INDEX idx_active (is_active)
);
```

#### email_templates Table
```sql
CREATE TABLE email_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_key VARCHAR(100) NOT NULL UNIQUE,
    context VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    subject VARCHAR(500) NOT NULL,
    body_html TEXT,
    body_text TEXT,
    variables JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_template_key (template_key),
    INDEX idx_context (context),
    INDEX idx_active (is_active),
    FOREIGN KEY (context) REFERENCES email_settings(context) ON DELETE CASCADE
);
```

### Models

#### EmailSetting Model
```php
class EmailSetting extends Model
{
    protected $fillable = ['context', 'email', 'name', 'is_active'];
    
    // Static methods for easy access
    public static function getEmail(string $context): string
    public static function getActiveSettings(): Collection
    public static function updateContext(string $context, string $email): bool
    
    // Relationships
    public function templates(): HasMany
    
    // Scopes
    public function scopeActive($query)
}
```

#### EmailTemplate Model
```php
class EmailTemplate extends Model
{
    protected $fillable = ['template_key', 'context', 'name', 'subject', 'body_html', 'body_text', 'variables', 'is_active'];
    protected $casts = ['variables' => 'array'];
    
    // Static methods
    public static function getTemplate(string $key): ?EmailTemplate
    public static function renderTemplate(string $key, array $data): array
    
    // Instance methods
    public function replaceVariables(array $data): array
    public function getAvailableVariables(): array
    
    // Relationships
    public function emailSetting(): BelongsTo
    
    // Scopes
    public function scopeActive($query)
    public function scopeByContext($query, string $context)
}
```

### Services

#### EmailService
```php
class EmailService
{
    public static function sendWithTemplate(
        string $templateKey, 
        array $recipients, 
        array $data = []
    ): bool
    
    public static function getContextEmail(string $context): string
    public static function validateEmailConfiguration(): array
    
    private static function prepareMailData(EmailTemplate $template, array $data): array
    private static function logEmailSent(string $templateKey, array $recipients): void
}
```

### Controllers

#### EmailConfigurationController (Extension)
```php
// Extend existing controller with new methods
public function emailSettings(): View
public function updateEmailSettings(Request $request): RedirectResponse
public function templateIndex(): View
public function templateEdit(EmailTemplate $template): View
public function templateUpdate(Request $request, EmailTemplate $template): RedirectResponse
```

### Views Structure

```
app/Features/SuperLinkiu/Views/email/
├── settings/
│   ├── index.blade.php          # Email addresses configuration
│   └── partials/
│       └── context-form.blade.php
├── templates/
│   ├── index.blade.php          # Templates list
│   ├── edit.blade.php           # Template editor
│   └── partials/
│       ├── template-form.blade.php
│       ├── variable-helper.blade.php
│       └── preview.blade.php
└── components/
    ├── email-input.blade.php
    └── template-editor.blade.php
```

## Data Models

### Email Contexts
```php
const EMAIL_CONTEXTS = [
    'store_management' => [
        'name' => 'Gestión de Tiendas',
        'default_email' => 'no-responder@linkiudev.co',
        'description' => 'Creación de tiendas, cambios de contraseña, notificaciones admin-tienda'
    ],
    'support' => [
        'name' => 'Soporte',
        'default_email' => 'soporte@linkiudev.co',
        'description' => 'CRUD de tickets, notificaciones de soporte'
    ],
    'billing' => [
        'name' => 'Facturación',
        'default_email' => 'contabilidad@linkiudev.co',
        'description' => 'Todo relacionado con facturación'
    ]
];
```

### Template Variables System
```php
const TEMPLATE_VARIABLES = [
    'store_management' => [
        '{{store_name}}' => 'Nombre de la tienda',
        '{{admin_name}}' => 'Nombre del administrador',
        '{{admin_email}}' => 'Email del administrador',
        '{{password}}' => 'Contraseña temporal',
        '{{login_url}}' => 'URL de acceso',
        '{{support_email}}' => 'Email de soporte'
    ],
    'support' => [
        '{{ticket_id}}' => 'ID del ticket',
        '{{ticket_subject}}' => 'Asunto del ticket',
        '{{customer_name}}' => 'Nombre del cliente',
        '{{response}}' => 'Respuesta del ticket',
        '{{status}}' => 'Estado del ticket'
    ],
    'billing' => [
        '{{invoice_number}}' => 'Número de factura',
        '{{amount}}' => 'Monto de la factura',
        '{{due_date}}' => 'Fecha de vencimiento',
        '{{store_name}}' => 'Nombre de la tienda',
        '{{plan_name}}' => 'Nombre del plan'
    ]
];
```

## Error Handling

### Validation Rules
- Email addresses: RFC 5322 compliant validation
- Template content: Required fields validation
- Context validation: Must be one of predefined contexts
- HTML sanitization: Prevent XSS in template content

### Error Scenarios
1. **Invalid email format**: Show field-specific error message
2. **Template rendering failure**: Log error, use fallback template
3. **Missing template**: Use default template or plain text
4. **Database connection issues**: Show user-friendly error, log technical details
5. **Permission denied**: Redirect to login with appropriate message

### Fallback Mechanisms
- Default email addresses if configuration is missing
- Plain text templates if HTML rendering fails
- System admin notification for critical email failures

## Testing Strategy

### Unit Tests
- EmailSetting model methods
- EmailTemplate model methods
- EmailService static methods
- Template variable replacement
- Email validation logic

### Integration Tests
- Email sending with templates
- Database operations
- Controller actions
- Form submissions
- Template rendering with real data

### Feature Tests
- Complete email configuration workflow
- Template creation and editing
- Email sending integration
- Permission checks
- Error handling scenarios

### Test Data
- Seed data for all email contexts
- Sample templates for each context
- Test email addresses
- Mock email sending for testing environment

## Performance Considerations

### Caching Strategy
- Cache email settings in application cache
- Cache compiled templates
- Cache template variables mapping
- Clear cache on configuration updates

### Database Optimization
- Indexes on frequently queried fields
- Minimal database queries for email sending
- Batch operations for bulk updates

### Memory Management
- Lazy loading of template content
- Efficient variable replacement algorithms
- Cleanup of temporary data

## Security Considerations

### Access Control
- Super admin middleware for all configuration routes
- CSRF protection on all forms
- Input validation and sanitization

### Data Protection
- HTML sanitization for template content
- SQL injection prevention
- XSS prevention in template variables

### Email Security
- Validate recipient addresses
- Rate limiting for email sending
- Logging of email activities for audit

## Migration Strategy

### Phase 1: Database Setup
1. Create migrations for new tables
2. Seed initial data with current email addresses
3. Create default templates

### Phase 2: Backend Implementation
1. Implement models and services
2. Extend existing controllers
3. Add validation and error handling

### Phase 3: Frontend Implementation
1. Create configuration views
2. Implement template editor
3. Add navigation and routing

### Phase 4: Integration
1. Update existing email sending code
2. Test all email flows
3. Deploy and monitor

### Rollback Plan
- Keep existing email sending as fallback
- Feature flags for gradual rollout
- Database rollback scripts if needed