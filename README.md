Shopware Package Test Suite
===========================

This project automates rudimentary QA tasks to ensure the most basic features of the tested Shopware version all work as expected.

## Installation

Clone the project and copy the following .dist files:

```bash
cp tests/.env.dist tests/.env
cp tests/behat.yml.dist tests/behat.yml
```

Most of the time, the defaults provided in both files will work just fine.
If you, for whatever reason, need to change the asset generator some behat configuration,
feel free to adjust your local copies accordingly.

The package test suit expects an install-package and an update-package in the `./files` directory.
The packages must be named to conform to `*_install_*_latest.zip` and `*_update_*_latest.zip` respectively.

Before executing the scripts locally, you need to login with docker on gitlab.shopware.com
`docker login gitlab.shopware.com:5005`
Create a token in your personal gitlab area and use it as password.

## Running tests

- Execute `composer install` in `tests`.
- Make sure that you have installed Selenium on your maschine like described here: https://github.com/shopware/shopware/tree/5.7/tests/Mink#selenium
- Adjust the value `assets_url` in the `tests/.env` file
  - It needs the path to the image generator shipped with this repository
  - it could look like this: `http://localhost/package-testsuite/www/assetgenerator/`
- Adjust the value `api_key` in the `tests/.env` file
  - It needs to be the API key of the admin user "demo" of your local shop installation
- Adjust the value `default.extensions.Behat\MinkExtension.base_url` in the `tests/behat.yml` file
  - Set it to a path of your local Shopware installation like `http://localhost/5.7/`
- Adjust the value `default.extensions.Behat\MinkExtension.selenium2.wd_host` in the `tests/behat.yml` file
  - If you execute Selenium directly on your maschine this is for example: `http://localhost:4444/wd/hub`

Now you can execute `./behat` in the `tests` directory for the whole test suite.
Or use `./behat features/frontend_account.feature` for a specific feature.

## Writing Tests

### What goes where?

The `*.feature` files should only contain human-readable, english sentences, assumptions and actions.

The `*Context.php` files all live in the `Shopware\Context` namespace
and may only contain step definitions for steps used the feature files.
All logic should be handled by the `Pages`.
Step definitions should be short and expressive.
Only use regular expressions in step name definitions when absolutely necessary.
Most of the time it might be better to refactor steps into smaller sub-steps that only handle one single functionality.

### Using Tags

Additional functionality for tests can be enabled by tagging either single scenarios or whole features with special tags.

#### @knownFailing

This tag prevents features from being tested completely,
useful to be able to commit WIP features that would otherwise cause a breaking CI build.

#### @isolated

When a scenario is tagged with this tag, the database gets wiped before and after the scenario is run.
Per default, the database is being reset to a clean state after every feature.

### How to use the XpathBuilder

The `Shopware\Component\XpathBuilder` namespace contains a few classes that can be helpful
when writing tests that rely on complicated xpath queries.
It is recommended to use either the `FrontendXpathBuilder` or the `BackendXpathBuilder`,
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

The BaseXpathBuilder comes pre-configured with a single `/` as its path.
There is *no implicit resetting* with the base builder.
If you configured a path and want to reuse the same Builder-instance,
you need to call `$builder->reset()` on it again in order to reset the path to its default value.
It is recommended that you explicitly call `->reset()` explicitly at the beginning of every xpath build to mitigate any possible, hard-to-debug mistakes.

All builder support building xpaths fluently by chaining calls such as `->child([...])`, `->descendant([...])` or`->followingSibling([...])`.
Those public methods all have the same signature of `->child($tag, $predicates, $index)`, with the latter two being optional.

To retrieve the currently configured xpath, call the `->getXpath()` method.
Please note that in contrast to the LegacyXpathBuilder,
the BaseXpathBuilder() *does not* implicitly reset the builder when you get the path.

There is a static shorthand method useful for creating xpaths inline:

```php
<?php

use Shopware\Component\XpathBuilder\BackendXpathBuilder;

$iconXpath = BackendXpathBuilder::create()->child('img', ['@class' => 'icon-smiley'])->getXpath();
```

#### Using the BackendXpathBuilder

The BackendXpathBuilder class has some helpful shorthand methods that provide easy access to common xpaths.
They can be accessed statically and always return the resulting Xpath as a ready-to-use xpath string.

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

The package test suite comes pre-configured with a simple asset generator,
that can generate random images with fixed sizes and text printed on them.
The asset generator is available over HTTP at `http://assetgenerator/`:
 ```
 http://assetgenerator/800x600/MyProduct.jpg
 ```

Please note that the size parameter is completely optional.
