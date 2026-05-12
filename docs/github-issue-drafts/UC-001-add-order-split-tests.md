# UC-001: Add Order Split Tests

## Summary

Add automated coverage for splitting WooCommerce orders by shipping class.

## Background

The protected behavior is documented in `docs/use-cases/UC-001-split-orders-by-shipping-class.md`. Current tests only verify AUI scaffold wiring.

## Acceptance Criteria

- Orders already marked `_order_split` are skipped.
- Single-shipping-class orders are marked but not split.
- Multi-shipping-class orders create split orders for later classes.
- Item meta, order meta, coupons, addresses, payment method, status, shipping, and totals are copied/recalculated.
- Diagnostic notes are written to original and split orders.

## Related Code

- `ampm_order_split.php`
- `orderSplitClass.php`
