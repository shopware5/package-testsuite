FROM mysql:5.7

# copy mysql config
ADD conf.d/my-custom.cnf /etc/mysql/conf.d/my-custom.cnf

COPY grant.sql /docker-entrypoint-initdb.d/grant.sql
