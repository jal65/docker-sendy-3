#!/usr/bin/env bash

## restore
docker exec -i dockersendy3_mysql-sendy_1 /usr/bin/mysql -u root -p<password>  --force < <db_sendy.sql>
