#!/usr/bin/env bash

## backup
docker exec -it dockersendy3_mysql-sendy_1 /usr/bin/mysqldump --opt -u root -p'RiNoLio131' --databases emkt_sendy > emkt_sendy_3040.sql
