# Design Document

## Overview

El rediseño del formulario de creación de tiendas transformará la experiencia actual de un formulario monolítico a un wizard multi-paso intuitivo con validación en tiempo real, plantillas predefinidas y configuración contextual. El objetivo es reducir el tiempo de creación, minimizar errores y mejorar significativamente la experiencia del super administrador.

## Architecture

### Frontend Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Store Creation Wizard                     │
├─────────────────────────────────────────────────────────────┤
│  Step Navigation Component                                   │
│  ├── Progress Indicator                                      │
│  ├── Step Validation Status                                 │
│  └── Navigation Controls                                     │
├─────────────────────────────────────────────────────────────┤
│  Dynamic Form Steps                                          │
│  ├── Step 1: Template & Plan Selection                      │
│  ├── Step 2: Owner Information                              │
│  ├── Step 3: Store Configuration                            │
│  ├── Step 4: Fiscal Information (conditional)              │
│  ├── Step 5: SEO & Advanced (conditional)                  │
│  └── Step 6: Review & Confirmation                         │
├─────────────────────────────────────────────────────────────┤
│  Real-time Validation Engine                                │
│  ├── Field-level Validators                                 │
│  ├── Async Availability Checkers                           │
│  └── Cross-field Dependencies                               │
├─────────────────────────────────────────────────────────────┤
│  State Management                                            │
│  ├── Form Data Persistence                                  │
│  ├── Draft Auto-save                                        │
│  └── Error Recovery                                         │
└─────────────────────────────────────────────────────────────┘
```

### Backend Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Enhanced Store Controller                 │
├─────────────────────────────────────────────────────────────┤
│  Validation Endpoints                                        │
│  ├── /api/stores/validate-email                            │
│  ├── /api/stores/validate-slug                             │
│  ├── /api/stores/suggest-slug                              │
│  └── /api/stores/calculate-billing                         │
├─────────────────────────────────────────────────────────────┤
│  Draft Management                                            │
│  ├── StoreDraftService                                      │
│  ├── Auto-save Middleware                                   │
│  └── Recovery Mechanisms                                     │
├─────────────────────────────────────────────────────────────┤
│  Template System                                             │
│  ├── StoreTemplateService                                   │
│  ├── Template Configurations                                │
│  └── Dynamic Field Mapping                                  │
├─────────────────────────────────────────────────────────────┤
│  Enhanced Creation Pipeline                                  │
│  ├── Multi-step Validation                                  │
│  ├── Atomic Transaction Management                          │
│  └── Post-creation Automation                               │
└─────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Wizard Navigation Component

```typescript
interface WizardStep {
  id: string;
  title: string;
  description: string;
  component: string;
  validation: ValidationRule[];
  isOptional: boolean;
  dependsOn?: string[];
}

interface WizardState {
  currentStep: number;
  completedSteps: Set<number>;
  formData: StoreFormData;
  errors: ValidationErrors;
  isDraft: boolean;
}
```

**Características:**
- Progress bar visual con indicadores de completado
- Navegación condicional basada en validación
- Auto-save cada 30 segundos
- Breadcrumb navigation con salto a pasos completados

### 2. Template Selection Component

```typescript
interface StoreTemplate {
  id: string;
  name: string;
  description: string;
  icon: string;
  defaultPlan: string;
  requiredSteps: string[];
  optionalSteps: string[];
  prefilledData: Partial<StoreFormData>;
}

const templates: StoreTemplate[] = [
  {
    id: 'basic',
    name: 'Tienda Básica',
    description: 'Configuración rápida con campos esenciales',
    requiredSteps: ['owner', 'store-basic'],
    optionalSteps: ['seo']
  },
  {
    id: 'complete',
    name: 'Tienda Completa',
    description: 'Configuración completa con todas las opciones',
    requiredSteps: ['owner', 'store-complete', 'fiscal'],
    optionalSteps: ['seo', 'advanced']
  },
  {
    id: 'enterprise',
    name: 'Tienda Empresarial',
    description: 'Enfoque en información fiscal y compliance',
    requiredSteps: ['owner', 'store-complete', 'fiscal', 'compliance'],
    optionalSteps: ['seo', 'advanced']
  }
];
```

### 3. Real-time Validation Engine

```typescript
interface ValidationEngine {
  validateField(field: string, value: any): Promise<ValidationResult>;
  validateStep(stepId: string, data: object): Promise<StepValidationResult>;
  checkAvailability(type: 'email' | 'slug', value: string): Promise<AvailabilityResult>;
  suggestAlternatives(type: 'slug', value: string): Promise<string[]>;
}

