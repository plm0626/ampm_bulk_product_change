#!/usr/bin/env sh
set -eu

ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"

if [ -x "$ROOT/vendor/bin/phpunit" ]; then
	PHPUNIT="$ROOT/vendor/bin/phpunit"
elif [ -x "$ROOT/../../vendor/bin/phpunit" ]; then
	PHPUNIT="$ROOT/../../vendor/bin/phpunit"
else
	echo "PHPUnit not found. Run composer install in this repository or the workspace root." >&2
	exit 1
fi

"$PHPUNIT" -c "$ROOT/phpunit.xml.dist"

