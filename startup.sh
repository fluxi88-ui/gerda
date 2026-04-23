#!/bin/bash
# Azure App Service Linux – PHP + nginx startup
# Diese Datei in Azure Portal unter: Configuration > General Settings > Startup Command eintragen:
# /home/site/wwwroot/startup.sh

cp /etc/nginx/sites-available/default /etc/nginx/sites-available/default.bak

# PHP-FPM sicherstellen
service php8.3-fpm start 2>/dev/null || service php-fpm start 2>/dev/null || true

echo "Startup done"
