#!/usr/bin/env bash

# Creates a compressed file of uploads on the workir of container
docker exec -it dockersendy3_sendy_1 tar -cvpzf uploads.tar.gz /var/www/site/uploads/

# Copy file from container to the hostcomputer
docker cp dockersendy3_sendy_1:uploads.tar.gz .
