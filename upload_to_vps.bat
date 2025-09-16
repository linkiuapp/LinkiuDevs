@echo off
echo Subiendo archivos al VPS...

echo 1. Subiendo SimpleShipping.php...
scp -P 22022 app/Features/TenantAdmin/Models/SimpleShipping.php wwlink@162.240.163.188:/home/wwlink/linkiubio_app/app/Features/TenantAdmin/Models/

echo 2. Subiendo SimpleShippingZone.php...
scp -P 22022 app/Features/TenantAdmin/Models/SimpleShippingZone.php wwlink@162.240.163.188:/home/wwlink/linkiubio_app/app/Features/TenantAdmin/Models/

echo 3. Subiendo SimpleShippingController.php...
scp -P 22022 app/Features/TenantAdmin/Controllers/SimpleShippingController.php wwlink@162.240.163.188:/home/wwlink/linkiubio_app/app/Features/TenantAdmin/Controllers/

echo 4. Subiendo vista index.blade.php...
scp -P 22022 app/Features/TenantAdmin/Views/simple-shipping/index.blade.php wwlink@162.240.163.188:/home/wwlink/linkiubio_app/app/Features/TenantAdmin/Views/simple-shipping/

echo 5. Subiendo migraciones...
scp -P 22022 database/migrations/2025_09_15_200131_create_simple_shipping_table.php wwlink@162.240.163.188:/home/wwlink/linkiubio_app/database/migrations/
scp -P 22022 database/migrations/2025_09_15_200214_create_simple_shipping_zones_table.php wwlink@162.240.163.188:/home/wwlink/linkiubio_app/database/migrations/
scp -P 22022 database/migrations/2025_09_15_214706_migrate_to_simple_shipping_system.php wwlink@162.240.163.188:/home/wwlink/linkiubio_app/database/migrations/

echo 6. Subiendo seeder...
scp -P 22022 database/seeders/SimpleShippingProductionSeeder.php wwlink@162.240.163.188:/home/wwlink/linkiubio_app/database/seeders/

echo 7. Subiendo comando artisan...
scp -P 22022 app/Console/Commands/DeployShippingSystem.php wwlink@162.240.163.188:/home/wwlink/linkiubio_app/app/Console/Commands/

echo 8. Subiendo sidebar actualizado...
scp -P 22022 app/Shared/Views/Components/admin/tenant-sidebar.blade.php wwlink@162.240.163.188:/home/wwlink/linkiubio_app/app/Shared/Views/Components/admin/

echo Archivos subidos. Ahora conectate al VPS y ejecuta:
echo ssh -p 22022 wwlink@162.240.163.188
echo cd /home/wwlink/linkiubio_app
echo php artisan shipping:deploy

pause

