#!/usr/bin/env sh

export COMPOSE_FILE=docker-compose-unit-es.yml

echo
echo
echo "Testing environment"
echo "==================="
echo "  COMPOSE_PROJECT_NAME:  ${COMPOSE_PROJECT_NAME}"
echo "  PHP_VERSION:           ${PHP_VERSION:-7.1}"
echo "  MYSQL_VERSION:         ${MYSQL_VERSION:-5.5}"
echo "  ELASTICSEARCH_VERSION: ${ELASTICSEARCH_VERSION}"
echo "  BEHAT_ARGS:            ${BEHAT_ARGS}"
echo "==================="
echo
echo

# Remove orphans from previous runs
docker-compose down -v --remove-orphans
docker-compose rm --force -v

# Update images
docker-compose pull

docker-compose up --remove-orphans  -d

echo "Wait for MySQL"
docker-compose run tools /tmp/wait.sh

# Enable elasticsearch in shopware
(head -n-1 ../config.php.dist && echo "    ,'es' => ['enabled' => true, 'number_of_replicas' => 0, 'client' => ['hosts' => ['elasticsearch.example:9200']]]" && tail -n 1 ../config.php.dist) | tee config.php.tmp; mv config.php.tmp ../config.php.dist

echo "Build unit"
docker-compose run -eANT_OPTS=-D"file.encoding=UTF-8" tools ant -f /var/www/html/build/build.xml build-unit -Dapp.path="" -Dapp.host="web.example" -Ddb.host="mysql.example" -Ddb.port=3306 -Ddb.name="shopware" -Ddb.user="shopware" -Ddb.password="shopware"

echo "Indexing to elasticsearch"
docker-compose run tools /var/www/html/bin/console sw:es:index:populate

echo "Run tests"
docker-compose run -eANT_OPTS=-D"file.encoding=UTF-8" tools ant -f /var/www/html/build/build.xml unit-continuous-es -Dapp.path="" -Dapp.host="web.example" -Ddb.host="mysql.example" -Ddb.port=3306 -Ddb.name="shopware" -Ddb.user="shopware" -Ddb.password="shopware"

echo "Run cleanup"
docker-compose run tools chown $(id -u):$(id -g) -R /var/www/html
docker-compose down -v --remove-orphans
docker-compose rm --force -v
