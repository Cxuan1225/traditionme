# Tradition Me — Incremental E-Commerce Roadmap

## Context

**Phases 0–3** wired the full product pipeline: real DB products → real cart → checkout → order creation → image upload. The storefront, cart, checkout, and admin product management are now fully functional.

**Phases 4–8** build on that foundation to complete the e-commerce platform: admin order management → customer order history → stock/inventory tracking → email notifications → payment integration (Stripe).

## Current State

- Phase 0 is complete.
- Phase 1 is complete in code and verification history, with only manual storefront smoke still recommended.
- Phase 2 is complete in code and automated verification, with only manual checkout/order smoke still recommended.
- Phase 3 is complete in code and automated verification, with only manual browser upload smoke still recommended.
- Phase 4 is complete in code and automated verification, including admin order management, product descriptions, and product pricing/category normalization.
- Phase 5 is complete in code and automated verification, with only manual account-order smoke still recommended.
- Phases 6–8 cover stock management, email notifications, and payment integration.

---

## Phase 0: Architectural Cleanup ✅

Extracted `useCurrency` composable. Deferred ProductCard refactor to Phase 1. Confirmed Settings pages and Enum/Support class patterns are intentional.

---

## Phase 1: Wire Shop + Welcome to Real DB Products ✅

Replaced all hardcoded storefront data with real DB queries. Cart validation against DB, `GetWelcomeDataAction` queries real products, Shop.vue rewritten with server-side filtering/pagination, Cart.vue rewritten with `router.patch/delete` mutations. Created `ShopFiltersData`, `GetShopProductsAction`, `GetCartAction`, `UpdateCartItemAction`, `RemoveCartItemAction`, `ShopProductResource`, `CartLineResource`.

---

## Phase 2: Checkout & Order Flow ✅

Created `Order` + `OrderItem` models with full shipping/pricing schema (sen-based). `PlaceOrderAction` handles cart → order conversion in `DB::transaction()` with coupon validation (`HERITAGE10` = 10%). Checkout.vue with Malaysia-specific state/city dropdowns, OrderConfirmation.vue with line items + totals. Created `CheckoutController`, `OrderController`, `OrderResource`, `OrderItemResource`, `CheckoutRequest`, `PlaceOrderData`.

---

## Phase 3: Product Image Upload ✅

Added file upload with drag-and-drop to admin product dialogs. Uploaded images stored on `public` disk as `/storage/products/...`. Old managed images cleaned up on replacement. Multipart payloads when file attached. Changes mirrored to both admin and settings Products.vue pages.

---

## Phase 4: Admin Order Management + Product Description + Product Management Normalization ✅

Added admin order management with list/detail pages, status transitions, timestamps, permission-gated routes, and navigation in the admin layout. Expanded products with `description`, plus product-management normalization using a `ProductCategory` enum (`malay`, `chinese`, `indian`, `other`), multipart request normalization, category cleanup migration, and RM-based pricing inputs across both admin/settings product pages.

---

## Phase 5: Customer Order History ✅

Added authenticated customer order history with paginated account order listing, dedicated order detail, status filtering, and timeline timestamps. `OrderController` now serves both account history and the existing post-checkout confirmation flow, with `/account/orders` routes, updated `OrderResource`/`order.ts`, and a “My Orders” entry in the storefront account menu.

---

## Phase 6: Stock / Inventory Management

### 6.1 Migration

- [ ] Create migration `add_stock_to_products_table`
    - `unsignedInteger('stock_quantity')->default(0)->after('is_active')`
    - `boolean('track_stock')->default(false)->after('stock_quantity')` (`track_stock = false` means unlimited stock, allowing gradual opt-in per product)

### 6.2 Backend model + DTO updates

- [ ] Modify `app/Models/Product.php`
    - Add `stock_quantity`, `track_stock` to `$fillable`
    - Add cast: `'track_stock' => 'bool'`
    - Add helper: `isInStock(): bool` — returns `!$this->track_stock || $this->stock_quantity > 0`
    - Add helper: `hasStockFor(int $quantity): bool` — returns `!$this->track_stock || $this->stock_quantity >= $quantity`
