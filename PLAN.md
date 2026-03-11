# Tradition Me — Incremental E-Commerce Roadmap

## Context

The storefront (Shop + Welcome) showed **hardcoded static data** instead of real DB products. Admin could create products, but they never appeared to buyers. Cart also used fake data. This plan wires the full pipeline: real products → real cart → checkout → image upload.

---

## Phase 0: Architectural Cleanup ✅

- **0.1** ✅ Extracted `useCurrency` composable (`resources/js/composables/useCurrency.ts`)
- **0.2** Skipped — ProductCard deferred (data shape changes in Phase 1)
- **0.3** Skipped — Settings pages are intentionally different per context
- **0.4** No issue — Enum + Support class are complementary

---

## Phase 1: Wire Shop + Welcome to Real DB Products ✅

- **1.1** ✅ `AddCartItemAction` now validates against DB (`Product::where(...)->exists()`)
- **1.2** ✅ `GetWelcomeDataAction` queries real products + count from DB
- **1.3** ✅ Shop backend: `ShopFiltersData`, `GetShopProductsAction`, `ShopProductResource`, `ShopController`
- **1.4** ✅ Shop.vue rewritten: server-side filtering, pagination, add-to-cart wired
- **1.5** ✅ Cart backend: `GetCartAction`, `UpdateCartItemAction`, `RemoveCartItemAction`, `CartLineResource`, routes
- **1.6** ✅ Cart.vue rewritten: uses `lines` prop, `router.patch/delete` for mutations
- **1.7** ✅ Wayfinder regenerated (`cart/items` now has `update` + `destroy`)

### Files created in Phase 1:
- `app/DTOs/Commerce/ShopFiltersData.php`
- `app/Actions/Commerce/GetShopProductsAction.php`
- `app/Actions/Commerce/GetCartAction.php`
- `app/Actions/Commerce/UpdateCartItemAction.php`
- `app/Actions/Commerce/RemoveCartItemAction.php`
- `app/Http/Resources/ShopProductResource.php`
- `app/Http/Resources/CartLineResource.php`
- `resources/js/types/shop.ts`

### Files modified in Phase 1:
- `app/Actions/Commerce/AddCartItemAction.php`
- `app/Actions/Home/GetWelcomeDataAction.php`
- `app/Http/Controllers/Commerce/ShopController.php`
- `app/Http/Controllers/Commerce/CartController.php`
- `app/Http/Controllers/Commerce/CartItemController.php`
- `app/Http/Requests/Commerce/AddCartItemRequest.php`
- `resources/js/pages/Shop.vue`
- `resources/js/pages/Cart.vue`
- `routes/web.php`

---

## Phase 2: Checkout & Order Flow ⬅️ IMPLEMENTED, VERIFYING

### 2.1 Create Order/OrderItem models + migrations
- [x] Create migration `create_orders_table`
  - `id`, `user_id` (FK → users), `status` (string: pending/paid/shipped/delivered/cancelled)
  - `subtotal_in_sen`, `discount_in_sen`, `shipping_in_sen`, `total_in_sen` (unsigned int)
  - `shipping_name`, `shipping_address` (text), `shipping_city`, `shipping_state`, `shipping_postcode`, `shipping_phone`
  - `coupon_code` (nullable), `notes` (nullable text)
  - `paid_at`, `shipped_at`, `delivered_at` (nullable timestamps)
  - `timestamps()`
- [x] Create migration `create_order_items_table`
  - `id`, `order_id` (FK → orders), `product_id` (FK → products)
  - `product_name` (string — snapshot at time of order)
  - `unit_price_in_sen`, `quantity`, `subtotal_in_sen`
  - `timestamps()`
- [x] Create `app/Models/Order.php` — fillable, casts, `user()`, `items()` relations
- [x] Create `app/Models/OrderItem.php` — fillable, `order()`, `product()` relations

### 2.2 Backend actions
- [x] Create `app/Actions/Commerce/ValidateCouponAction.php`
  - Input: coupon code string → returns `[valid: bool, discountPercent: int]`
  - Hardcoded: `HERITAGE10` = 10% off, everything else invalid
- [x] Create `app/DTOs/Commerce/PlaceOrderData.php`
  - `shippingName`, `shippingAddress`, `shippingCity`, `shippingState`, `shippingPostcode`, `shippingPhone`
  - `couponCode` (nullable), `notes` (nullable)
  - `fromRequest()` factory
- [x] Create `app/Actions/Commerce/PlaceOrderAction.php`
  - Reads cart via `GetCartAction`
  - Validates cart not empty
  - Calculates subtotal, discount (via `ValidateCouponAction`), shipping, total
  - Wraps `Order` + `OrderItem` creation in `DB::transaction()`
  - Clears `cart.items` from session
  - Returns created `Order`

