#!/bin/bash
# MySQL initialization script for TokenLite

echo "Initializing TokenLite database..."

# Set timezone
mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root -p"$MYSQL_ROOT_PASSWORD" mysql

# Create additional configurations if needed
mysql -u root -p"$MYSQL_ROOT_PASSWORD" <<-EOSQL
    -- Set up database character set and collation
    ALTER DATABASE ${MYSQL_DATABASE} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    
    -- Grant additional privileges if needed
    GRANT ALL PRIVILEGES ON ${MYSQL_DATABASE}.* TO '${MYSQL_USER}'@'%';
    FLUSH PRIVILEGES;
    
    -- Set MySQL settings for optimal Laravel performance (MySQL 8.0 compatible)
    SET GLOBAL innodb_default_row_format = 'dynamic';
    SET GLOBAL innodb_file_per_table = 'ON';
EOSQL

echo "Database initialization completed."