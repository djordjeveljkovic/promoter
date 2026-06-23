# UI Refactor Plan — Promoteri Dashboard

> **Status:** Draft — awaiting review. No source files have been edited.
> **Goal:** Standardize the visual language of the dashboard so future features
> can be built faster and look consistent across every page.

---

## 1. Project audit summary

The project is a **Laravel 12 + Livewire 3 + Volt + Livewire Flux 2.1** app
styled with **Tailwind CSS 4** and **Alpine.js**. It exposes two layouts:

* `resources/views/components/layouts/app.blade.php` — main authenticated shell
  (uses `flux:sidebar` + the partials in `layouts/app/{header,sidebar}`)
* `resources/views/components/layouts/auth/{card,simple,split}.blade.php` —
  public/auth pages.

Across roughly **40 blade files** I found **three coexisting visual systems**:

| System | Pages | Look & feel |
| --- | --- | --- |
| **A — Indigo + Zinc (new)** | `admin/dashboard`, `admin/email_settings/*`, `promoter_managers/dashboard`, `promoter_managers/sub_promoters/*`, `subpromoters/*`, `livewire/admin/order-details`, `promoters/orders/show` | `bg-white dark:bg-zinc-900/60`, `border-zinc-200 dark:border-zinc-800`, `rounded-xl`, indigo primary, polished KPI strips |
| **B — Indigo + Gray (older)** | `admin/promoters/*`, `admin/promoter_managers/*`, `admin/orders/*`, `admin/ticket_type/index`, `promoters/dashboard`, `promoters/orders/{index,create}`, `subpromoters/dashboard` | `bg-white dark:bg-gray-800`, `border-gray-200 dark:border-gray-700`, `shadow-lg rounded-lg`, less polished |
| **C — One-off** | `promoters/help` (Serbian-only modal), `promoters/dashboard` cards, `welcome.blade.php` | Hard-coded Serbian copy, unique styles |

### Concrete duplication found

| Pattern | Where it appears | Approx. count |
| --- | --- | --- |
| Long Tailwind input class string | `mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white …` | 31 files |
| Indigo button (`bg-indigo-600 hover:bg-indigo-700 …`) | All form pages | 10+ files |
| Plus-icon SVG inline (`<svg class="w-5 h-5 mr-2 -ml-1" … viewBox="0 0 20 20">…`) | `admin/promoters`, `admin/promoter_managers`, `promoter_managers/sub_promoters`, `admin/ticket_type`, `promoters/orders/index` | 5 files |
| Status badge (job status pill) | `admin/dashboard`, `admin/orders/index`, `livewire/admin/order-details`, `promoters/orders/{index,show}`, `subpromoters/{dashboard,orders}` | 7 files |
| Table thead/tbody markup (`min-w-full divide-y divide-{gray,zinc}-200 dark:divide-{gray,zinc}-700` etc.) | 15+ files |
| Flash message markup (`@if (session('success'))<div class="mb-4 rounded-md bg-green-50 …">…`) | 14+ files (in addition to the global `<x-flash-messages>` component) |
| KPI strip (`grid gap-px overflow-hidden rounded-xl bg-gray-200 ring-1 ring-gray-200 dark:bg-zinc-800 …`) | `livewire/admin/order-details`, `promoters/orders/show`, `promoter_managers/dashboard` |
| `$jobStatusColors` array duplicated in 4 controllers (`AdminOrderController`, `OrderController`, `OrderController1`, `SubPromoterController`) — each with slightly different keys/colors |
| "Empty state" `<tr><td colspan="…">` | `promoters`, `promoters/orders`, `admin/promoters`, `admin/ticket_type`, `admin/promoter_managers`, `subpromoters/orders` |
| `text-3xl font-bold text-gray-800 dark:text-white` page headings | Almost every index page |
| `<div class="mb-6 flex items-center justify-between">` page-header wrapper | `admin/orders/create`, `admin/ticket_type/{index,create,edit}`, `promoters/orders/{index,create}` |

