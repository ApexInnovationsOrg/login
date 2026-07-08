#!/usr/bin/env bash
# Runs on first initialization of the MySQL data volume:
# creates the database used by the phpunit suite.
mysql --user=root --password="$MYSQL_ROOT_PASSWORD" <<-EOSQL
    CREATE DATABASE IF NOT EXISTS apex_login_test;
    GRANT ALL PRIVILEGES ON \`apex_login_test\`.* TO '$MYSQL_USER'@'%';
EOSQL
