#!/bin/bash
set -e

echo "Creating testing database and user..."

mysql -u root -p"${MYSQL_ROOT_PASSWORD}" <<-EOSQL
    CREATE DATABASE IF NOT EXISTS testing;
    CREATE USER IF NOT EXISTS 'admin'@'%' IDENTIFIED BY 'admin';
    GRANT ALL PRIVILEGES ON testing.* TO 'admin'@'%';
    FLUSH PRIVILEGES;
EOSQL

echo "Testing database setup completed!"
