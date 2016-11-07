# Shopware Package Tests
> Release package testing suite for Shopware 

This project aims to automate QA work.
It consists of docker containers to automatically install and test
a shopware installation package from zipfile or release tag.

## Installing / Getting started

This project is build on and for Linux systems.
You need [docker](https://docs.docker.com/engine/installation/linux/) and [docker compose](https://docs.docker.com/compose/) installed on the system.

Then you can setup and run the project (after cloning of course):

```shell
cp tests/.env.dist /tests/.env
cp tests/behat.yml.dist /tests/behat.yml
```

The `.env` and `behat.yml` are filled with defaults, you won't need to change them unless
you're changing the way the project behaves, e.g. by not testing from zip/release tag or by
using your own asset generator.

After copying the config files and, if needed, editing them, you can start the testing.   
All there is to do is calling `run.sh` and specifying either a locally available zip file, a remote zipfile or a release tag:
 
```
./run.sh --zipfile=/path/to/zip/file.zip
``` 
```
./run.sh --url=http://host.tld/path/to/zip/file.zip
```

> Make sure the zipfile is a Shopware install package.

```
./run.sh --release=5.2.0
```

## Features

The package test suite consists of the following docker containers, defined in `docker/docker-compose.yml`:
* apache
* behat
* mysql
* selenium
* smtp

The tests live inside the folder `tests/`, the asset generator is located in `assetgenerator/`.
The asset generator is used for creating random images with a text printed on it. That way,
products created during tests can easily be decorated with images.
 
The asset generator takes an URL in the format `http://host/WidthxHeight/SomeText.extension`.   
Example: `http://assetgenerator/800x600/MyProduct.jpg`. The size parameter is optional.

The containers in detail:

#### Apache

The container is build from a `Dockerfile` located in `docker/build-apache`.

It is based on [`php:7-apache`](https://hub.docker.com/_/php/).   
The container is used for serving the Shopware installation that is tested as well as the integrated asset generator.

The shopware installation is expected to live inside `/var/www/shopware` in the container, the asset generator
is mounted inside `/var/www/assetgenerator`.

The container does not export any ports to the host.

#### behat

The container is build from a `Dockerfile` located in `docker/build-behat`.

It is based on [`php:7-cli`](https://hub.docker.com/_/php/).   
Inside this container the actual testsuite is executed. It is connected to the Apache container,
which is accessible via the host `shopware.localhost` from inside the behat container.

The `selenium` and `smtp` containers are accessible via their names as hostname.

The container does not export any ports to the host.

#### mysql

The container is build from a `Dockerfile` located in `docker/build-mysql`.

It is based on [`mariadb`](https://hub.docker.com/_/mariadb/).   
On creation the database `shopware` with user `shopware` and password `shopware` is created.
This database is later used for hosting the Shopware installation.

Admin account is `root` with password `toor`, although there should be no reason to use this account during testing.

The container does not export any ports to the host.

#### selenium

The container is build from a `Dockerfile` located in `docker/build-selenium`.

It is based on [`selenium-standalone-firefox/`](https://hub.docker.com/u/selenium/).   
On creation the database `shopware` with user `shopware` and password `shopware` is created.
This database is later used for hosting the Shopware installation.

Admin account is `root` with password `toor`, although there should be no reason to use this account during testing.

The container does not export any ports to the host.

#### smtp

The container uses the [`mailhog/mailhog`](https://hub.docker.com/r/mailhog/mailhog/) image.   
The apache container is configured to send all mails to the mailhog container.
No e-mails are sent to the outside. Mailhog comes with [an API](https://github.com/mailhog/MailHog/blob/master/docs/APIv2.md) which can be used in tests. 

The container does not export any ports to the host.

## Configuration

#### tests/.env

You may need to adjust the parameter `assets_url` if you're not using the built in container structure.

#### tests/behat.yml

You may need to adjust the parameters `base_url` and `wd_host` if you're not using the built in container structure.