The presence of **two visual languages** is the biggest problem — newly-built
pages look polished, legacy pages look dated.

---

## 2. Target visual language

Adopt **System A (Indigo + Zinc)** as the single design system, because:

1. It is already used by the most recent, most polished pages.
2. It mirrors Livewire Flux's own neutral palette, so future Flux components
   drop in cleanly.
3. The "zinc" colors are slightly cooler than gray, which fits the dark
   sidebar/header combo in `layouts/app/sidebar.blade.php`.

**Design tokens (canonical):**

* Surfaces: `bg-white dark:bg-zinc-900/60` (cards), `bg-zinc-50 dark:bg-zinc-900` (page)
* Borders: `border-zinc-200 dark:border-zinc-800`
* Text: `text-zinc-900 dark:text-zinc-100`, secondary `text-zinc-500 dark:text-zinc-400`
* Primary: `bg-indigo-600 hover:bg-indigo-700 text-white`
* Danger: `bg-rose-600 hover:bg-rose-700 text-white`
* Success: `bg-emerald-600 hover:bg-emerald-700 text-white`
* Warning: `bg-amber-600 hover:bg-amber-700 text-white`
* Neutrals: `bg-white text-zinc-700 ring-1 ring-inset ring-zinc-300 dark:bg-zinc-900 dark:text-zinc-200 dark:ring-zinc-700`
* Radius: `rounded-lg` for buttons/inputs, `rounded-xl` for cards
* Spacing scale: `gap-3`, `gap-4`, `p-4`, `p-5`, `p-6`
* Shadows: `shadow-sm` (cards), no shadow on buttons (use ring instead)

**Status palette (canonical — replaces the 4 duplicated `$jobStatusColors` arrays):**

```php
'pending'    => 'warning',
'processing' => 'info',
'failed'     => 'danger',
'blocked'    => 'neutral',
'completed'  => 'success',
'sent'       => 'success',
'unknown'    => 'neutral',
```

These become a `Status::colors()` helper / enum in `app/Support/Status.php`
so the same color logic is available everywhere.

---

## 3. New component library

A new namespace `resources/views/components/ui/` will host the components.
Every component is a Blade anonymous component (Laravel 12 style — simple
PHP class via `Component::class` or pure blade).

```
resources/views/components/ui/
├── button.blade.php
├── link.blade.php
├── icon.blade.php              # <x-ui.icon name="plus" />
├── field.blade.php             # wrapper for label + control + error + hint
├── input.blade.php
├── textarea.blade.php
├── select.blade.php
├── checkbox.blade.php
├── radio.blade.php
├── card.blade.php              # <x-ui.card> ... </x-ui.card>
├── card-header.blade.php       # <x-ui.card.header :title="…" :action="…" />
├── kpi-strip.blade.php         # the 4-cell KPI grid
├── stat-card.blade.php         # single KPI tile (label, value, icon, trend)
├── page-header.blade.php       # eyebrow + title + subtitle + actions slot
├── alert.blade.php             # success/error/info/warning message
├── badge.blade.php             # <x-ui.badge variant="success">…</x-ui.badge>
├── status-pill.blade.php       # <x-ui.status-pill :status="$order->job_status" />
├── empty-state.blade.php       # <x-ui.empty-state icon="…" title="…" />
├── table.blade.php             # thead + tbody + responsive wrapper
├── table-header.blade.php
├── table-body.blade.php
├── table-row.blade.php
├── table-cell.blade.php        # supports :align, :numeric, :wrap
└── filter-form.blade.php       # standard GET filter bar
```

### 3.1 Component API contract (high level)

#### `<x-ui.button>`

```blade
<x-ui.button variant="primary" size="md" :href="route('…')" icon="plus">
    Add promoter
</x-ui.button>

<x-ui.button variant="danger" size="sm" type="submit" wire:click="…">
    Delete
</x-ui.button>
```

