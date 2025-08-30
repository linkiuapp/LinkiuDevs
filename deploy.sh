#!/bin/bash

# Deploy script para LinkiuBio
# Uso: ./deploy.sh

echo "🚀 Iniciando deploy a producción..."

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuración VPS
VPS_HOST="162.240.163.188"
VPS_USER="wwlink"
VPS_PORT="22022"
VPS_PATH="/home/wwlink/linkiubio_app"

# Verificar que estamos en la rama correcta
BRANCH=$(git branch --show-current)
echo -e "${YELLOW}Rama actual: $BRANCH${NC}"

if [ "$BRANCH" != "main" ] && [ "$BRANCH" != "master" ]; then
    read -p "¿Estás seguro de deployar desde la rama '$BRANCH'? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${RED}Deploy cancelado${NC}"
        exit 1
    fi
fi

# Verificar cambios sin commitear
if [ -n "$(git status --porcelain)" ]; then
    echo -e "${RED}Tienes cambios sin commitear. Haz commit primero.${NC}"
    exit 1
fi

# Push a GitHub
echo -e "${YELLOW}📤 Subiendo cambios a GitHub...${NC}"
git push origin $BRANCH

# Sync archivos al VPS (excluyendo archivos innecesarios)
echo -e "${YELLOW}📁 Sincronizando archivos al VPS...${NC}"
rsync -avz --delete \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='.env' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    -e "ssh -p $VPS_PORT" \
    ./ $VPS_USER@$VPS_HOST:$VPS_PATH/

# Ejecutar comandos en el servidor
echo -e "${YELLOW}⚙️  Ejecutando comandos en el servidor...${NC}"
ssh -p $VPS_PORT $VPS_USER@$VPS_HOST << EOF
    cd $VPS_PATH
    
    # Instalar dependencias
    composer install --no-dev --optimize-autoloader --no-interaction
    
    # Limpiar y optimizar cache
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    
    # Optimizar para producción
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Ejecutar migraciones
    php artisan migrate --force
    
    # Reiniciar queue workers
    php artisan queue:restart
    
    # Recargar nginx
    sudo systemctl reload nginx
    
    echo "✅ Deploy completado en el servidor"
EOF

echo -e "${GREEN}🎉 Deploy completado exitosamente!${NC}"
echo -e "${GREEN}🌐 Tu sitio está disponible en: https://linkiu.bio${NC}"