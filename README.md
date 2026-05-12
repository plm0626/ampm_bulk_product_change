# AMPM Order Split

WooCommerce plugin that splits completed orders into processing orders grouped by product shipping class.

## Purpose

AMPM Order Split protects the fulfillment workflow where a mixed-location checkout order is separated into shipping-class-specific orders while preserving customer/order context.

- group original order items by product shipping class
- create split orders for additional shipping classes
- copy billing/shipping address, coupons, item meta, and order meta
- mark original and split orders with shipping class metadata
- add diagnostic order notes for troubleshooting

## AUI Development Framework

The AUI Development Framework is the persistent maintenance layer for plugin work. It keeps the intent, code map, tests, and future work close to the code so a developer or assistant can resume work without rediscovering the whole plugin.

Start with [docs/architecture/aui-development-framework.md](docs/architecture/aui-development-framework.md).

## AUI Layers

| Layer | Purpose |
| --- | --- |
| `AGENTS.md` | Persistent AUI behavioral instructions for Codex |
| `.github/` templates | Standardize issues, pull requests, and discussions |
| `docs/templates/` | Reusable use case, issue, and ADR templates |
| `scripts/` | Repeatable automation commands |
| `Makefile` | One-command execution |
| `.cursor/rules/` and `.codex/prompts/` | IDE-integrated AUI context |
| GitHub Actions | Automated enforcement |
| Plugin-specific docs and tests | Preserve order split behavior and follow-up gaps |

## Plugin Structure

```text
ampm_order_split/
├── docs/
│   ├── architecture/
│   ├── decisions/
│   ├── github-issue-drafts/
│   └── use-cases/
├── includes/
├── tests/
├── ampm_order_split.php
├── orderSplitClass.php
├── composer.json
├── phpunit.xml.dist
└── README.md
```

## Primary Behavior

- `AMPM_split_order_after_checkout()` runs after checkout.
- Orders already marked `_order_split` are skipped.
- Orders with more than one shipping class create split orders for later classes.
- Original and split orders get shipping-class metadata and explanatory notes.

## Testing

From this plugin directory:

```bash
make test
```

From the workspace root:

```bash
./vendor/bin/phpunit -c plugins/ampm_order_split/phpunit.xml.dist
```
