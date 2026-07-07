#!/bin/sh
# Runs pending migrations before the app serves. Safe against the shared
# database because migration history is tracked in this app's own table
# (config/database.php 'migrations' => 'migrations_login'), and safe against
# concurrent task launches via --isolated (cache lock). A migration failure
# exits non-zero so ECS keeps the previous tasks serving.
#
# The local schema dump (database/schema/mysql-schema.sql) is deliberately
# absent from this image (.dockerignore-php ships only *.php), so migrate
# never attempts a schema load here — it only applies real migrations.
set -e

cd /var/www/html
php artisan migrate --force --isolated

exec "$@"
