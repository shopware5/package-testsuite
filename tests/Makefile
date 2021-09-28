SHELL := bash
.ONESHELL:
.SHELLFLAGS := -eu -o pipefail -c
.DELETE_ON_ERROR:
MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules

check-code: check-phpstan check-code-style

check-code-style:
	./vendor/bin/php-cs-fixer fix --dry-run

fix-code-style:
	./vendor/bin/php-cs-fixer fix

check-phpstan:
	 ./vendor/bin/phpstan analyze
