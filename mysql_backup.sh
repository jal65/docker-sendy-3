#!/usr/bin/env bash

## backup
docker exec -it dockersendy3_mysql-sendy_1 /usr/bin/mysqldump --opt -u root -p<password> --databases <db_sendy> > <db_sendy_backup.sql>
