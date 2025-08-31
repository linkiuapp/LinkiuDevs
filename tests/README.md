# Store Creation Wizard - Testing Documentation

This document describes the comprehensive testing strategy implemented for the Store Creation Wizard improvements.

## Overview

The testing suite covers all aspects of the enhanced store creation wizard, including:

- **Unit Tests**: Individual components and services
- **Integration Tests**: Component interactions and API endpoints
- **End-to-End Tests**: Complete user workflows
- **Performance Tests**: Response times and scalability
- **Accessibility Tests**: WCAG 2.1 AA compliance

## Test Structure

```
tests/
├── Unit/                           # Unit tests
│   ├── StoreControllerValidationTest.php
│   ├── StoreTemplateServiceTest.php
│   ├── FiscalValidationServiceTest.php
│   └── WizardComponentsTest.js
├── Feature/                        # Integration tests
│   ├── WizardFlowIntegrationTest.php
│   ├── TemplateSelectionIntegrationTest.php
│   ├── DraftManagementIntegrationTest.php
│   ├── StoreConfigurationStepTest.php
│   ├── FiscalInformationStepTest.php
│   └── ValidationPerformanceTest.php
├── Browser/                        # End-to-end tests
│   ├── StoreCreationWizardTest.php
│   └── AccessibilityTest.php
└── setup.js                       # JavaScript test setup
```

## Running Tests

### All Tests
```bash
# Linux/Mac
./run-tests.sh

# Windows
run-tests.bat
```

### Individual Test Suites

#### PHP Unit Tests
```bash
php artisan test --testsuite=Unit
```

#### PHP Feature Tests
```bash
php artisan test --testsuite=Feature
```

#### JavaScript Unit Tests
```bash
npm test
```

#### Browser Tests (Dusk)
```bash
php artisan dusk
```

#### Performance Tests
```bash
php artisan test tests/Feature/ValidationPerformanceTest.php
```

## Test Coverage

### Unit Tests Coverage

#### StoreController Validation Endpoints
- ✅ Email validation (uniqueness, format)
- ✅ Slug validation (availability, sanitization)
- ✅ Slug suggestions (intelligent alternatives)
- ✅ Billing calculations (plans, periods, discounts)
- ✅ Email domain suggestions (typo corrections)
- ✅ Validation error recovery suggestions
- ✅ Error handling and edge cases

#### StoreTemplateService
- ✅ Template retrieval and configuration
- ✅ Template validation rules generation
- ✅ Field mapping and step processing
- ✅ Capability-based filtering
- ✅ Template validation and structure checks
- ✅ Cache management
- ✅ Custom template management

#### FiscalValidationService
- ✅ Document validation (NIT, RUT, RFC, Cedula)
- ✅ Country-specific validation rules
- ✅ Tax regime validation
- ✅ Complete fiscal information validation
- ✅ Suggestion generation for corrections

#### JavaScript Components
- ✅ Wizard navigation and step management
- ✅ Real-time validation engine
- ✅ Slug generation and sanitization
- ✅ Form state management and persistence
- ✅ Auto-save functionality
- ✅ Debounced validation calls

### Integration Tests Coverage

#### Wizard Flow Integration
- ✅ Complete wizard workflow (basic template)
- ✅ Complete wizard workflow (enterprise template)
- ✅ Template selection and configuration
- ✅ Real-time validation integration
- ✅ Draft management system
- ✅ Error handling and recovery
- ✅ State persistence across requests

#### Template Selection Integration
- ✅ Template retrieval and filtering
- ✅ Dynamic form generation
- ✅ Validation rules application
- ✅ Field mapping and dependencies
- ✅ Capability-based template selection

#### Draft Management Integration
- ✅ Draft creation and updates
- ✅ Draft retrieval and restoration
- ✅ User isolation and security
- ✅ Expiration and cleanup
- ✅ Concurrent access handling
- ✅ Large data handling

### End-to-End Tests Coverage

#### Complete User Workflows
- ✅ Basic store creation workflow
- ✅ Enterprise store creation workflow
- ✅ Real-time validation feedback
- ✅ Step navigation and state management
- ✅ Auto-save and draft recovery
- ✅ Error scenarios and recovery
- ✅ Network error handling
- ✅ Form validation and prevention

#### Advanced Features
- ✅ Billing calculation display
- ✅ Keyboard navigation support
- ✅ Tooltip and guidance systems
- ✅ Browser back/forward button handling

#### Accessibility Compliance
- ✅ Proper heading structure (h1-h6 hierarchy)
- ✅ Form labels and descriptions
- ✅ Error message association (aria-describedby)
- ✅ Keyboard navigation (Tab, Enter, Escape)
- ✅ Focus management and indicators
- ✅ ARIA live regions for announcements
- ✅ Screen reader support (roles, landmarks)
- ✅ Color contrast compliance
- ✅ Reduced motion preferences
- ✅ High contrast mode support
- ✅ Skip links and navigation aids
- ✅ Zoom level compatibility (up to 200%)

### Performance Tests Coverage

#### Response Time Requirements
- ✅ Email validation: < 200ms
- ✅ Slug validation: < 200ms
- ✅ Slug suggestions: < 500ms (even with many existing slugs)
- ✅ Billing calculations: < 300ms
- ✅ Location search: < 400ms
- ✅ Template endpoints: < 150ms