Props: `variant` (primary | secondary | danger | ghost | success | warning),
`size` (sm | md | lg), `href` (renders `<a>`, sets `wire:navigate` when
present), `icon` (string, renders `<x-ui.icon>`), `icon-trailing`,
`loading` (wire target), `disabled`, `type` (submit|button), full-width
boolean. Slot is the label.

When `href` is set it renders an `<a>` styled like a button (also used by
`<x-ui.link>` internally).

#### `<x-ui.link>`

Same visual as `<x-ui.button variant="link">`, always renders `<a>` with
optional `wire:navigate`. Used for table action links.

#### `<x-ui.field>`

```blade
<x-ui.field label="Email" for="email" :error="$errors->first('email')" hint="We'll never share it.">
    <x-ui.input id="email" name="email" type="email" :value="old('email')" />
</x-ui.field>
```

Centralises the `block text-sm font-medium` label, the `mt-1 text-xs text-red-500`
error, and the `mt-1 text-xs text-zinc-500` hint. Removes the need for the
repeated `block text-sm font-medium text-zinc-700 dark:text-zinc-300`
label pattern.

#### `<x-ui.input>`, `<x-ui.textarea>`, `<x-ui.select>`, `<x-ui.checkbox>`, `<x-ui.radio>`

Render the canonical Tailwind input class (single source of truth). Accept
`:error` so the border turns red. `:placeholder` works as normal.

`<x-ui.select>` accepts `options` (array `[value => label]`) **or** a slot
for raw `<option>` children (needed because many forms have translated
options inside a `foreach`). It also renders the chevron arrow consistently.

#### `<x-ui.card>` family

```blade
<x-ui.card>
    <x-ui.card.header title="Top promoters" :subtitle="__('…')">
        <x-slot:actions>
            <x-ui.button variant="secondary" size="sm" :href="route('…')">View all</x-ui.button>
        </x-slot:actions>
    </x-ui.card.header>
    <x-ui.card.body>
        {{ $slot }}
    </x-ui.card.body>
</x-ui.card>
```

The default card uses `rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900/60` and `shadow-sm`. The body slot handles overflow
(tables etc.) via `<div class="overflow-x-auto">` internally.

#### `<x-ui.kpi-strip>` and `<x-ui.stat-card>`

Replaces the multi-cell `<section class="mb-8 grid grid-cols-1 gap-px overflow-hidden rounded-xl bg-zinc-200 ring-1 ring-zinc-200 dark:bg-zinc-800 dark:ring-zinc-800 sm:grid-cols-2 lg:grid-cols-4">` pattern that
appears in `livewire/admin/order-details.blade.php`,
`pages/promoter_managers/dashboard.blade.php`, and
`pages/promoters/orders/show.blade.php`.

```blade
<x-ui.kpi-strip :cols="4">
    <x-ui.stat-card label="Total revenue" :value="number_format($rev, 2)" icon="banknotes" tone="success" />
    …
</x-ui.kpi-strip>
```

#### `<x-ui.page-header>`

Replaces the 6+ different `<div class="mb-6 flex items-center justify-between"><h1>…</h1></div>` blocks AND the `<header class="mb-8 flex flex-col gap-4 sm:mb-10 sm:flex-row sm:items-end sm:justify-between">` block in newer pages. One component, one look:

```blade
<x-ui.page-header
    :title="__('orders.main_heading')"
    :subtitle="__('orders.sub_heading')"
    :eyebrow="__('orders.eyebrow')"
>
    <x-slot:actions>
        <x-ui.button variant="primary" :href="route('orders.create')" icon="plus">
            {{ __('orders.create_new_order_button') }}
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>
```

#### `<x-ui.alert>`

Replaces **both** the 14 inline `@if (session('success')) <div class="mb-4 rounded-md bg-green-50 …">` blocks and the existing `<x-flash-messages>` component
(which is currently a giant JS-driven toast on the right edge).

