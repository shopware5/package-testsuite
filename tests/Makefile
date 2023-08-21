SHELL := bash
.ONESHELL:
.SHELLFLAGS := -eu -o pipefail -c
.DELETE_ON_ERROR:
MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules

check-code: check-code-style check-phpstan

check-code-style:
	./vendor/bin/php-cs-fixer fix --dry-run -v

fix-code-style:
	./vendor/bin/php-cs-fixer fix -v

check-phpstan:
	 ./vendor/bin/phpstan analyze