#### Scalability Tests
- ✅ Concurrent request handling (10+ simultaneous)
- ✅ Large dataset performance (1000+ records)
- ✅ Load testing (20+ sequential requests)
- ✅ Database query optimization
- ✅ Large form data handling (draft saves)

#### Error Handling Performance
- ✅ Malformed request handling (< 100ms)
- ✅ Validation error responses (< 100ms)
- ✅ Network error recovery

## Requirements Validation

Each test is mapped to specific requirements from the specification:

### Requirement 1: Intuitive UX and Wizard Flow
- **Tests**: `StoreCreationWizardTest`, `WizardFlowIntegrationTest`
- **Coverage**: Step navigation, progress indicators, form validation gates

### Requirement 2: Real-time Validation
- **Tests**: `StoreControllerValidationTest`, `ValidationPerformanceTest`
- **Coverage**: Email/slug validation, suggestion systems, error feedback

### Requirement 3: Template-based Workflows
- **Tests**: `TemplateSelectionIntegrationTest`, `StoreTemplateServiceTest`
- **Coverage**: Template selection, dynamic forms, conditional fields

### Requirement 4: Credential Management
- **Tests**: `StoreCreationWizardTest` (credential display scenarios)
- **Coverage**: Secure credential display, copy functionality, email delivery

### Requirement 5: Error Handling and Recovery
- **Tests**: `DraftManagementIntegrationTest`, `StoreCreationWizardTest`
- **Coverage**: Auto-save, draft recovery, error scenarios, state preservation

### Requirement 6: Billing Integration
- **Tests**: `StoreControllerValidationTest` (billing calculations)
- **Coverage**: Dynamic pricing, discount application, payment scheduling

### Requirement 7: Bulk Operations
- **Tests**: Integration tests for bulk import functionality
- **Coverage**: CSV import, batch processing, result reporting

## Test Data and Fixtures

### Factory Classes Used
- `User::factory()` - Creates test users with various roles
- `Store::factory()` - Creates test stores with different configurations
- `Plan::factory()` - Creates test plans with various features

### Test Database
- Uses SQLite in-memory database for fast test execution
- Automatic database refresh between tests
- Seeded with minimal required data

## Continuous Integration

### GitHub Actions Workflow
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.1
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: ./run-tests.sh
```

### Coverage Requirements
- **Unit Tests**: > 90% code coverage
- **Integration Tests**: > 80% feature coverage
- **End-to-End Tests**: 100% critical user path coverage

## Debugging Tests

### Common Issues and Solutions

#### Test Database Issues
```bash
# Reset test database
php artisan migrate:fresh --env=testing
```

#### Browser Test Failures
```bash
# Check Chrome/Chromium installation
php artisan dusk:chrome-driver

# Run with debugging
php artisan dusk --env=testing --debug
```

#### JavaScript Test Issues
```bash
# Clear Jest cache
npm test -- --clearCache

# Run with verbose output
npm test -- --verbose
```

### Test Debugging Tools

#### Laravel Telescope (Development)
- Monitor database queries during tests
- Track performance bottlenecks
- Debug validation logic

#### Browser Developer Tools
- Network tab for API call inspection
- Console for JavaScript errors
- Accessibility tab for WCAG compliance

## Best Practices

### Writing New Tests

1. **Follow AAA Pattern**: Arrange, Act, Assert
2. **Use Descriptive Names**: Test method names should describe the scenario
3. **Test One Thing**: Each test should verify a single behavior
4. **Use Factories**: Prefer factories over manual data creation
5. **Clean Up**: Ensure tests don't affect each other

### Performance Considerations

1. **Use Transactions**: Wrap database tests in transactions when possible
2. **Mock External Services**: Don't make real API calls in tests
3. **Optimize Queries**: Use `DB::enableQueryLog()` to monitor query count
4. **Parallel Execution**: Structure tests to run in parallel safely

### Accessibility Testing

1. **Automated Tools**: Use axe-core for automated accessibility testing
2. **Manual Testing**: Test with actual screen readers when possible
3. **Keyboard Navigation**: Verify all functionality works without mouse
4. **Color Contrast**: Use tools to verify WCAG AA compliance

## Maintenance

### Regular Tasks

1. **Update Test Data**: Keep factory data realistic and current
2. **Review Performance**: Monitor test execution times
3. **Update Dependencies**: Keep testing libraries up to date
4. **Coverage Analysis**: Review coverage reports monthly

### When Adding New Features

1. **Write Tests First**: Follow TDD when possible
2. **Update Integration Tests**: Ensure new features work with existing ones
3. **Add E2E Scenarios**: Cover new user workflows
4. **Performance Impact**: Test performance impact of new features

## Reporting Issues

When tests fail:

1. **Check Recent Changes**: Review recent commits for breaking changes
2. **Run Locally**: Verify the failure reproduces locally
3. **Check Dependencies**: Ensure all dependencies are up to date
4. **Review Logs**: Check Laravel logs and browser console for errors
5. **Create Detailed Reports**: Include steps to reproduce and environment details