Decision point (please confirm): I propose collapsing the global toasts and
inline alerts into **inline alerts only** (rendered at the top of the
content area, not floating). Pros: matches what 90% of the app already
does, simpler, accessible. Cons: lose the corner-toast style on the admin
orders page (we'd keep that toast on the `<x-layouts.app>` as a separate,
opt-in component if you want).

If you'd rather keep the toast, the alert component can support a `toast`
boolean prop.

#### `<x-ui.badge>` and `<x-ui.status-pill>`

```blade
<x-ui.badge variant="success">Active</x-ui.badge>

<x-ui.status-pill :status="$order->job_status" />
```

`<x-ui.status-pill>` reads from `app/Support/Status.php` (which exposes
`Status::colors()` and `Status::label($key)`). Both controllers and views
import the same helper, so the `$jobStatusColors` arrays in 4 controllers
go away.

#### `<x-ui.icon>`

Centralises inline SVG icons. We will register the icons the codebase
actually uses:

| Name | Used today in |
| --- | --- |
| `plus` | All "Add …" buttons |
| `search` | Filter bars |
| `arrow-left`, `arrow-right`, `arrow-path` | Header buttons |
| `trash`, `pencil-square`, `eye` | Table actions |
| `ticket`, `users`, `user`, `chart-bar`, `envelope`, `home` | Sidebar (already in Flux) |
| `magnifying-glass`, `bars-2`, `x-mark`, `cog`, `arrow-right-start-on-rectangle`, `chevrons-up-down`, `chevron-down` | Layout |

Flux icons remain the preferred source for sidebar / nav. `<x-ui.icon>` is
for ad-hoc inline use.

#### `<x-ui.table>` family

```blade
<x-ui.table>
    <x-ui.table-header>
        <x-ui.table-row>
            <x-ui.table-cell header>#</x-ui.table-cell>
            <x-ui.table-cell header>Email</x-ui.table-cell>
            <x-ui.table-cell header align="right">Total</x-ui.table-cell>
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        @forelse ($rows as $row)
            <x-ui.table-row>
                <x-ui.table-cell>{{ $row->id }}</x-ui.table-cell>
                <x-ui.table-cell>{{ $row->email }}</x-ui.table-cell>
                <x-ui.table-cell align="right" numeric>{{ number_format($row->total, 2) }}</x-ui.table-cell>
            </x-ui.table-row>
        @empty
            <x-ui.table-row>
                <x-ui.table-cell colspan="…">
                    <x-ui.empty-state … />
                </x-ui.table-cell>
            </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>
```

Props: `header` (boolean), `align` (left|right|center), `numeric` (boolean,
adds `tabular-nums`), `wrap` (default true), `nowrap` shortcut.

#### `<x-ui.empty-state>`

Standardises the `<h3>No data</h3><p>There are no records yet.</p>` blocks.
Accepts `icon`, `title`, `description`, and an `actions` slot.

#### `<x-ui.filter-form>`

Wraps the `<form method="GET" class="…">` patterns in
`pages/admin/orders/index.blade.php`, `pages/supremeadmin/overview.blade.php`,
and others. Standardises spacing between selects/inputs/buttons.

---

## 4. Backend support

To make `<x-ui.status-pill>` and any future status-aware UI consistent, add:

* `app/Support/Status.php` — single source of truth for job statuses
  (`colors()`, `label($key)`, `all()`). The 4 controllers currently passing
  `$jobStatusColors` will be refactored to use this class (and stop passing
  the array to the view).

* `app/Support/Concerns/HasStatusColor.php` — optional trait for the
  `Order` model so views can do `<x-ui.status-pill :status="$order->job_status" />`
  without any controller plumbing.

---

## 5. Step-by-step implementation order

The order is **bottom-up**: build the small atoms first, prove them on one
page, then sweep through the rest of the codebase.

1. **Phase 0 — Design tokens**
   * Add a `resources/css/components.css` file imported by `app.css` containing
     any custom utilities / `@layer components` rules needed
     (e.g. `.tabular-nums` helpers, custom focus ring shortcuts).
   * No changes to existing views yet.

