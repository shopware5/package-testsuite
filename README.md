Shopware Package Test Suite
===========================

This project automates rudimentary QA tasks to ensure the most basic features of the tested Shopware version all work
as expected.

## Installation
This project is only supported on Linux and requires that both [docker](https://docs.docker.com/engine/installation/linux/) 
and [docker compose](https://docs.docker.com/compose/) are available on the system.

Clone the project and copy the following .dist files:

```bash
cp tests/.env.dist /tests/.env
cp tests/behat.yml.dist /tests/behat.yml
```

Most of the time, the defaults provided in both files will work just fine. If you, for whatever reason, need to change
the asset generator some behat configuration, feel free to adjust your local copies accordingly.

The package test suit expects an install-package and an update-package in the `./files` directory. The packages must be
named to conform to `*_install_*_latest.zip` and `*_update_*_latest.zip` respectively.

## Running tests
Running the tests is as easy as calling the appropriate shell scripts in the `./docker` directory. Please note that these
scripts are intended to be run by a Bamboo agent, so they do expect a Bamboo build key as the first argument. For local
testing purposes, you can simply pass any arbitrary string as an argument such as:

* `./stage_installer.sh testing` Tests install functionality
* `./stage_updater.sh testing` Tests update functionality
* `./stage_general.sh testing` Regular shop functionality

## Running single features
For local development, it is very useful to be able to run single features or tests without running a whole stage. That
is what the `./docker/_local_manual_testing.sh` script is for. It sets up the same environment the stages execute in but
doesn't execute a single test on its own. After calling the script you can run specific features manually. It is recommended
to use an alias like the following:
 
 ```bash
alias runmink='docker-compose -f docker-compose.yml -f docker-compose.local.yml run --rm tools ./behat $1 --format=pretty --out=std --format=junit --out=/logs/mink'
 ```

 Then you can run a specific feature by simply calling:

 ```bash
runmink /tests/features/backend_customers.feature
 ```
 
 
## Debugging and Development

### Remote debugging using VNC Sessions
When run in development mode (by using `./docker/_local_manual_testing.sh`), Selenium is run in debug mode and exposes
port 5900. Is is necessary to forward this port from the docker container to the host (your development machine), which
is already done in `./docker/docker-compose.local.yml`. That means if you use the alias supplied above for running your
tests, you can use any VNC client to connect to `localhost:5900`. When prompted for a password, enter `secret`.

#### Note for Windows user
If you develop on Windows and run the test suite in a virtual machine, make sure you also forward the port from you VM
to your host machine. With Vagrant, add the following port forwarding rule to your Vagrantfile and restart your VM:

```
    config.vm.network "forwarded_port", guest: 5900, host: 5900
```

## Writing Tests

### What goes where?
The `*.feature` files should only contain human-readable, english sentences, assumptions and actions.

The `*Context.php` files all live in the `Shopware\Context` namespace and may only contain step definitions for
steps used the feature files. All logic should be handled by the `Pages`. Step definitions should be short and expressive. 
Only use regular expressions in step name definitions when absolutely necessary. Most of the time it might be better to
refactor steps into smaller sub-steps that only handle one single functionality.

### Using Tags

Additional functionality for tests can be enabled by tagging either single scenarios or whole features
with special tags.

#### @knownFailing
This tag prevents features from being tested completely, useful to be able to commit WIP features
that would otherwise cause a breaking CI build.

#### @isolated
When a scenario is tagged with this tag, the database gets wiped before and after the scenario is run.
Per default, the database is being reset to a clean state after every feature.

### How to use the XpathBuilder
The `Shopware\Component\XpathBuilder` namespace contains a few classes that can be helpful when writing tests that rely 
on complicated xpath queries. It is recommended to use either the `FrontendXpathBuilder` or the `BackendXpathBuilder`, 
which both inherit from the `BaseXpathBuilder` but additionally provide useful shortcuts for often-needed functionality, 
like e.g. selecting an ExtJS window by its title.

#### Using the BaseXpathBuilder
The XpathBuilder can be used with a fluent, method-chain-style interface:

```php
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

```php
<?php

use Shopware\Component\XpathBuilder\BackendXpathBuilder;

$iconXpath = BackendXpathBuilder::create()->child('img', ['@class' => 'icon-smiley'])->getXpath();
```

#### Using the BackendXpathBuilder
The BackendXpathBuilder class has some helpful shorthand methods that provide easy access to common xpaths. They can
be accessed statically and always return the resulting Xpath as a ready-to-use xpath string.

The following (non exhaustive) list contains some of these methods:

```php
<?php

use Shopware\Component\XpathBuilder\BackendXpathBuilder;

// [...]

$window = $this->find('xpath', BackendXpathBuilder::getWindowXpathByTitle('Kundenverwaltung'));

$textInput = $this->find('xpath', BackendXpathBuilder::getInputXpathByLabel('Email:'));

$textarea = $this->find('xpath', BackendXpathBuilder::getFormElementXpathByLabel('textarea', 'Kommentar:'));

$combobox = $this->find('xpath', BackendXpathBuilder::getComboboxXpathByLabel('Steuersatz:'));

$saveButton = $this->find('xpath', BackendXpathBuilder::getButtonXpathByLabel('Speichern'));

// [...]

```

### Using the asset generator
The package test suite comes pre-configured with a simple asset generator that can generate random images with fixed
sizes and text printed on them. The asset generator is available over HTTP at `http://assetgenerator/`:
 ```
 http://assetgenerator/800x600/MyProduct.jpg
 ```

Please note that the size parameter is completely optional.

## Docker Container Reference

### Apache container
* Runs PHP7 and Apache Server
* Serves Shopware installation from `/var/www/shopware`
* Serves asset generator from `/var/www/assetgenerator`

### Tools container
* Based on PHP7 CLI Docker image
* Place where the actual test suite is executed
* Linked to apache container via host `shopware.localhost`
* Linked to selenium container via host `selenium`
* Linked to smtp container via host `smtp`

### MySQL container
* Provides mariaDB database named `shopware` for user `shopware` with password `shopware`
* Root access for user `root` with password `toor`

### Selenium container
* Based on selenium-chrome Docker image
* Exposes port 5900 in development mode (`docker-compose.local.yml`) for remote debugging via VNC

### SMTP container
* Based on mailhog Docker image
* Configured to receive all mails from apache container
* Refer to [Mailhog's API Documentation](https://github.com/mailhog/MailHog/blob/master/docs/APIv2.md)