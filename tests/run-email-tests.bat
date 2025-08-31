@echo off
echo Running Email Configuration System Test Suite
echo =============================================

echo.
echo Running Unit Tests...
echo ---------------------
php artisan test tests/Unit/EmailSettingTest.php tests/Unit/EmailTemplateTest.php tests/Unit/EmailServiceTest.php tests/Unit/EmailSecurityServiceTest.php

echo.
echo Running Feature Tests (Template Variable Replacement)...
echo --------------------------------------------------------
php artisan test tests/Feature/TemplateVariableReplacementTest.php

echo.
echo Running Feature Tests (Error Handling)...
echo -----------------------------------------
php artisan test tests/Feature/EmailErrorHandlingTest.php

echo.
echo Running Feature Tests (Validation and Security)...
echo --------------------------------------------------
php artisan test tests/Feature/EmailValidationAndSecurityTest.php

echo.
echo Running Integration Tests...
echo ----------------------------
php artisan test tests/Feature/EmailTemplateIntegrationTest.php

echo.
echo Running Existing Email Tests...
echo -------------------------------
php artisan test tests/Feature/EmailSystemIntegrationTest.php tests/Feature/EmailSecurityTest.php

echo.
echo Test Suite Complete!
echo ====================