### 2.3 Controllers, resources & routes
- [x] Create `app/Http/Requests/Commerce/CheckoutRequest.php`
  - Validates: `shipping_name`, `shipping_address`, `shipping_city`, `shipping_state`, `shipping_postcode`, `shipping_phone` (required strings)
  - Optional: `coupon_code`, `notes`
- [x] Create `app/Http/Controllers/Commerce/CheckoutController.php`
  - `show()` — renders Checkout page with cart lines (redirects to cart if empty)
  - `store()` — calls `PlaceOrderAction`, redirects to order confirmation
- [x] Create `app/Http/Controllers/Commerce/OrderController.php`
  - `show(Order $order)` — renders OrderConfirmation, checks `$order->user_id === auth()->id()`
- [x] Create `app/Http/Resources/OrderResource.php` — order details + checkout-ready summary/shipping payload
- [x] Create `app/Http/Resources/OrderItemResource.php` — item snapshot + storefront display fields
- [x] Add routes in `routes/web.php` (inside auth middleware group):
  - `GET /checkout` → `CheckoutController@show`
  - `POST /checkout` → `CheckoutController@store`
  - `GET /orders/{order}` → `OrderController@show`
- [ ] Run `php artisan wayfinder:generate`
  - Attempted on 2026-03-11, but the command failed with `Permission denied` writing `resources/js/actions/Laravel/index.ts`

### 2.4 Frontend pages
- [x] Create `resources/js/types/order.ts`
  - `Order`, `OrderItem`, `OrderSummary` interfaces
- [x] Create `resources/js/pages/Checkout.vue`
  - Shipping address form (name, address, city, state, postcode, phone)
  - Order summary sidebar (reuses cart summary pattern)
  - Coupon code input
  - Submit via `router.post('/checkout', formData)`
  - Uses `StorefrontLayout`
- [x] Create `resources/js/pages/OrderConfirmation.vue`
  - Order number, status badge, line items table, totals
  - Shipping address display
  - "Continue shopping" link → `/shop`
  - Uses `StorefrontLayout`
- [x] Modify `resources/js/pages/Cart.vue`
  - Wire "Proceed to checkout" button → `router.get('/checkout')` (or `Link :href`)

---

## Phase 3: Product Image Upload

### 3.1 Backend
- [ ] Modify `app/Http/Requests/Settings/ProductStoreRequest.php`
  - Add rule: `'image' => ['nullable', 'image', 'max:4096']`
- [ ] Modify `app/Http/Requests/Settings/ProductUpdateRequest.php` — same
- [ ] Modify `app/DTOs/Settings/ProductPayloadData.php`
  - Add `?UploadedFile $imageFile` property
  - Update `fromStoreRequest()` / `fromUpdateRequest()` to extract uploaded file
- [ ] Modify `app/Actions/Settings/CreateProductAction.php`
  - If `$data->imageFile` present: `Storage::disk('public')->putFile('products', $data->imageFile)`
  - Set `image_url` to `/storage/products/{filename}`
- [ ] Modify `app/Actions/Settings/UpdateProductAction.php`
  - Same as create + delete old stored image if replaced
- [ ] Run `php artisan storage:link`

### 3.2 Frontend
- [ ] Modify `resources/js/pages/admin/settings/Products.vue`
  - Add file input with drag-and-drop zone in create/edit dialog
  - Image preview (current image or selected file)
  - Submit with `forceFormData: true` when file attached
- [ ] Modify `resources/js/pages/settings/Products.vue` — same changes if this page also has product management

---

## Verification

### Phase 0 ✅
### Phase 1 ✅ (pending migration run + manual test)

### Phase 2
1. Run `php artisan migrate` for orders + order_items tables
2. Add items to cart via `/shop`, visit `/cart`
3. Click "Proceed to checkout" → redirected to `/checkout` (must be logged in)
4. Fill shipping form, submit → order created, cart cleared
5. Order confirmation page shows correct order details
6. Quality gates remain pending because:
   - `php artisan test tests/Feature/Commerce/CheckoutFlowTest.php` passed
   - `vendor/bin/pint` passed for the Phase 2 backend files
   - `npx prettier --check` passed for touched frontend files, but the shell wrapper still timed out after printing success
   - `npm run types:check` reports repo-wide failures under `vendor/laravel/wayfinder/resources/*.blade.ts`, outside the checkout slice
   - broader `phpstan`, `eslint`, and full-suite verification runs remain unresolved due shell stalls in the shared terminal session

### Phase 3
1. In admin Products, upload an image file → stored in `storage/app/public/products/`
2. Product card on Shop/Welcome shows the uploaded image
3. Replacing image deletes old file from storage
