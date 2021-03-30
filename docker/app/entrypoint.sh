#!/usr/bin/env sh

chown -R www-data:www-data db var
php -d memory_limit=256M bin/console cache:clear
php -d memory_limit=256M bin/console app:index
chown -R www-data:www-data db var

apache2-foreground