interface ValidationResult {
  isValid: boolean;
  message?: string;
  suggestions?: string[];
}
```

**Validadores implementados:**
- Email uniqueness con debounce de 500ms
- Slug availability con sugerencias automáticas
- Document number format validation
- Plan compatibility checks
- Geographic data validation

### 4. Dynamic Form Fields

```typescript
interface DynamicField {
  name: string;
  type: 'text' | 'email' | 'select' | 'textarea' | 'autocomplete';
  label: string;
  placeholder?: string;
  required: boolean;
  validation: ValidationRule[];
  dependsOn?: FieldDependency[];
  autoComplete?: AutoCompleteConfig;
}

interface FieldDependency {
  field: string;
  condition: 'equals' | 'not_equals' | 'in' | 'not_in';
  value: any;
  action: 'show' | 'hide' | 'require' | 'disable';
}
```

### 5. Auto-complete Integration

```typescript
interface AutoCompleteConfig {
  source: 'api' | 'static';
  endpoint?: string;
  staticData?: any[];
  searchKey?: string;
  displayKey?: string;
  minChars: number;
  debounce: number;
}

// Implementaciones específicas
const locationAutoComplete: AutoCompleteConfig = {
  source: 'api',
  endpoint: '/api/locations/search',
  searchKey: 'query',
  displayKey: 'name',
  minChars: 2,
  debounce: 300
};
```

## Data Models

### Enhanced Store Creation Models

```typescript
interface StoreFormData {
  // Template selection
  template: string;
  
  // Owner information
  owner: {
    name: string;
    email: string;
    documentType: 'cedula' | 'nit' | 'pasaporte';
    documentNumber: string;
    country: string;
    department: string;
    city: string;
    password: string;
  };
  
  // Store configuration
  store: {
    name: string;
    slug: string;
    planId: string;
    email?: string;
    phone?: string;
    description?: string;
    status: 'active' | 'inactive';
  };
  
  // Fiscal information (conditional)
  fiscal?: {
    documentType?: 'nit' | 'cedula';
    documentNumber?: string;
    country?: string;
    department?: string;
    city?: string;
    address?: string;
  };
  
  // Billing configuration
  billing: {
    period: 'monthly' | 'quarterly' | 'biannual';
    initialStatus: 'pending' | 'paid';
    discountCode?: string;
  };
  
  // SEO configuration (optional)
  seo?: {
    metaTitle?: string;
    metaDescription?: string;
    metaKeywords?: string;
  };
  
  // Advanced configuration (optional)
  advanced?: {
    customDomain?: string;
    analyticsCode?: string;
    customCss?: string;
  };
}

interface StoreDraft {
  id: string;
  userId: string;
  formData: Partial<StoreFormData>;
  currentStep: number;
  template: string;
  createdAt: Date;
  updatedAt: Date;
  expiresAt: Date;
}
```

### Validation Schema

```typescript
const validationSchemas = {
  owner: {
    name: ['required', 'string', 'max:255'],
    email: ['required', 'email', 'unique:users,email'],
    documentType: ['required', 'in:cedula,nit,pasaporte'],
    documentNumber: ['required', 'string', 'max:20'],
    country: ['required', 'string', 'max:100'],
    department: ['required', 'string', 'max:100'],
    city: ['required', 'string', 'max:100'],
    password: ['required', 'string', 'min:8']
  },
  
  store: {
    name: ['required', 'string', 'max:255'],
    slug: ['required', 'string', 'max:255', 'unique:stores,slug', 'regex:/^[a-z0-9-]+$/'],
    planId: ['required', 'exists:plans,id'],
    email: ['nullable', 'email', 'unique:stores,email'],
    phone: ['nullable', 'string', 'max:20'],
    description: ['nullable', 'string', 'max:1000']
  }
};
```

## Error Handling

### Comprehensive Error Management

```typescript
interface ErrorHandler {
  // Network errors
  handleNetworkError(error: NetworkError): void;
  
  // Validation errors
  handleValidationError(field: string, error: ValidationError): void;
  
  // Server errors
  handleServerError(error: ServerError): void;
  
  // Recovery mechanisms
  recoverFromError(errorType: string): Promise<void>;
}

class WizardErrorHandler implements ErrorHandler {
  handleNetworkError(error: NetworkError) {
    // Auto-save current state
    this.autoSaveDraft();
    
    // Show user-friendly message
    this.showNotification('Conexión perdida. Datos guardados automáticamente.', 'warning');
    
    // Retry mechanism
    this.scheduleRetry();
  }
  
  handleValidationError(field: string, error: ValidationError) {
    // Show inline error
    this.showFieldError(field, error.message);
    
    // Suggest corrections
    if (error.suggestions) {
      this.showSuggestions(field, error.suggestions);
    }
    
    // Focus problematic field
    this.focusField(field);
  }
}
```

### Recovery Mechanisms

```typescript
interface RecoveryService {
  // Auto-save functionality
  autoSave(formData: Partial<StoreFormData>): Promise<void>;
  