- [ ] Modify `app/DTOs/Settings/ProductPayloadData.php` — add `int $stockQuantity`, `bool $trackStock`
- [ ] Modify `app/Http/Requests/Settings/ProductStoreRequest.php`
    - Add rules: `'stock_quantity' => ['nullable', 'integer', 'min:0']`, `'track_stock' => ['nullable', 'boolean']`
- [ ] Modify `app/Http/Requests/Settings/ProductUpdateRequest.php` — same
- [ ] Modify `app/Actions/Settings/CreateProductAction.php` — include stock fields in create array
- [ ] Modify `app/Actions/Settings/UpdateProductAction.php` — include stock fields in update array
- [ ] Modify `app/Http/Resources/ShopProductResource.php` — add `'inStock'`, conditionally `'stockQuantity'`

### 6.3 Stock enforcement in cart + checkout

- [ ] Modify `app/Actions/Commerce/AddCartItemAction.php`
    - After finding product, check `$product->hasStockFor($quantity)`
    - If not, throw or return error: "Only {$product->stock_quantity} items available"
- [ ] Modify `app/Actions/Commerce/UpdateCartItemAction.php`
    - Same stock check when increasing quantity
- [ ] Modify `app/Actions/Commerce/PlaceOrderAction.php`
    - Inside the `DB::transaction()`, re-check stock for each line with `lockForUpdate()`
    - Decrement stock: `Product::where('id', ...)->decrement('stock_quantity', $quantity)` for tracked products
    - If any item is out of stock, throw `RuntimeException` with clear message
- [ ] Create `app/Actions/Admin/RestoreStockAction.php`
    - Called when an order is cancelled: restores stock for each order item
- [ ] Modify `app/Actions/Admin/UpdateOrderStatusAction.php`
    - Call `RestoreStockAction` when status changes to `cancelled`

### 6.4 Frontend

- [ ] Modify `resources/js/pages/admin/settings/Products.vue`
    - Add "Track Stock" toggle and "Stock Quantity" number input in create/edit dialog
    - When `track_stock` is off, stock quantity input is disabled/hidden
    - Show stock indicator in product table: green dot for in-stock, red for out-of-stock, grey dash for untracked
- [ ] Modify `resources/js/pages/Shop.vue`
    - Show "Out of Stock" overlay/badge on product cards when `inStock === false`
    - Disable "Add to Cart" button for out-of-stock products
- [ ] Modify `resources/js/pages/Cart.vue`
    - Show stock warning if quantity exceeds available stock
    - Prevent quantity increase beyond stock limit

### Files created in Phase 6:

- `database/migrations/..._add_stock_to_products_table.php`
- `app/Actions/Admin/RestoreStockAction.php`

### Files modified in Phase 6:

- `app/Models/Product.php`
- `app/DTOs/Settings/ProductPayloadData.php`
- `app/Http/Requests/Settings/ProductStoreRequest.php`
- `app/Http/Requests/Settings/ProductUpdateRequest.php`
- `app/Actions/Settings/CreateProductAction.php`
- `app/Actions/Settings/UpdateProductAction.php`
- `app/Http/Resources/ShopProductResource.php`
- `app/Actions/Commerce/AddCartItemAction.php`
- `app/Actions/Commerce/UpdateCartItemAction.php`
- `app/Actions/Commerce/PlaceOrderAction.php`
- `app/Actions/Admin/UpdateOrderStatusAction.php`
- `resources/js/pages/admin/settings/Products.vue`
- `resources/js/pages/Shop.vue`
- `resources/js/pages/Cart.vue`

---

## Phase 7: Email Notifications

### 7.1 Mail classes

- [ ] Create `app/Mail/OrderConfirmationMail.php`
    - Mailable class using `Envelope` + `Content` (Laravel 12 pattern)
    - Subject: "Your Tradition Me Order TM-{id} is Confirmed"
    - Receives `Order $order` (with items loaded)
    - Uses Blade markdown template
- [ ] Create `resources/views/mail/order-confirmation.blade.php`
    - Markdown mail template
    - Order number, placed date
    - Line items table (product name, qty, unit price, subtotal)
    - Payment summary (subtotal, discount, shipping, total)
    - Shipping address
    - "View your order" button linking to `/account/orders/{order}`
