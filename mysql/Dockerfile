
FROM mysql:5.7
MAINTAINER JAL <jal65.dev@gmail.com>

RUN echo "sql_mode = STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION" >> /etc/mysql/mysql.conf.d/mysqld.cnf


ENTRYPOINT ["/entrypoint.sh"]
HEALTHCHECK CMD /healthcheck.sh

EXPOSE 3306
CMD ["mysqld"]
