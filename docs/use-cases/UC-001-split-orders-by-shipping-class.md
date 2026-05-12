# UC-001: Split Orders By Shipping Class

## Goal

Protect the checkout-completion behavior that separates a WooCommerce order into processing orders grouped by item shipping class.

## Actors

- Site admin
- Store manager
- Customer
- Developer

## Preconditions

- WooCommerce is active.
- AMPM Order Split is active.
- The checkout order contains line items with product shipping classes.
- The order has not already been marked with `_order_split`.

## Main Flow

1. WooCommerce fires the thank-you hook after checkout.
2. `AMPM_split_order_after_checkout()` loads the order.
3. The plugin groups line items by product shipping class.
4. If the order has one shipping class, the original order is marked and left intact.
5. If the order has multiple shipping classes, each later class is moved into a new order.
6. The new order receives copied addresses, item meta, coupons, order meta, payment method, status, and totals.
7. The original and split orders receive `_order_split`, `_shipping_class`, customer notes, and diagnostic order notes.

## Acceptance Criteria

- Orders marked `_order_split` are not processed again.
- Single-shipping-class orders are not split.
- Multi-shipping-class orders create one split order per additional shipping class.
- Item meta, coupons, addresses, payment metadata, and customer notes are preserved.
- Shipping and totals are recalculated after moving items.
- Diagnostic notes are added to support troubleshooting.

## Related Code

- `ampm_order_split.php`
- `orderSplitClass.php`
- `includes/debug_class.php`

## Missing Automated Tests

- Add WooCommerce order double tests for single-class and multi-class orders.
- Add coupon, meta, and address copy coverage.
- Add idempotency coverage for `_order_split`.