- [ ] Create `app/Mail/OrderStatusUpdatedMail.php`
    - Subject: "Your Order TM-{id} is now {Status}"
    - Receives `Order $order`, `string $newStatus`
    - Different messaging per status:
        - `paid`: "Payment confirmed! We're preparing your order."
        - `shipped`: "Your order is on its way!"
        - `delivered`: "Your order has been delivered."
        - `cancelled`: "Your order has been cancelled."
- [ ] Create `resources/views/mail/order-status-updated.blade.php`
    - Markdown mail template with status-specific messaging
    - Order summary, shipping info, "View order" button

### 7.2 Notifications

- [ ] Create `app/Notifications/OrderPlacedNotification.php`
    - Uses `mail` channel
    - Wraps `OrderConfirmationMail` via `toMail()`
    - `User` model already uses `Notifiable` trait
- [ ] Create `app/Notifications/OrderStatusChangedNotification.php`
    - Uses `mail` channel
    - Wraps `OrderStatusUpdatedMail`

### 7.3 Dispatch points

- [ ] Modify `app/Actions/Commerce/PlaceOrderAction.php`
    - After order creation and cart clearing: `$user->notify(new OrderPlacedNotification($order))`
- [ ] Modify `app/Actions/Admin/UpdateOrderStatusAction.php`
    - After status update: `$order->user->notify(new OrderStatusChangedNotification($order, $newStatus))`

### Files created in Phase 7:

- `app/Mail/OrderConfirmationMail.php`
- `app/Mail/OrderStatusUpdatedMail.php`
- `resources/views/mail/order-confirmation.blade.php`
- `resources/views/mail/order-status-updated.blade.php`
- `app/Notifications/OrderPlacedNotification.php`
- `app/Notifications/OrderStatusChangedNotification.php`

### Files modified in Phase 7:

- `app/Actions/Commerce/PlaceOrderAction.php`
- `app/Actions/Admin/UpdateOrderStatusAction.php`

---

## Phase 8: Payment Integration (Stripe)

### 8.1 Setup

- [ ] Install `stripe/stripe-php` via Composer
- [ ] Add `.env` keys: `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`
- [ ] Create `config/payment.php`
    - `'driver' => env('PAYMENT_DRIVER', 'stripe')`
    - `'stripe.key' => env('STRIPE_KEY')`
    - `'stripe.secret' => env('STRIPE_SECRET')`
    - `'stripe.webhook_secret' => env('STRIPE_WEBHOOK_SECRET')`
    - `'currency' => 'myr'`

### 8.2 Service layer

- [ ] Create `app/Contracts/PaymentGateway.php` (interface)
    - `createCheckoutSession(Order $order): PaymentSession`
    - `handleWebhook(Request $request): WebhookResult`
    - `verifyPayment(string $sessionId): PaymentVerification`
- [ ] Create `app/DTOs/Commerce/PaymentSession.php`
    - `readonly class` with `public string $sessionId`, `public string $checkoutUrl`
- [ ] Create `app/DTOs/Commerce/PaymentVerification.php`
    - `readonly class` with `public bool $success`, `public string $transactionId`, `public int $amountInSen`
- [ ] Create `app/Services/Payment/StripeGateway.php` implements `PaymentGateway`
    - `createCheckoutSession()`: creates Stripe Checkout Session with line items from order, success/cancel URLs, MYR currency
    - `handleWebhook()`: verifies Stripe signature, processes `checkout.session.completed` event
    - `verifyPayment()`: retrieves session, confirms payment status
- [ ] Create `app/Providers/PaymentServiceProvider.php`
    - Binds `PaymentGateway` interface to `StripeGateway` based on config driver

### 8.3 Migration

- [ ] Create migration `add_payment_fields_to_orders_table`
    - `string('payment_method')->nullable()->after('notes')`
    - `string('payment_session_id')->nullable()->after('payment_method')`
    - `string('payment_transaction_id')->nullable()->after('payment_session_id')`
- [ ] Modify `app/Models/Order.php` — add payment fields to `$fillable`

### 8.4 Actions + controllers

- [ ] Create `app/Actions/Commerce/InitiatePaymentAction.php`
    - Takes `Order $order`, calls `PaymentGateway::createCheckoutSession()`
    - Stores `payment_session_id` on the order
    - Returns the checkout URL