  // Draft recovery
  recoverDraft(userId: string): Promise<StoreDraft | null>;
  
  // Session restoration
  restoreSession(sessionId: string): Promise<WizardState>;
  
  // Conflict resolution
  resolveConflicts(conflicts: DataConflict[]): Promise<Resolution[]>;
}
```

## Testing Strategy

### Unit Testing

```typescript
// Validation engine tests
describe('ValidationEngine', () => {
  test('should validate email uniqueness', async () => {
    const result = await validator.validateField('email', 'test@example.com');
    expect(result.isValid).toBe(false);
    expect(result.message).toContain('already exists');
  });
  
  test('should suggest alternative slugs', async () => {
    const suggestions = await validator.suggestAlternatives('slug', 'taken-slug');
    expect(suggestions).toContain('taken-slug-1');
    expect(suggestions).toContain('taken-slug-2');
  });
});

// Wizard navigation tests
describe('WizardNavigation', () => {
  test('should prevent navigation to incomplete steps', () => {
    const wizard = new WizardNavigation();
    wizard.setStepComplete(1, false);
    expect(wizard.canNavigateTo(2)).toBe(false);
  });
  
  test('should allow navigation to completed steps', () => {
    const wizard = new WizardNavigation();
    wizard.setStepComplete(1, true);
    expect(wizard.canNavigateTo(2)).toBe(true);
  });
});
```

### Integration Testing

```typescript
// End-to-end wizard flow
describe('Store Creation Wizard E2E', () => {
  test('should complete full store creation flow', async () => {
    // Select template
    await page.click('[data-template="basic"]');
    await page.click('[data-action="next"]');
    
    // Fill owner information
    await page.fill('[name="owner.name"]', 'John Doe');
    await page.fill('[name="owner.email"]', 'john@example.com');
    // ... more fields
    
    // Verify auto-save
    await page.waitForTimeout(31000); // Wait for auto-save
    const draftExists = await page.locator('[data-draft-indicator]').isVisible();
    expect(draftExists).toBe(true);
    
    // Complete creation
    await page.click('[data-action="create-store"]');
    await page.waitForSelector('[data-success-modal]');
  });
});
```

### Performance Testing

```typescript
// Load testing for validation endpoints
describe('Validation Performance', () => {
  test('should handle concurrent validation requests', async () => {
    const promises = Array.from({ length: 100 }, () =>
      fetch('/api/stores/validate-email', {
        method: 'POST',
        body: JSON.stringify({ email: `test${Math.random()}@example.com` })
      })
    );
    
    const results = await Promise.all(promises);
    const avgResponseTime = results.reduce((sum, r) => sum + r.responseTime, 0) / results.length;
    
    expect(avgResponseTime).toBeLessThan(200); // 200ms average
  });
});
```

### Accessibility Testing

```typescript
// A11y compliance tests
describe('Wizard Accessibility', () => {
  test('should be keyboard navigable', async () => {
    await page.keyboard.press('Tab');
    const focusedElement = await page.evaluate(() => document.activeElement.tagName);
    expect(focusedElement).toBe('BUTTON');
  });
  
  test('should have proper ARIA labels', async () => {
    const progressBar = await page.locator('[role="progressbar"]');
    expect(await progressBar.getAttribute('aria-valuenow')).toBe('1');
    expect(await progressBar.getAttribute('aria-valuemax')).toBe('6');
  });
});
```

## Implementation Phases

### Phase 1: Core Wizard Infrastructure (Week 1-2)
- Wizard navigation component
- Step management system
- Basic form validation
- Auto-save functionality

### Phase 2: Enhanced Validation (Week 3)
- Real-time field validation
- Async availability checking
- Suggestion engine
- Error handling improvements

### Phase 3: Template System (Week 4)
- Template selection interface
- Dynamic form generation
- Conditional field display
- Template-specific validation

### Phase 4: Advanced Features (Week 5-6)
- Auto-complete integration
- Geographic data APIs
- Billing calculations
- Preview functionality

### Phase 5: Polish & Testing (Week 7)
- UI/UX refinements
- Comprehensive testing
- Performance optimization
- Documentation

## Success Metrics

### User Experience Metrics
- **Form completion time**: Target 40% reduction (8min → 5min)
- **Abandonment rate**: Target 60% reduction
- **Validation errors**: Target 70% reduction
- **User satisfaction**: Target 4.5/5 rating

### Technical Metrics
- **API response time**: <200ms for validation endpoints
- **Auto-save reliability**: 99.9% success rate
- **Error recovery**: 95% successful recovery from failures
- **Accessibility score**: WCAG 2.1 AA compliance

### Business Metrics
- **Support tickets**: 30% reduction in store creation issues
- **Time to first store setup**: 50% improvement
- **Feature adoption**: 40% increase in advanced feature usage
- **Admin productivity**: 25% improvement in stores created per hour