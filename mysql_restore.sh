#!/usr/bin/env bash

## restore
docker exec -i dockersendy3_mysql-sendy_1 /usr/bin/mysql -u root -p'RiNoLio131'  --force < emkt_sendy_3040.sql
