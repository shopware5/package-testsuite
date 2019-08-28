#!/usr/bin/env sh

export COMPOSE_PROJECT_NAME=$1
export COMPOSE_FILE=docker-compose-package.yml
echo "COMPOSE_PROJECT_NAME: ${COMPOSE_PROJECT_NAME}"

if [ "$1" = "" ]
then
    echo "Missing argument. Should be 'bamboo.buildResultKey'"
    exit 1
fi

export buildBranch=$(cd ../ && git rev-parse --abbrev-ref HEAD)
export currentReleasedVersion=$(curl -s 'http://update-api.shopware.com/v1/release/update?channel=stable&shopware_version=5.6' | python -c 'import sys, json; print json.load(sys.stdin)["version"]')
export packageVersion=${bamboo_PACKAGE_VERSION}
export packageTag="$bamboo_PACKAGE_TAG"

if [ "$bamboo_PACKAGE_VERSION" = "" ]
then
    export packageVersion="${currentReleasedVersion%.*}.$((${currentReleasedVersion##*.}+1))"
fi

if [ "$buildBranch" = "HEAD" ]
then
    export buildBranch="5.6"
fi

#if [ "$bamboo_PACKAGE_TAG" = "" ]
#then
#	export packageTag="NIGHTLY$(date +%Y%m%d)"
#fi

echo
echo "================================="
echo "        Shopware Build           "
echo "================================="
echo "Latest version: ${currentReleasedVersion}"
echo "Target version: ${packageVersion}"

if [ "$packageTag" != "" ]
then
    echo "Target tag:     ${packageTag}"
fi

echo "Source branch:  ${buildBranch}"
echo "================================="
echo

docker-compose down -v --remove-orphans
docker-compose rm --force -v
docker-compose pull
docker-compose up -d

echo "Wait for MySQL"
docker-compose run tools /tmp/wait.sh

echo "Running packge build"
docker-compose run -eANT_OPTS=-D"file.encoding=UTF-8" tools ant -f /source/deploy-script/build.xml install update -Ddb.host="mysql.example" -Ddb.port=3306 -Ddb.name="shopware" -Ddb.user="shopware" -Ddb.password="shopware" -Dgit.repo.url="/source/_build" -Dgit.checkout.branch=$buildBranch -Dgit.newtag=$packageVersion -Dgit.newtag_text=$packageTag

echo "Cleanup"
docker-compose run tools chown $(id -u):$(id -g) -R /source
docker-compose run tools chown $(id -u):$(id -g) -R /var/www/html
docker-compose down -v --remove-orphans
docker-compose rm --force -v
