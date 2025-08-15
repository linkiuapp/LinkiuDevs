# 🚀 CHECKLIST DEPLOY PRODUCCIÓN

## ✅ PRE-DEPLOY (Local)
- [ ] `npm run build` ejecutado
- [ ] NO existe archivo `public/hot`
- [ ] Todos los templates usan `@vite()` en lugar de CDN
- [ ] Cambios probados en localhost

## ✅ DEPLOY (Servidor)
- [ ] Backup creado
- [ ] Archivos subidos
- [ ] `composer install --no-dev --optimize-autoloader` 
- [ ] `php artisan view:clear`
- [ ] `php artisan config:clear`
- [ ] Eliminar `public/hot` si existe
- [ ] Verificar enlaces simbólicos:
  - `/var/www/html/build` → `/home/wwlink/linkiubio_app/public/build`
  - `/var/www/html/storage` → `/home/wwlink/linkiubio_app/storage/app/public`
- [ ] Permisos: `chown -R nobody:nobody` y `chmod 755/775`

## ✅ VERIFICACIÓN POST-DEPLOY
- [ ] `curl -I https://linkiu.bio` → 200 OK
- [ ] `curl -I https://linkiu.bio/superlinkiu/login` → 200 OK  
- [ ] `curl -I https://linkiu.bio/build/assets/app-*.css` → 200 OK
- [ ] Página carga con estilos correctos
- [ ] NO aparece "tailwindcss.com" en el source
- [ ] SÍ aparece "build/assets/app-*.css" en el source

## 🚨 COMANDOS DE EMERGENCIA
```bash
# Si faltan estilos:
rm -f /home/wwlink/linkiubio_app/public/hot
php artisan view:clear
ln -sf /home/wwlink/linkiubio_app/public/build /var/www/html/build

# Verificar assets:
curl -s 'https://linkiu.bio/superlinkiu/login' | grep -E 'css|app-'
```
