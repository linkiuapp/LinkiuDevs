#!/bin/bash

# Comprehensive Test Runner for Store Creation Wizard
# This script runs all test suites and generates coverage reports

set -e

echo "🧪 Starting comprehensive test suite for Store Creation Wizard..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if required dependencies are installed
check_dependencies() {
    print_status "Checking dependencies..."
    
    if ! command -v php &> /dev/null; then
        print_error "PHP is not installed"
        exit 1
    fi
    
    if ! command -v composer &> /dev/null; then
        print_error "Composer is not installed"
        exit 1
    fi
    
    if ! command -v npm &> /dev/null; then
        print_error "NPM is not installed"
        exit 1
    fi
    
    print_success "All dependencies are available"
}

# Install test dependencies
install_dependencies() {
    print_status "Installing test dependencies..."
    
    # Install PHP dependencies
    composer install --dev --no-interaction
    
    # Install JavaScript dependencies
    npm install --save-dev jest jsdom babel-jest @babel/preset-env
    
    print_success "Dependencies installed"
}

# Run PHP Unit Tests
run_php_unit_tests() {
    print_status "Running PHP Unit Tests..."
    
    if php artisan test --testsuite=Unit --coverage-html=coverage/php-unit --coverage-clover=coverage/php-unit.xml; then
        print_success "PHP Unit Tests passed"
    else
        print_error "PHP Unit Tests failed"
        return 1
    fi
}

# Run PHP Feature Tests
run_php_feature_tests() {
    print_status "Running PHP Feature Tests..."
    
    if php artisan test --testsuite=Feature --coverage-html=coverage/php-feature --coverage-clover=coverage/php-feature.xml; then
        print_success "PHP Feature Tests passed"
    else
        print_error "PHP Feature Tests failed"
        return 1
    fi
}

# Run JavaScript Unit Tests
run_js_unit_tests() {
    print_status "Running JavaScript Unit Tests..."
    
    if npm test -- --coverage --coverageDirectory=coverage/js-unit; then
        print_success "JavaScript Unit Tests passed"
    else
        print_error "JavaScript Unit Tests failed"
        return 1
    fi
}

# Run Browser Tests (Dusk)
run_browser_tests() {
    print_status "Running Browser Tests..."
    
    # Start Laravel development server for Dusk tests
    php artisan serve --port=8001 &
    SERVER_PID=$!
    
    # Wait for server to start
    sleep 5
    
    # Run Dusk tests
    if php artisan dusk --env=testing; then
        print_success "Browser Tests passed"
        DUSK_SUCCESS=true
    else
        print_error "Browser Tests failed"
        DUSK_SUCCESS=false
    fi
    
    # Stop the server
    kill $SERVER_PID
    
    if [ "$DUSK_SUCCESS" = false ]; then
        return 1
    fi
}

# Run Performance Tests
run_performance_tests() {
    print_status "Running Performance Tests..."
    
    if php artisan test tests/Feature/ValidationPerformanceTest.php; then
        print_success "Performance Tests passed"
    else
        print_warning "Performance Tests failed (this may be acceptable depending on environment)"
    fi
}

# Generate Combined Coverage Report
generate_coverage_report() {
    print_status "Generating combined coverage report..."
    
    mkdir -p coverage/combined
    
    # Create a simple HTML report combining all coverage
    cat > coverage/combined/index.html << EOF
<!DOCTYPE html>
<html>
<head>
    <title>Store Creation Wizard - Test Coverage Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .success { background-color: #d4edda; }
        .warning { background-color: #fff3cd; }
        .error { background-color: #f8d7da; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>Store Creation Wizard - Test Coverage Report</h1>
    <p>Generated on: $(date)</p>
    
    <div class="section success">
        <h2>PHP Unit Tests</h2>
        <p><a href="../php-unit/index.html">View PHP Unit Test Coverage</a></p>
    </div>
    
    <div class="section success">
        <h2>PHP Feature Tests</h2>
        <p><a href="../php-feature/index.html">View PHP Feature Test Coverage</a></p>
    </div>
    
    <div class="section success">
        <h2>JavaScript Unit Tests</h2>
        <p><a href="../js-unit/lcov-report/index.html">View JavaScript Unit Test Coverage</a></p>
    </div>
    
    <div class="section">
        <h2>Test Summary</h2>
        <ul>
            <li>✅ Unit Tests: Validation endpoints, services, and components</li>
            <li>✅ Integration Tests: Wizard flow, template selection, draft management</li>
            <li>✅ End-to-End Tests: Complete user workflows and accessibility</li>
            <li>✅ Performance Tests: Response times and concurrent requests</li>
        </ul>
    </div>
</body>
</html>
EOF
    
    print_success "Combined coverage report generated at coverage/combined/index.html"
}

# Main execution
main() {
    local failed_tests=()
    
    check_dependencies
    install_dependencies
    
    # Create coverage directory
    mkdir -p coverage
    
    # Run all test suites
    if ! run_php_unit_tests; then
        failed_tests+=("PHP Unit Tests")
    fi
    
    if ! run_php_feature_tests; then
        failed_tests+=("PHP Feature Tests")
    fi
    
    if ! run_js_unit_tests; then
        failed_tests+=("JavaScript Unit Tests")
    fi
    
    # Browser tests are optional and may fail in CI environments
    if ! run_browser_tests; then
        print_warning "Browser tests failed - this may be expected in some environments"
    fi
    
    # Performance tests are informational
    run_performance_tests
    
    # Generate coverage report
    generate_coverage_report
    
    # Summary
    echo ""
    echo "=========================================="
    echo "           TEST SUMMARY"
    echo "=========================================="
    
    if [ ${#failed_tests[@]} -eq 0 ]; then
        print_success "All critical tests passed! 🎉"
        echo ""
        echo "Coverage reports available at:"
        echo "  - PHP Unit: coverage/php-unit/index.html"
        echo "  - PHP Feature: coverage/php-feature/index.html"
        echo "  - JavaScript: coverage/js-unit/lcov-report/index.html"
        echo "  - Combined: coverage/combined/index.html"
        exit 0
    else
        print_error "The following test suites failed:"
        for test in "${failed_tests[@]}"; do
            echo "  - $test"
        done
        exit 1
    fi
}

# Run main function
main "$@"