2. **Phase 1 — Atoms**
   * Build `button`, `link`, `icon`, `field`, `input`, `textarea`, `select`,
     `checkbox`, `radio`, `badge`, `alert`.
   * Build `app/Support/Status.php`.

3. **Phase 2 — Molecules**
   * Build `card`, `card-header`, `kpi-strip`, `stat-card`, `page-header`,
     `status-pill`, `empty-state`.

4. **Phase 3 — Table primitives**
   * Build `table`, `table-header`, `table-body`, `table-row`, `table-cell`,
     `filter-form`.

5. **Phase 4 — Pilot page**
   * Convert **one** page end-to-end to validate the API. Suggested:
     `resources/views/pages/admin/promoters/index.blade.php` (medium-sized,
     uses most of the building blocks: header, table, status pills, actions,
     empty state).

6. **Phase 5 — Sweep (alphabetical by directory)**
   * Replace page-by-page. Order chosen so dependencies are migrated first.

7. **Phase 6 — Layout polish**
   * Update `components/layouts/app.blade.php`, `sidebar.blade.php`,
     `header.blade.php` if needed so they match the new design tokens
     (e.g. swap any remaining `gray` palette for `zinc`).
   * Decide what to do with the old `components/flash-messages.blade.php`
     (re-implement as `<x-ui.alert>`-based or keep as-is).

8. **Phase 7 — Cleanup**
   * Remove the four duplicated `$jobStatusColors` arrays from controllers
     and the now-unused variables in views.
   * Remove `echo.js` toast script if we replace it with `<x-ui.alert>`.
   * Lint pass with `vendor/bin/pint`.

---

## 6. File-by-file change list (the "todo")

> Every file below will be edited to use the new components. Items marked
> **(new)** are files that don't exist yet and will be created.

### 6.1 New component files

| Path | Purpose |
| --- | --- |
| `resources/views/components/ui/button.blade.php` **(new)** | Unified button |
| `resources/views/components/ui/link.blade.php` **(new)** | Unified link |
| `resources/views/components/ui/icon.blade.php` **(new)** | Inline SVG registry |
| `resources/views/components/ui/field.blade.php` **(new)** | Label + error + hint wrapper |
| `resources/views/components/ui/input.blade.php` **(new)** | Text/email/password input |
| `resources/views/components/ui/textarea.blade.php` **(new)** | Textarea |
| `resources/views/components/ui/select.blade.php` **(new)** | Select |
| `resources/views/components/ui/checkbox.blade.php` **(new)** | Checkbox |
| `resources/views/components/ui/radio.blade.php` **(new)** | Radio |
| `resources/views/components/ui/card.blade.php` **(new)** | Card |
| `resources/views/components/ui/card-header.blade.php` **(new)** | Card header |
| `resources/views/components/ui/kpi-strip.blade.php` **(new)** | KPI grid |
| `resources/views/components/ui/stat-card.blade.php` **(new)** | Single KPI tile |
| `resources/views/components/ui/page-header.blade.php` **(new)** | Standard page header |
| `resources/views/components/ui/alert.blade.php` **(new)** | Inline message |
| `resources/views/components/ui/badge.blade.php` **(new)** | Pill badge |
| `resources/views/components/ui/status-pill.blade.php` **(new)** | Order status pill |
| `resources/views/components/ui/empty-state.blade.php` **(new)** | Empty list placeholder |
| `resources/views/components/ui/table.blade.php` **(new)** | Table wrapper |
| `resources/views/components/ui/table-header.blade.php` **(new)** | `<thead>` |
| `resources/views/components/ui/table-body.blade.php` **(new)** | `<tbody>` |
| `resources/views/components/ui/table-row.blade.php` **(new)** | `<tr>` |
| `resources/views/components/ui/table-cell.blade.php` **(new)** | `<th>`/`<td>` |
| `resources/views/components/ui/filter-form.blade.php` **(new)** | Filter bar |
| `app/Support/Status.php` **(new)** | Central status map |
| `resources/css/components.css` **(new)** | Optional helper styles |
| `resources/views/components/icons/_registry.blade.php` **(new)** | Optional icon registry if `<x-ui.icon>` needs more than ~10 icons |

