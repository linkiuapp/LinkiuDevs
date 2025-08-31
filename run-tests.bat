@echo off
REM Comprehensive Test Runner for Store Creation Wizard (Windows)
REM This script runs all test suites and generates coverage reports

echo 🧪 Starting comprehensive test suite for Store Creation Wizard...

REM Check if PHP is available
php --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP is not installed or not in PATH
    exit /b 1
)

REM Check if Composer is available
composer --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Composer is not installed or not in PATH
    exit /b 1
)

REM Create coverage directory
if not exist coverage mkdir coverage

echo [INFO] Running PHP Unit Tests...
php artisan test --testsuite=Unit --coverage-html=coverage/php-unit --coverage-clover=coverage/php-unit.xml
if errorlevel 1 (
    echo [ERROR] PHP Unit Tests failed
    set "UNIT_FAILED=1"
) else (
    echo [SUCCESS] PHP Unit Tests passed
)

echo [INFO] Running PHP Feature Tests...
php artisan test --testsuite=Feature --coverage-html=coverage/php-feature --coverage-clover=coverage/php-feature.xml
if errorlevel 1 (
    echo [ERROR] PHP Feature Tests failed
    set "FEATURE_FAILED=1"
) else (
    echo [SUCCESS] PHP Feature Tests passed
)

echo [INFO] Running Performance Tests...
php artisan test tests/Feature/ValidationPerformanceTest.php
if errorlevel 1 (
    echo [WARNING] Performance Tests failed - this may be acceptable depending on environment
) else (
    echo [SUCCESS] Performance Tests passed
)

REM Generate simple coverage report
echo [INFO] Generating coverage report...
if not exist coverage\combined mkdir coverage\combined

echo ^<!DOCTYPE html^> > coverage\combined\index.html
echo ^<html^> >> coverage\combined\index.html
echo ^<head^> >> coverage\combined\index.html
echo     ^<title^>Store Creation Wizard - Test Coverage Report^</title^> >> coverage\combined\index.html
echo     ^<style^> >> coverage\combined\index.html
echo         body { font-family: Arial, sans-serif; margin: 20px; } >> coverage\combined\index.html
echo         .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; } >> coverage\combined\index.html
echo         .success { background-color: #d4edda; } >> coverage\combined\index.html
echo         a { color: #007bff; text-decoration: none; } >> coverage\combined\index.html
echo     ^</style^> >> coverage\combined\index.html
echo ^</head^> >> coverage\combined\index.html
echo ^<body^> >> coverage\combined\index.html
echo     ^<h1^>Store Creation Wizard - Test Coverage Report^</h1^> >> coverage\combined\index.html
echo     ^<div class="section success"^> >> coverage\combined\index.html
echo         ^<h2^>PHP Unit Tests^</h2^> >> coverage\combined\index.html
echo         ^<p^>^<a href="../php-unit/index.html"^>View PHP Unit Test Coverage^</a^>^</p^> >> coverage\combined\index.html
echo     ^</div^> >> coverage\combined\index.html
echo     ^<div class="section success"^> >> coverage\combined\index.html
echo         ^<h2^>PHP Feature Tests^</h2^> >> coverage\combined\index.html
echo         ^<p^>^<a href="../php-feature/index.html"^>View PHP Feature Test Coverage^</a^>^</p^> >> coverage\combined\index.html
echo     ^</div^> >> coverage\combined\index.html
echo ^</body^> >> coverage\combined\index.html
echo ^</html^> >> coverage\combined\index.html

echo.
echo ==========================================
echo            TEST SUMMARY
echo ==========================================

if defined UNIT_FAILED (
    echo [ERROR] Unit Tests failed
    set "HAS_FAILURES=1"
)

if defined FEATURE_FAILED (
    echo [ERROR] Feature Tests failed
    set "HAS_FAILURES=1"
)

if defined HAS_FAILURES (
    echo [ERROR] Some tests failed
    echo Coverage reports available at:
    echo   - PHP Unit: coverage\php-unit\index.html
    echo   - PHP Feature: coverage\php-feature\index.html
    echo   - Combined: coverage\combined\index.html
    exit /b 1
) else (
    echo [SUCCESS] All critical tests passed! 🎉
    echo.
    echo Coverage reports available at:
    echo   - PHP Unit: coverage\php-unit\index.html
    echo   - PHP Feature: coverage\php-feature\index.html
    echo   - Combined: coverage\combined\index.html
    exit /b 0
)