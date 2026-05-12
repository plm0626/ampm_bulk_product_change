PHP ?= php

.PHONY: check lint test

check:
	sh scripts/check.sh

lint:
	sh scripts/lint-php.sh

test:
	sh scripts/test.sh

