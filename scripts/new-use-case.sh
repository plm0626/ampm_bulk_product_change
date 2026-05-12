#!/usr/bin/env sh
set -eu

ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"

if [ "$#" -lt 2 ]; then
	echo "Usage: scripts/new-use-case.sh UC-001 \"Use Case Title\"" >&2
	exit 1
fi

ID="$1"
TITLE="$2"
SLUG="$(printf '%s' "$TITLE" | tr '[:upper:] ' '[:lower:]-' | tr -cd 'a-z0-9-')"
TARGET="$ROOT/docs/use-cases/$ID-$SLUG.md"

if [ -e "$TARGET" ]; then
	echo "Use case already exists: $TARGET" >&2
	exit 1
fi

cp "$ROOT/docs/templates/use-case.md" "$TARGET"
echo "Created $TARGET"

