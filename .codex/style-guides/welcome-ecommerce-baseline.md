# Welcome.vue Visual Baseline

Baseline for future edits to `resources/js/pages/Welcome.vue`.

## 1) Layout Structure (Do Not Drift)

1. Announcement bar (full width, top)
2. Sticky header
3. Category row/chips under header
4. Hero + promo side panel (2-column on desktop)
5. Featured product grid
6. Trust/service row

Mobile behavior:
- Single-column flow
- Keep the same section order
- Category chips remain horizontally scrollable

## 2) Placement Rules

- Brand block: top-left in header
- Search placeholder block: centered area in header (desktop)
- Cart cue + auth actions: top-right in header
- Hero CTA buttons: directly below hero copy
- Flash deals panel: right column beside hero on desktop
- Product cards: 2 columns on small screens, 4 columns on XL
- Trust cards: final row after product grid

## 3) Color Baseline

Core neutrals:
- Page background: `zinc-50`
- Main text: `zinc-900`
- Card borders: `zinc-200`

Primary actions:
- Dark CTA/button: `zinc-900` with white text
- Hover for dark CTA: `zinc-700`

Secondary actions:
- Register button: `amber-500` (hover `amber-400`)
- Dashboard button: `emerald-600` (hover `emerald-500`)

Promotional accents:
- Hero gradient: orange/amber/rose light tones
- Deal panel background: `zinc-900`
- Discount/current price emphasis: `red-700`

## 4) Card Style Baseline

Product card:
- Rounded corners: `rounded-3xl`
- Border: `border-zinc-200`
- Background: white
- Shadow: subtle (`shadow-sm`)
- Includes:
  - badge chip
  - category label
  - product visual block (gradient placeholder)
  - name
  - current + original price
  - full-width `Add to Cart` button

Deal/trust cards:
- Rounded 2xl/3xl
- Thin borders
- Clear heading + one supporting line

## 5) Typography Baseline

- Brand display font: `Playfair Display` (`.brand-title`)
- UI/body font: system Tailwind defaults in page
- Headings: bold/heavy, high contrast
- Labels/chips: uppercase tracking for commerce clarity

## 6) E-commerce Clarity Requirements

First viewport must clearly show:
- This is a store
- Products/prices exist
- Shopping CTAs exist

Mandatory visible cues:
- `Shop Now` or equivalent
- `Add to Cart` buttons on product cards
- Price formatting (`RM ...`)
- Cart indicator in header

## 7) Technical Constraints

- Keep `<script setup lang="ts">`
- Keep Wayfinder links only for auth:
  - `dashboard()`
  - `login()`
  - `register()`
- Keep `canRegister` condition
- No hard-coded internal app URLs