- [ ] Create `app/Actions/Commerce/ProcessPaymentWebhookAction.php`
    - Receives webhook payload, finds order by session ID
    - Updates order status to `paid`, sets `paid_at`, stores `payment_transaction_id`
    - Triggers `OrderStatusChangedNotification` (from Phase 7)
- [ ] Create `app/Http/Controllers/Commerce/PaymentController.php`
    - `initiate(Order $order)` — calls `InitiatePaymentAction`, redirects to gateway checkout URL
    - `success(Request $request)` — landing page after successful payment, verifies and shows confirmation
    - `cancel(Request $request)` — landing page after cancelled payment
    - `webhook(Request $request)` — handles gateway webhook callbacks (no auth middleware, uses signature verification)

### 8.5 Routes

- [ ] Add routes in `routes/web.php` (inside auth+verified group):
    - `POST /orders/{order}/pay` → `PaymentController@initiate`
    - `GET /payment/success` → `PaymentController@success`
    - `GET /payment/cancel` → `PaymentController@cancel`
- [ ] Add outside auth group: `POST /webhooks/payment` → `PaymentController@webhook` (exclude from CSRF)
- [ ] Modify `app/Http/Controllers/Commerce/CheckoutController.php`
    - After `PlaceOrderAction`, redirect to payment initiation instead of order confirmation
- [ ] Run `php artisan wayfinder:generate`

### 8.6 Frontend pages

- [ ] Modify `resources/js/pages/OrderConfirmation.vue`
    - If order status is `pending` (unpaid), show "Pay Now" button that posts to `/orders/{order}/pay`
    - If status is `paid`, show payment confirmation checkmark
- [ ] Create `resources/js/pages/PaymentSuccess.vue`
    - Shows payment success message with order details
    - Links to order detail page
- [ ] Create `resources/js/pages/PaymentCancel.vue`
    - Shows "Payment was not completed" message
    - Option to retry payment or return to order
- [ ] Modify `resources/js/pages/account/OrderDetail.vue` (from Phase 5)
    - Show "Pay Now" button for unpaid orders
    - Show payment status and transaction ID for paid orders

### Files created in Phase 8:

- `config/payment.php`
- `app/Contracts/PaymentGateway.php`
- `app/DTOs/Commerce/PaymentSession.php`
- `app/DTOs/Commerce/PaymentVerification.php`
- `app/Services/Payment/StripeGateway.php`
- `app/Providers/PaymentServiceProvider.php`
- `app/Actions/Commerce/InitiatePaymentAction.php`
- `app/Actions/Commerce/ProcessPaymentWebhookAction.php`
- `app/Http/Controllers/Commerce/PaymentController.php`
- `database/migrations/..._add_payment_fields_to_orders_table.php`
- `resources/js/pages/PaymentSuccess.vue`
- `resources/js/pages/PaymentCancel.vue`

### Files modified in Phase 8:

- `app/Models/Order.php`
- `app/Http/Controllers/Commerce/CheckoutController.php`
- `resources/js/pages/OrderConfirmation.vue`
- `resources/js/pages/account/OrderDetail.vue`
- `routes/web.php`

---

## Phase Dependencies

- **Phase 4** — standalone (no dependency on Phase 3)
- **Phase 5** — depends on Phase 4 (reuses `AdminOrderResource` patterns)
- **Phase 6** — mostly standalone, but hooks into Phase 4's `UpdateOrderStatusAction` for stock restore on cancellation
- **Phase 7** — depends on Phase 4 (hooks into `UpdateOrderStatusAction`) and Phase 5 (email links to account order pages)
- **Phase 8** — depends on Phases 4, 5, and 7

---

## Verification

### Phases 0–5 ✅

All completed phases passed their relevant automated checks, including `php artisan test`, `vendor/bin/phpstan analyse`, `vendor/bin/pint --test`, `npm run lint:check`, `npm run types:check`, and `npm run format:check`. Manual browser smoke tests are still recommended for storefront checkout, account orders, and admin product-management UX.

---

> See [PLAN2.md](PLAN2.md) for Phases 9–19 (product variants, coupons, saved addresses, reviews, wishlist, categories, search, analytics, shipping, refunds, multi-language).
