#!/bin/bash

# 🧹 SCRIPT PARA LIMPIAR ARCHIVOS DEBUG/TEST EN VPS
# Ejecutar desde: /home/wwlink/linkiubio_app/

echo "🧹 Iniciando limpieza de archivos debug/test en VPS..."

# ========================
# 📁 ARCHIVOS PHP DE DEBUG/TEST
# ========================

echo "📁 Eliminando archivos PHP de debug/test..."

# Scripts de verificación/debug
rm -f check_tables.php
rm -f check-db-config.php
rm -f clean_db_alt.php
rm -f clean_db.php
rm -f create_new_db.php
rm -f create-email-config.php
rm -f debug_avatar.php
rm -f debug-detailed-comparison.php
rm -f debug-email-config.php
rm -f debug-panel-email.php
rm -f debug-web-email.php
rm -f email-diagnostic.php
rm -f fix_tables.php
rm -f recreate_db_alt.php
rm -f recreate_db.php
rm -f recreate_main_tables.php
rm -f test_db.php
rm -f test-exact-replication.php
rm -f test-upload.php
rm -f update-email-settings.php

echo "✅ Archivos PHP de debug eliminados"

# ========================
# 🚀 SCRIPTS DE SETUP/DEPLOY
# ========================

echo "🚀 Eliminando scripts de setup/deploy obsoletos..."

rm -f fix-email-production.bat
rm -f fix-email-production.sh
rm -f fix-production-permissions.sh
rm -f setup-env-vps.sh
rm -f vps-setup.sh
rm -f vps-setup-almalinux.sh
rm -f deploy.sh
rm -f deploy-linkiubio.sh

echo "✅ Scripts de setup/deploy eliminados"

# ========================
# 🧪 SCRIPTS DE TESTING
# ========================

echo "🧪 Eliminando scripts de testing..."

rm -f run-tests.bat
rm -f run-tests.sh
rm -f tests/run-email-tests.bat

echo "✅ Scripts de testing eliminados"

# ========================
# 📄 DOCUMENTACIÓN TEMPORAL
# ========================

echo "📄 Eliminando documentación temporal..."

rm -f DEPLOY_CHECKLIST.md
rm -f DEPLOY_COMPLETO.md
rm -f DEPLOY_DEBUG_SYSTEM.md
rm -f DEPLOY_TEST.md
rm -f DEPLOYMENT_CHECKLIST.md
rm -f INFORME-DEPLOYMENT-VPS.md
rm -f INFORME-ESTRUCTURA-PROYECTO-VPS.md
rm -f install-debug-system.md
rm -f MICROSOFT_365_EMAIL_CONFIG.md
rm -f FLUJO-DESARROLLO.md
rm -f ESTANDAR_IMAGENES.md

echo "✅ Documentación temporal eliminada"

# ========================
# 🌐 ARCHIVOS HTML DE TEST
# ========================

echo "🌐 Eliminando archivos HTML de test..."

rm -f public/test-enhanced-validation.html
rm -f public/test-fiscal-information.html
rm -f public/test-store-configuration.html

echo "✅ Archivos HTML de test eliminados"

# ========================
# 🔧 ARCHIVOS JAVASCRIPT DE DEBUG
# ========================

echo "🔧 Eliminando archivos JavaScript de debug..."

rm -f QUICK_DEBUG_TEST.js

echo "✅ Archivos JavaScript de debug eliminados"

# ========================
# 📧 ARCHIVOS DE CONFIG TEMPORAL
# ========================

echo "📧 Eliminando archivos de configuración temporal..."

rm -f PRODUCTION_EMAIL_CONFIG.env

echo "✅ Archivos de configuración temporal eliminados"

# ========================
# 📊 RESUMEN DE LIMPIEZA
# ========================

echo ""
echo "🎉 ¡LIMPIEZA COMPLETADA!"
echo "📊 Resumen de archivos eliminados:"
echo "   📁 Archivos PHP de debug/test: 20"
echo "   🚀 Scripts de setup/deploy: 8" 
echo "   🧪 Scripts de testing: 3"
echo "   📄 Documentación temporal: 11"
echo "   🌐 Archivos HTML de test: 3"
echo "   🔧 Archivos JS de debug: 1"
echo "   📧 Configs temporales: 1"
echo "   ✨ Total eliminados: ~47 archivos"
echo ""
echo "📋 Archivos CONSERVADOS (importantes):"
echo "   ✅ README.md - Documentación principal"
echo "   ✅ SECURITY_IMPLEMENTATION.md - Seguridad"
echo "   ✅ FEATURE_ARCHITECTURE.md - Arquitectura"
echo "   ✅ DEPLOYMENT_GUIDE_LGV.md - Guía actual"
echo "   ✅ DEPLOYMENT_VPS_RULES.md - Reglas actuales"
echo "   ✅ CHANGELOG.md - Historial de cambios"
echo "   ✅ tests/ - Framework de testing"
echo "   ✅ app/Console/Commands/ - Comandos del sistema"
echo ""
echo "🔄 Recomendación: Reinicia Apache/nginx después de la limpieza"
echo "🗂️  Recomendación: Haz backup antes de ejecutar este script"