### 6.2 Files to edit (existing)

#### Layouts & global chrome

| File | Changes |
| --- | --- |
| `resources/views/components/layouts/app.blade.php` | Swap inner card `bg-white dark:bg-zinc-800` for `bg-white dark:bg-zinc-900/60 border border-zinc-200 dark:border-zinc-800`; consider rendering the alert via `<x-ui.alert>` instead of `<x-flash-messages>` |
| `resources/views/components/layouts/app/sidebar.blade.php` | Replace any `gray-*` palette with `zinc-*` if found; mostly already consistent |
| `resources/views/components/layouts/app/header.blade.php` | Same |
| `resources/views/components/layouts/auth/card.blade.php` | No change (already on the modern palette) |
| `resources/views/components/layouts/auth/split.blade.php` | No change |
| `resources/views/components/layouts/auth/simple.blade.php` | No change |
| `resources/views/components/flash-messages.blade.php` | Re-implement on top of `<x-ui.alert>` (or keep as a thin wrapper) — see open question below |
| `resources/css/app.css` | Import the new `components.css` |
| `resources/partials/head.blade.php` | Remove the inline `<style>` block defining input/select styles — those are now defined by `<x-ui.input>` / `<x-ui.select>` (open question below) |

#### Pages — admin

| File | Changes |
| --- | --- |
| `resources/views/pages/admin/dashboard.blade.php` | Replace 3 table blocks with `<x-ui.table>` family; replace KPI tiles with `<x-ui.stat-card>`; replace status pills with `<x-ui.status-pill>`; use `<x-ui.page-header>` |
| `resources/views/pages/admin/orders/index.blade.php` | Use `<x-ui.filter-form>`; `<x-ui.table>` family; `<x-ui.badge>` for status; `<x-ui.empty-state>`; `<x-ui.page-header>`; review whether to keep the JS toast |
| `resources/views/pages/admin/orders/create.blade.php` | `<x-ui.page-header>`; `<x-ui.field>` + `<x-ui.input>` / `<x-ui.select>` |
| `resources/views/pages/admin/orders/edit.blade.php` | Same as create |
| `resources/views/pages/admin/promoters/index.blade.php` | **Pilot page**. Full migration. |
| `resources/views/pages/admin/promoters/create.blade.php` | `<x-ui.field>` + `<x-ui.input>`; `<x-ui.button>`; `<x-ui.page-header>` |
| `resources/views/pages/admin/promoters/edit.blade.php` | Same as create |
| `resources/views/pages/admin/promoter_managers/index.blade.php` | `<x-ui.page-header>`, `<x-ui.table>` family, `<x-ui.button>`, `<x-ui.link>` for action links |
| `resources/views/pages/admin/promoter_managers/create.blade.php` | `<x-ui.field>`, `<x-ui.input>`, `<x-ui.button>`, `<x-ui.alert>` for errors |
| `resources/views/pages/admin/promoter_managers/edit.blade.php` | Same as create |
| `resources/views/pages/admin/ticket_type/index.blade.php` | `<x-ui.table>` family, `<x-ui.page-header>`, `<x-ui.empty-state>` |
| `resources/views/pages/admin/ticket_type/create.blade.php` | `<x-ui.field>`, `<x-ui.input>`; `<x-ui.button>` |
| `resources/views/pages/admin/ticket_type/edit.blade.php` | Same as create |
| `resources/views/pages/admin/email_settings/index.blade.php` | `<x-ui.page-header>`; `<x-ui.card>`; `<x-ui.alert>`; `<x-ui.button>`; `<x-ui.table>` family for templates |
| `resources/views/pages/admin/email_settings/create.blade.php` | `<x-ui.field>`; `<x-ui.radio>`; `<x-ui.button>` |
| `resources/views/pages/admin/email_settings/edit.blade.php` | `<x-ui.field>`; `<x-ui.button>`; `<x-ui.alert>`; `<x-ui.card>` |
| `resources/views/pages/admin/email_settings/_preview_frame.blade.php` | Probably no change (it's only rendered inside iframe) |

#### Pages — promoter managers

| File | Changes |
| --- | --- |
| `resources/views/pages/promoter_managers/dashboard.blade.php` | Already modern, minor cleanup: replace inline KPI markup with `<x-ui.kpi-strip>` / `<x-ui.stat-card>`, `<x-ui.page-header>`, `<x-ui.alert>` |
| `resources/views/pages/promoter_managers/sub_promoters/index.blade.php` | `<x-ui.page-header>`, `<x-ui.table>` family, `<x-ui.badge>` for overrides |
| `resources/views/pages/promoter_managers/sub_promoters/create.blade.php` | `<x-ui.field>`, `<x-ui.input>`, `<x-ui.button>`, `<x-ui.alert>` |
| `resources/views/pages/promoter_managers/sub_promoters/edit.blade.php` | Same as create |

#### Pages — promoters

| File | Changes |
| --- | --- |
| `resources/views/pages/promoters/dashboard.blade.php` | Currently uses gray palette — full migration to zinc palette; `<x-ui.kpi-strip>`; `<x-ui.table>` for ticket sales; `<x-ui.page-header>` |
| `resources/views/pages/promoters/help.blade.php` | Currently untranslated (Serbian) — open question below. After translation: replace inline modal markup with `<x-ui.alert>` / `<x-ui.button>` |
| `resources/views/pages/promoters/orders/index.blade.php` | `<x-ui.page-header>`, `<x-ui.table>` family, `<x-ui.status-pill>`, `<x-ui.empty-state>`, `<x-ui.alert>` |
| `resources/views/pages/promoters/orders/create.blade.php` | `<x-ui.field>`, `<x-ui.input>`, `<x-ui.select>`, `<x-ui.button>`, `<x-ui.alert>` |
| `resources/views/pages/promoters/orders/edit.blade.php` | Same as create |
| `resources/views/pages/promoters/orders/show.blade.php` | `<x-ui.kpi-strip>`; `<x-ui.table>` family for items; `<x-ui.page-header>` |

#### Pages — sub-promoters

| File | Changes |
| --- | --- |
| `resources/views/pages/subpromoters/dashboard.blade.php` | Migrate gray → zinc; `<x-ui.page-header>`; `<x-ui.stat-card>`; `<x-ui.table>` family |
| `resources/views/pages/subpromoters/orders.blade.php` | `<x-ui.page-header>`; `<x-ui.table>` family; `<x-ui.status-pill>`; `<x-ui.empty-state>`; `<x-ui.alert>` |

#### Pages — supreme admin

| File | Changes |
| --- | --- |
| `resources/views/pages/supremeadmin/overview.blade.php` | `<x-ui.page-header>`; `<x-ui.stat-card>`; `<x-ui.filter-form>`; `<x-ui.table>` family; replace inline filter logic with `<x-ui.field>`; `<x-ui.alert>` |

#### Livewire components

| File | Changes |
| --- | --- |
| `resources/views/livewire/admin/order-details.blade.php` | `<x-ui.page-header>`; `<x-ui.kpi-strip>`; `<x-ui.status-pill>`; `<x-ui.button>`; `<x-ui.field>` for the paid-amount form |
| `resources/views/livewire/settings/profile.blade.php` | Already uses `<flux:input>`; only minor: wrap in `<x-ui.page-header>` if desired (Flux has its own form scaffolding, so probably leave alone) |
| `resources/views/livewire/settings/password.blade.php` | Same — leave alone |
| `resources/views/livewire/settings/delete-user-form.blade.php` | Same |
| `resources/views/livewire/settings/appearance.blade.php` | Same |
| `resources/views/livewire/auth/login.blade.php` | Same — already uses `<flux:*>` |
| `resources/views/livewire/auth/register.blade.php` | Same |
| `resources/views/livewire/auth/forgot-password.blade.php` | Same |
| `resources/views/livewire/auth/reset-password.blade.php` | Same |
| `resources/views/livewire/auth/confirm-password.blade.php` | Same |
| `resources/views/livewire/auth/verify-email.blade.php` | Same |

> **Decision point:** Livewire/Flux already ships with `<flux:input>`,
> `<flux:select>`, `<flux:button>`, etc. They render with the accent color
> from `app.css`. Should we keep `<flux:*>` in auth & settings pages and
> only standardize the dashboard pages with `<x-ui.*>`? **My recommendation:
> yes — Flux already looks good in auth and the auth pages don't need to
> match the dashboard aesthetic.** Say the word if you'd prefer a single
> system everywhere.

#### Controllers (cleanup)

| File | Changes |
| --- | --- |
| `app/Http/Controllers/AdminOrderController.php` | Remove local `$jobStatusColors`; pass nothing (status pill reads from `Status::colors()`); drop the `compact('jobStatusColors')` |
| `app/Http/Controllers/OrderController.php` | Same |
| `app/Http/Controllers/OrderController1.php` | Same |
| `app/Http/Controllers/SubPromoterController.php` | Same |

---

## 7. Open questions for you before we start

Please confirm or override each one — these will affect the final code:

1. **Flash messages**: keep the floating toast in the top-right (current
   `<x-flash-messages>`) or move to inline alerts at the top of the page?
2. **Livewire Flux on auth pages**: keep `<flux:*>` in auth/settings or
   unify to `<x-ui.*>` everywhere?
3. **Promoters help page**: that page is hard-coded Serbian. Should we
   (a) translate it to all lang files and migrate it, or (b) leave it
   out of scope for this refactor?
4. **Dark mode**: the current `app.css` forces `class="dark"` on `<html>`.
   Should we leave the always-dark theme, or also add light-mode tokens?
5. **Form library split**: should `<x-ui.input>` use Flux under the hood
   (i.e. `<x-ui.input>` becomes a thin wrapper around `<flux:field>` /
   `<flux:input>`), or be a standalone Tailwind component? Flux wraps
   nicely with its own labels, but adds extra DOM. **Recommendation:
   standalone — matches the rest of the dashboard and avoids double
   wrapping with `<x-ui.field>`.**
6. **Pilot page**: happy to use `admin/promoters/index.blade.php` as the
   pilot? Or pick another one?

---

## 8. Risks & how I'll mitigate

* **Layout regressions**: each migrated page will be screenshotted before
  & after in dark mode (project is always-dark). Will visually verify.
* **Behaviour regressions**: `<x-ui.alert>` will keep the same `role="alert"`
  semantics the existing inline blocks have. The toast behaviour can be
  preserved if you decide to keep it (Phase 6).
* **Translation regressions**: no translation keys will change; only the
  markup wrapping them. We won't touch `lang/`.
* **JS regressions**: the existing `<x-flash-messages>` initialiser will
  be migrated, not removed, until you decide. The `orderStatusBoard`
  Alpine component in `admin/orders/index.blade.php` is preserved
  unchanged — only the surrounding markup changes.

---

## 9. Out of scope (intentionally)

* Translating `pages/promoters/help.blade.php` (unless you say otherwise).
* Re-styling the `<email>` blast template
  (`resources/views/emails/customer/tickets.blade.php`) — different
  audience, different constraints.
* Changing routes, controllers, or any business logic.
* Tailwind theme tweaks beyond the existing zinc palette.

---

**When you approve (or amend) the plan, I'll start with Phase 1 + 2**
(atom components + `app/Support/Status.php`), then do the pilot page
(`admin/promoters/index.blade.php`) before sweeping the rest.
