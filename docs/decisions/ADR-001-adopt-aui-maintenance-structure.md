# ADR-001: Adopt The AUI Maintenance Structure For AMPM Order Split

## Status

Accepted

## Context

AMPM Order Split mutates WooCommerce orders after checkout. It moves line items, creates related orders, recalculates totals, and writes metadata used for fulfillment. The expected behavior needs to stay visible beside the code.

## Decision

AMPM Order Split will use the AUI maintenance structure:

- use cases under `docs/use-cases/`
- issue drafts under `docs/github-issue-drafts/`
- decision records under `docs/decisions/`
- reusable documentation templates under `docs/templates/`
- repeatable commands through `Makefile` and `scripts/`

## Consequences

- Order mutation changes should start from a documented behavior target.
- Missing WooCommerce integration tests stay visible.
- Future refactors can preserve shipping-class split semantics.
