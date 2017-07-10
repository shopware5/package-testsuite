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

## Writing Tests

### How to use the XpathBuilder

The `Shopware\Component\XpathBuilder` namespace contains a few classes that can be helpful when writing tests that rely 
on complicated xpath queries. It is recommended to use either the `FrontendXpathBuilder` or the `BackendXpathBuilder`, 
which both inherit from the `BaseXpathBuilder` but additionally provide useful shortcuts for often-needed functionality, 
like e.g. selecting an ExtJS window by its title.


#### Using the BaseXpathBuilder

The XpathBuilder can be used with a fluent, method-chain-style interface:
```
<?php

use Shopware\Component\XpathBuilder\BaseXpathBuilder;

$builder = new BaseXpathBuilder();

/*  Would match the following:
 *
 *      <ul class="x-dropdown col-xs-12">
 *          <li>Demo Text</li>
 *      </ul>
 */
$builder
    ->child('li', ['~text' => 'Demo Text'])
    ->ancestor('ul', ['~class' => 'x-dropdown'], 1)
    ->getXpath();

```
The BaseXpathBuilder comes pre-configured with a single `/` as its path. There is *no implicit resetting* with the base builder. 
If you configured a path and want to reuse the same Builder-instance, you need to call `$builder->reset()` on it again in 
order to reset the path to its default value. It is recommended that you explicitly call `->reset()` explicitly at the 
beginning of every xpath build to mitigate any possible, hard-to-debug mistakes.

All builder support building xpaths fluently by chaining calls such as `->child([...])`, `->descendant([...])` or 
`->followingSibling([...])`. Those public methods all have the same signature of `->child($tag, $predicates, $index)`, with
the latter two being optional.

To retrieve the currently configured xpath, call the `->getXpath()` method. Please note that in contrast to the LegacyXpathBuilder,
the BaseXpathBuilder() *does not* implicitly reset the builder when you get the path. 

There is a static shorthand method useful for creating xpaths inline:
```
<?php

use Shopware\Component\XpathBuilder\BaseXpathBuilder;

$iconXpath = BackendXpathBuilder::create()->child('img', ['@class' => 'icon-smiley'])->getXpath();
```

#### Using the BackendXpathBuilder

The BackendXpathBuilder class has some helpful shorthand methods that provide easy access to common xpaths. They can
be accessed statically and always return the resulting Xpath as a ready-to-use xpath string.

The following (non exhaustive) list contains some of these methods:
```
<?php

use Shopware\Component\XpathBuilder\BackendXpathBuilder;

[...]

$window = $this->find('xpath', BackendXpathBuilder::getWindowXpathByTitle('Kundenverwaltung'));

$textInput = $this->find('xpath', BackendXpathBuilder::getInputXpathByLabel('Email:'));

$textarea = $this->find('xpath', BackendXpathBuilder::getFormElementXpathByLabel('Kommentar:'));

$combobox = $this->find('xpath', BackendXpathBuilder::getComboboxXpathByLabel('Steuersatz:'));

$saveButton = $this->find('xpath', BackendXpathBuilder::getButtonXpathByLabel('Speichern'));
```
