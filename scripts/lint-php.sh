#!/usr/bin/env sh
set -eu

ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"

find "$ROOT" \
	\( -path "$ROOT/vendor" -o -path "$ROOT/node_modules" \) -prune \
	-o -name '*.php' -print |
while IFS= read -r FILE; do
	php -l "$FILE" >/dev/null
done
