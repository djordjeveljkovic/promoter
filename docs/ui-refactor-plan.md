# UI Refactor Plan — Promoteri Dashboard

> **Goal:** Standardise the visual language of every page in the
> Promoteri app (Laravel 12 + Livewire 3 + Tailwind 4). No business logic
> changes. Bottom-up approach: every controller-rendered blade is
> migrated **first**, then layouts and chrome are migrated **last**.

---

## 0. ⚡ SESSION RECOVERY — read this first in any new session

> **Paste this block at the start of a new conversation so the AI has
> full context.** Update the "Last completed step" line as you go.

### Project context

- **Stack:** Laravel 12, Livewire 3, Volt, Livewire Flux 2.1
  (being phased out), Tailwind CSS 4, Alpine.js
- **Layouts:** `components/layouts/app.blade.php` (dashboard) and
  `components/layouts/auth/{card,split,simple}.blade.php` (auth)
- **Source tree:** every controller returns a `view('pages.<area>.<page>')`
  or renders a `livewire/...` component. The full route map is in
  `routes/web.php` and `routes/auth.php`.
- **Status helper:** `app/Support/Status.php` (single source of truth for
  the 4 duplicated `$jobStatusColors` arrays — see §5 Step 37).

### Refactor status

| | |
| --- | --- |
| **Components created** | ✅ All 24 `<x-ui.*>` components + `Status` class |
| **Existing files modified** | ❌ None yet |
| **Last completed step** | _none yet — start with **Step 1** below_ |

### User decisions (locked in, do not re-question)

1. **Flash messages** → keep the floating **top-right toast**
2. **Auth & settings** → **unify** under `<x-ui.*>`, **remove** all `<flux:*>`
3. **`pages/promoters/help.blade.php`** → **do not touch** (out of scope)
4. **Dark mode** → **light is default** (remove `class="dark"` from
   `<html>`); keep `dark:` variants in every component so the user can
   re-enable dark mode by adding the class back
5. **`<x-ui.input>`** → **standalone Tailwind** (no Flux wrapping)
6. **Pilot page** → `admin/promoters/index.blade.php`, make it responsive

### Style guide — single source of truth

| Token | Light | Dark |
| --- | --- | --- |
| Page bg | `bg-zinc-50` | `dark:bg-zinc-900` |
| Card bg | `bg-white` | `dark:bg-zinc-900/60` |
| Border | `border-zinc-200` | `dark:border-zinc-800` |
| Body text | `text-zinc-900` | `dark:text-zinc-50` |
| Muted text | `text-zinc-500` | `dark:text-zinc-400` |
| Primary action | `bg-indigo-600 hover:bg-indigo-700 text-white` | (same) |
| Danger | `bg-rose-600 hover:bg-rose-700 text-white` | (same) |
| Success | `bg-emerald-600 hover:bg-emerald-700 text-white` | (same) |
| Warning | `bg-amber-500 hover:bg-amber-600 text-white` | (same) |
| Neutral button | `bg-white text-zinc-700 ring-1 ring-inset ring-zinc-300` | `dark:bg-zinc-900 dark:text-zinc-200 dark:ring-zinc-700` |
| Card radius | `rounded-xl` | (same) |
| Button radius | `rounded-lg` | (same) |

Status colour map (in `App\Support\Status::VARIANTS`):

| Status key | Variant | Colour family |
| --- | --- | --- |
| `pending` | `warning` | amber |
| `processing` | `info` | sky |
| `failed`, `failed_clickable` | `danger` | rose |
| `blocked`, `unknown`, `N/A` | `neutral` | zinc |
| `completed`, `sent` | `success` | emerald |

### Component API (quick reference)

```blade
{{-- Buttons & links --}}
<x-ui.button variant="primary" size="md" :href="route('…')" icon="plus">Add</x-ui.button>
<x-ui.button variant="danger" size="sm" type="submit" wire:click="delete">Delete</x-ui.button>
<x-ui.link variant="danger" :href="route('…')" icon="trash">Delete</x-ui.link>
{{-- variants: primary | secondary | danger | success | warning | ghost | link --}}
{{-- sizes: sm | md | lg --}}

{{-- Icons (28 names) --}}
<x-ui.icon name="plus" class="h-4 w-4" />
{{-- Names: plus, minus, search, x-mark, check, chevron-{down,up,left,right},
   arrow-{left,right,path,up,down,trending-up,up-right,down-right},
   pencil-square, trash, eye, calendar, banknotes, chart-bar, ticket,
   users, user, envelope, cog, home, shopping-bag, currency-dollar,
   user-group, document-duplicate --}}

{{-- Forms --}}
<x-ui.field label="Email" for="email" :error="$errors->first('email')" required>
    <x-ui.input id="email" name="email" type="email" :value="old('email')" required />
</x-ui.field>
<x-ui.input leadingIcon="search" placeholder="Search…" />
<x-ui.textarea :error="$errors->first('body')" rows="5" />
<x-ui.select :options="$ticketTypes->pluck('name','id')" :error="$errors->first('ticket_type_id')" />
<x-ui.select><option value="…">…</option></x-ui.select>  {{-- slot mode --}}
<x-ui.checkbox label="Remember me" wire:model="remember" />
<x-ui.radio name="type" value="percentage" label="Percentage" />

{{-- Layout --}}
<x-ui.page-header :title="__('…')" :subtitle="__('…')" :eyebrow="__('…')">
    <x-slot:actions>
        <x-ui.button variant="primary" :href="route('…')" icon="plus">Add</x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

<x-ui.card>
    <x-ui.card.header title="Title" :subtitle="__('…')">
        <x-slot:actions>
            <x-ui.button variant="secondary" size="sm">Action</x-ui.button>
        </x-slot:actions>
    </x-ui.card.header>
    {{-- body content --}}
</x-ui.card>

{{-- KPI strip & stat card --}}
<x-ui.kpi-strip :cols="4">
    <x-ui.stat-card label="Revenue" :value="number_format($rev,2)" icon="banknotes" tone="success" />
    <x-ui.stat-card label="Orders" :value="number_format($orders)" icon="shopping-bag" tone="indigo" />
</x-ui.kpi-strip>

{{-- Status & badges --}}
<x-ui.status-pill :status="$order->job_status" />
<x-ui.status-pill :status="$order->job_status" clickable />
<x-ui.badge variant="success" icon="check">Active</x-ui.badge>
{{-- variants: success | danger | warning | info | neutral | indigo --}}

{{-- Alerts --}}
<x-ui.alert variant="success" :dismissable="true" title="Saved!">{{ session('success') }}</x-ui.alert>
<x-ui.alert variant="danger" position="toast">{{ session('error') }}</x-ui.alert>
{{-- variants: success | danger | warning | info | neutral --}}
{{-- positions: inline (default) | toast (fixed top-right, with Alpine dismiss) --}}

{{-- Tables --}}
<x-ui.table>
    <x-ui.table-header>
        <x-ui.table-row>
            <x-ui.table-cell header>Name</x-ui.table-cell>
            <x-ui.table-cell header align="right">Total</x-ui.table-cell>
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        @forelse ($rows as $row)
            <x-ui.table-row>
                <x-ui.table-cell>{{ $row->name }}</x-ui.table-cell>
                <x-ui.table-cell align="right" numeric>{{ number_format($row->total, 2) }}</x-ui.table-cell>
            </x-ui.table-row>
        @empty
            <x-ui.table-row>
                <x-ui.table-cell colspan="2">
                    <x-ui.empty-state icon="users" title="No records yet" />
                </x-ui.table-cell>
            </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>

{{-- Empty state --}}
<x-ui.empty-state icon="ticket" title="No tickets yet" description="Create your first ticket to start selling.">
    <x-slot:actions>
        <x-ui.button variant="primary" :href="route('…')">Add ticket</x-ui.button>
    </x-slot:actions>
</x-ui.empty-state>

{{-- Filter form --}}
<x-ui.filter-form :action="route('admin.orders.index')">
    <x-ui.field label="Status" for="status_filter">
        <x-ui.select name="status_filter" onchange="this.form.submit()">
            <option value="">All</option>
            @foreach(\App\Support\Status::all() as $key => $variant)
                <option value="{{ $key }}">{{ ucfirst($key) }}</option>
            @endforeach
        </x-ui.select>
    </x-ui.field>
    <x-ui.button variant="primary" type="submit" icon="search">Filter</x-ui.button>
    <x-ui.button variant="secondary" :href="route('admin.orders.index')">Clear</x-ui.button>
</x-ui.filter-form>
```

### Component files (already created)

| Path | Component |
| --- | --- |
| `app/Support/Status.php` | Status → variant helper |
| `resources/views/components/ui/button.blade.php` | `<x-ui.button>` |
| `resources/views/components/ui/link.blade.php` | `<x-ui.link>` |
| `resources/views/components/ui/icon.blade.php` | `<x-ui.icon>` (28 SVGs) |
| `resources/views/components/ui/field.blade.php` | `<x-ui.field>` |
| `resources/views/components/ui/input.blade.php` | `<x-ui.input>` |
| `resources/views/components/ui/textarea.blade.php` | `<x-ui.textarea>` |
| `resources/views/components/ui/select.blade.php` | `<x-ui.select>` |
| `resources/views/components/ui/checkbox.blade.php` | `<x-ui.checkbox>` |
| `resources/views/components/ui/radio.blade.php` | `<x-ui.radio>` |
| `resources/views/components/ui/card.blade.php` | `<x-ui.card>` |
| `resources/views/components/ui/card/header.blade.php` | `<x-ui.card.header>` |
| `resources/views/components/ui/page-header.blade.php` | `<x-ui.page-header>` |
| `resources/views/components/ui/kpi-strip.blade.php` | `<x-ui.kpi-strip>` |
| `resources/views/components/ui/stat-card.blade.php` | `<x-ui.stat-card>` |
| `resources/views/components/ui/badge.blade.php` | `<x-ui.badge>` |
| `resources/views/components/ui/status-pill.blade.php` | `<x-ui.status-pill>` |
| `resources/views/components/ui/alert.blade.php` | `<x-ui.alert>` |
| `resources/views/components/ui/empty-state.blade.php` | `<x-ui.empty-state>` |
| `resources/views/components/ui/table.blade.php` | `<x-ui.table>` |
| `resources/views/components/ui/table/header.blade.php` | `<x-ui.table-header>` |
| `resources/views/components/ui/table/body.blade.php` | `<x-ui.table-body>` |
| `resources/views/components/ui/table/row.blade.php` | `<x-ui.table-row>` |
| `resources/views/components/ui/table/cell.blade.php` | `<x-ui.table-cell>` |
| `resources/views/components/ui/filter-form.blade.php` | `<x-ui.filter-form>` |

### When migrating a page, also reference

* `routes/web.php` and `routes/auth.php` for the route → blade map
* The corresponding controller (only edit controllers in **Step 37**)
* Live Alpine scripts (`orderStatusBoard`, `supremeOverview`,
  `ticketOrder`, `emailEditor`) — **never** rewrite these, only change
  the surrounding HTML

---

## 1. Implementation rules (apply to every step)

1. **No business logic changes.** Translation keys, controller logic,
   validation, route definitions all stay the same.
2. **No behaviour changes.** Form submissions, JS handlers, Alpine
   components, Livewire `wire:*` wiring all stay the same.
3. **All `<flux:*>` → `<x-ui.*>`.** Decision 2 says no Flux anywhere.
4. **Use `App\Support\Status` instead of `$jobStatusColors`.** When a
   page needs a status class string, call `Status::classes($key)`
   directly. Don't pass `jobStatusColors` from controllers anymore.
5. **Don't touch files outside the step's list.** If a step mentions
   `pages/admin/promoters/create.blade.php`, only that file changes.
6. **After each step, test the corresponding route in the browser**
   (light + dark if dark mode is opted in).
7. **Don't move on until the user confirms the step is good.**

---

## 2. Implementation order (37 small steps, ≤ 5 files each)

> **Bottom-up:** every controller-rendered blade is migrated first;
> layouts, partials, and chrome are migrated last. This way the
> `<x-ui.*>` components are validated on real pages before we touch the
> shared chrome that wraps them.

### Phase A — Admin pages (Steps 1–12)

#### Step 1 — Pilot: `admin/promoters/index.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `resources/views/pages/admin/promoters/index.blade.php` |
| **Route** | `GET /admin/promoters` → `AdminController::promoters` |
| **Components** | `page-header`, `button`, `link`, `table` family, `empty-state` |
| **Responsive** | Hide tickets/commission columns under `md:`; stack page-header on mobile |
| **Verify** | `/admin/promoters` renders, table sorts visually, edit/delete links work, empty state shows for no data, no Flux in source |

#### Step 2 — `admin/promoters/{create,edit}` (2 files)

| | |
| --- | --- |
| **Files** | `pages/admin/promoters/create.blade.php`, `pages/admin/promoters/edit.blade.php` |
| **Routes** | `GET /admin/promoter/create`, `GET /admin/promoter/edit/{id}` |
| **Components** | `page-header`, `field`, `input`, `button`, `alert` (errors), `link` (cancel) |
| **Verify** | Form fields render, validation errors show as `<x-ui.alert variant="danger">`, submit succeeds, cancel returns to index |

#### Step 3 — `admin/promoter_managers/index.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `pages/admin/promoter_managers/index.blade.php` |
| **Route** | `GET /admin/promoter-managers` → `PromoterManagerController::index` |
| **Components** | `page-header`, `button`, `link`, `table` family, `empty-state` |
| **Verify** | List renders, actions work, empty state correct |

#### Step 4 — `admin/promoter_managers/{create,edit}` (2 files)

| | |
| --- | --- |
| **Files** | `pages/admin/promoter_managers/create.blade.php`, `pages/admin/promoter_managers/edit.blade.php` |
| **Routes** | `GET /admin/promoter-manager/create`, `GET /admin/promoter-manager/edit/{id}` |
| **Components** | `page-header`, `field`, `input`, `button`, `alert`, `link` (cancel) |
| **Verify** | Both forms work, errors styled, cancel link returns to index |

#### Step 5 — `admin/ticket_type/index.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `pages/admin/ticket_type/index.blade.php` |
| **Route** | `GET /admin/tickets` → `TicketController::index` |
| **Components** | `page-header`, `button`, `link`, `table` family, `empty-state` |
| **Verify** | List renders, photo thumbnails visible, edit/delete work |

#### Step 6 — `admin/ticket_type/{create,edit}` (2 files)

| | |
| --- | --- |
| **Files** | `pages/admin/ticket_type/create.blade.php`, `pages/admin/ticket_type/edit.blade.php` |
| **Routes** | `GET /admin/ticket/create`, `GET /admin/ticket/edit/{id}` |
| **Components** | `page-header`, `field`, `input`, `button`, `alert`, `link` |
| **Note** | These are **largest** files (~25k chars each). Pay attention to the price-with-currency-suffix input and QR coordinates fieldset — keep all input fields functional. |
| **Verify** | File upload works, price field shows currency suffix, QR fields render correctly, submit succeeds |

#### Step 7 — `admin/orders/index.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `pages/admin/orders/index.blade.php` |
| **Route** | `GET /admin/orders` → `AdminOrderController::index` |
| **Components** | `page-header`, `filter-form`, `status-pill`, `badge`, `link`, `table` family, `empty-state`, `alert` |
| **Note** | **Preserve the `orderStatusBoard` Alpine component and the toast `<div>` at the bottom of the file unchanged** — only HTML surrounding it changes. Use `Status::classes()` instead of `$jobStatusColors['failed_clickable']` etc. |
| **Verify** | Filters submit, status pills render, live updates via Echo still work, toast for off-page updates still appears |

#### Step 8 — `admin/orders/create.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `pages/admin/orders/create.blade.php` |
| **Route** | `GET /admin/order/create` → `AdminOrderController::create` |
| **Note** | `pages/admin/orders/edit.blade.php` is 4 lines and effectively empty — skip it. |
| **Components** | `page-header`, `field`, `input`, `button`, `alert`, `link` |
| **Verify** | Form renders, submit succeeds, cancel works |

#### Step 9 — `livewire/admin/order-details.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `resources/views/livewire/admin/order-details.blade.php` |
| **Route** | `GET /admin/orders/{id}` → `App\Livewire\Admin\OrderDetails` |
| **Components** | `page-header`, `kpi-strip`, `stat-card`, `card`, `field`, `input`, `button`, `status-pill`, `alert`, `empty-state`, `checkbox` (the "select ticket" one), `link` (back) |
| **Note** | **Do not touch any `wire:click`, `wire:model`, `wire:submit` attributes** — only the surrounding HTML. The togglePaidInput form, the regenerate button, the bulk activate/deactivate buttons must all keep their `wire:*` wiring. |
| **Verify** | Page renders, paid-amount editor works, ticket selection works, bulk activate/deactivate works, regenerate images works, download QR works |

#### Step 10 — `admin/email_settings/index.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `pages/admin/email_settings/index.blade.php` |
| **Route** | `GET /admin/email-settings` → `EmailSettingsController::index` |
| **Components** | `page-header`, `card`, `card-header`, `field`, `input`, `select`, `textarea`, `checkbox`, `button`, `alert`, `table` family (for the templates list), `badge` (source type) |
| **Note** | Two tabs (`config` / `templates`) — Alpine `x-data="{ tab: @js($tab) }"` stays; the **two `<div x-show="tab === '…'">` panels get the new component markup** inside them. |
| **Verify** | Tab switching works, mail config form submits, test email form submits, templates list renders with source badges, edit/duplicate/activate/delete buttons work |

#### Step 11 — `admin/email_settings/create.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `pages/admin/email_settings/create.blade.php` |
| **Route** | `GET /admin/email-settings/templates/create` → `EmailSettingsController::createTemplate` |
| **Components** | `page-header`, `card`, `field`, `input`, `textarea`, `radio`, `checkbox`, `button`, `alert`, `link` (back / cancel) |
| **Note** | Alpine `x-data="{ sourceType: @js(old('source_type', 'view')) }"` for the view/html radio toggle stays. |
| **Verify** | Form submits, source type toggle hides/shows the right fields, validation errors styled |

#### Step 12 — `admin/email_settings/edit.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `pages/admin/email_settings/edit.blade.php` |
| **Route** | `GET /admin/email-settings/templates/{emailTemplate}/edit` |
| **Components** | `page-header`, `card`, `field`, `input`, `checkbox`, `button`, `alert`, `link` |
| **Note** | The split-view code editor (left) and preview iframe (right) are **sensitive**: keep the `<textarea id="source-editor">` and the `emailEditor()` Alpine component intact — only wrap them in `<x-ui.card>`. The iframe refresh button must keep its `onclick` handler. |
| **Verify** | Metadata form submits, code editor renders, preview iframe loads, refresh button works, save source button works |

### Phase B — Supreme admin + sub-promoter pages (Steps 13–14)

#### Step 13 — `pages/supremeadmin/overview.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `pages/supremeadmin/overview.blade.php` |
| **Route** | `GET /superadmin/overview` → `SupremeAdminController::overview` |
| **Components** | `page-header`, `stat-card` x7 (in a `kpi-strip :cols="7"`), `filter-form`, `field`, `input`, `select`, `button`, `card`, `table` family, `empty-state`, `alert` |
| **Note** | **Preserve the `supremeOverview()` Alpine component and the `@php` helpers `$fmt`, `$sortUrl`, `$sortIcon`, `$owedClass` at the top of the file unchanged**. The collapsible sub-promoter detail row (`<tr id="subs-…">`) is the second nested table — it should also use `<x-ui.table>` family. |
| **Verify** | KPI strip renders, search submits, filter rows add/remove via Alpine, sort links work, collapsible rows expand, owed amounts colour-coded |

#### Step 14 — Sub-promoter pages (2 files)

| | |
| --- | --- |
| **Files** | `pages/subpromoters/dashboard.blade.php`, `pages/subpromoters/orders.blade.php` |
| **Routes** | `GET /sub-promoter/dashboard`, `GET /sub-promoter/orders` |
| **Components** | `page-header`, `stat-card`, `card`, `field`, `table` family, `status-pill`, `button`, `link`, `empty-state`, `alert` |
| **Verify** | Both pages render, stats correct, status pills styled, view-all / new-order buttons work |

### Phase C — Promoter-manager pages (Steps 15–17)

#### Step 15 — `pages/promoter_managers/dashboard.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `pages/promoter_managers/dashboard.blade.php` |
| **Route** | `GET /promoter-manager/dashboard` → `PromoterManagerController::dashboard` |
| **Components** | `page-header`, `kpi-strip` (4 cells, financials), `kpi-strip` (3 cells, team overview), `stat-card` (each cell), `card`, `table` family (desktop), mobile card `<ul>` (keep existing markup, just clean up classes), `link`, `button`, `empty-state` |
| **Verify** | Both KPI strips render, mobile list < md, desktop table ≥ md, edit links work, add sub-promoter button works |

#### Step 16 — `pages/promoter_managers/sub_promoters/index.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `pages/promoter_managers/sub_promoters/index.blade.php` |
| **Route** | `GET /promoter-manager/sub-promoters` |
| **Components** | `page-header`, `table` family, `badge` (per-ticket-type override pills), `empty-state`, `link`, `button` |
| **Verify** | List renders, per-type override pills styled, edit/delete work, empty state correct |

#### Step 17 — `pages/promoter_managers/sub_promoters/{create,edit}` (2 files)

| | |
| --- | --- |
| **Files** | `pages/promoter_managers/sub_promoters/create.blade.php`, `pages/promoter_managers/sub_promoters/edit.blade.php` |
| **Routes** | `GET /promoter-manager/sub-promoter/create`, `GET /promoter-manager/sub-promoter/edit/{id}` |
| **Components** | `page-header`, `card` (commission split section), `field`, `input`, `button`, `alert`, `link` |
| **Note** | The Alpine `x-data="{ modes: {} }"` and per-type `x-data='{ mode: @json($mode) }'` toggles stay. Convert the per-type percentage/fixed toggle into two `<x-ui.button>` (or keep raw `<button>` with new Tailwind classes). |
| **Verify** | Form submits, percentage/fixed toggle hides/shows correct input, validation errors styled |

### Phase D — Promoter pages (Steps 18–21)

#### Step 18 — `pages/promoters/dashboard.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `pages/promoters/dashboard.blade.php` |
| **Route** | `GET /promoter/dashboard` → `PromoterController::dashboard` |
| **Components** | `page-header`, `kpi-strip` (financials), `kpi-strip` (general performance), `stat-card` (each cell), `card`, `table` family |
| **Note** | Migrate the gray palette → zinc; convert the two "section h2" headings to be inside `<x-ui.kpi-strip>` headers. |
| **Verify** | All KPIs render, top-ticket-sales table renders, manager banner (if present) styled |

#### Step 19 — `pages/promoters/orders/index.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `pages/promoters/orders/index.blade.php` |
| **Route** | `GET /promoter/orders` → `OrderController::index` |
| **Components** | `page-header`, `alert`, `table` family, `status-pill`, `empty-state`, `link`, `button` |
| **Note** | Replace `$jobStatusColors` lookup with `Status::classes($order->job_status)`. The error-row JS at the bottom (toggles `id="error-row-…"`) stays unchanged. |
| **Verify** | List renders, status pills coloured, failed click-to-expand still works, view/resend buttons work |

#### Step 20 — `pages/promoters/orders/create.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `pages/promoters/orders/create.blade.php` |
| **Route** | `GET /order/create` → `OrderController::create` |
| **Components** | `page-header`, `field`, `input`, `select`, `button`, `alert`, `link` |
| **Note** | The `ticketOrder()` Alpine component and the "Add item" button, the items table, the hidden inputs for items, the total — **all stay unchanged**. Only the outer field markup and buttons get the new components. |
| **Verify** | Email field, ticket-type select, quantity input, "add item" button all work; items table populates; submit creates an order |

#### Step 21 — `pages/promoters/orders/show.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `pages/promoters/orders/show.blade.php` |
| **Route** | `GET /promoter/orders/{order}` → `OrderController::show` |
| **Components** | `page-header`, `kpi-strip` (4-cell summary), `kpi-strip` (2-cell seller/commission), `stat-card` (each cell), `card`, `table` family, `status-pill`, `link` (back), `empty-state` |
| **Verify** | Summary tiles render, items table renders, tickets grid renders, download all button works, status pill coloured |

### Phase E — Welcome + settings (Steps 22–24)

#### Step 22 — `welcome.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `resources/views/welcome.blade.php` |
| **Route** | `GET /` |
| **Components** | None (just adjust the body classes to use zinc tokens; keep the `<a href="{{ route('login') }}">` markup) |
| **Verify** | `/` renders, login link visible |

#### Step 23 — Settings: `profile` + `password` (2 files)

| | |
| --- | --- |
| **Files** | `livewire/settings/profile.blade.php`, `livewire/settings/password.blade.php` |
| **Routes** | `GET /settings/profile`, `GET /settings/password` (Livewire) |
| **Components** | `<x-ui.field>` + `<x-ui.input>` (replace `<flux:input>`), `<x-ui.button>` (replace `<flux:button>`), `<x-ui.link>` (replace `<flux:link>`), `<x-ui.alert>` (replace `<x-action-message>`) |
| **Note** | The wrapper `<x-settings.layout>` is replaced in Step 34 — leave the layout wrapper alone for now, only change the inputs/buttons inside. |
| **Verify** | Profile update works (Livewire), password update works, action messages appear and dismiss |

#### Step 24 — Settings: `delete-user-form` + `appearance` (2 files)

| | |
| --- | --- |
| **Files** | `livewire/settings/delete-user-form.blade.php`, `livewire/settings/appearance.blade.php` |
| **Components** | `field`, `input`, `button`, `alert`, `link`, `card` |
| **Verify** | Delete-user form requires password and submits; appearance toggles (if any) work |

### Phase F — Auth (Steps 25–28)

#### Step 25 — `livewire/auth/login.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `livewire/auth/login.blade.php` |
| **Route** | `GET /login` (Livewire) |
| **Components** | `field`, `input`, `checkbox`, `button`, `link` (replaces `<flux:link>` "forgot password") |
| **Verify** | Login submits, "remember me" works, forgot-password link navigates correctly |

#### Step 26 — `livewire/auth/register.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `livewire/auth/register.blade.php` |
| **Route** | `GET /register` (Livewire) |
| **Components** | `field`, `input`, `checkbox`, `button`, `link` |
| **Verify** | Registration submits, terms checkbox required, login link navigates |

#### Step 27 — Auth: `forgot-password` + `reset-password` (2 files)

| | |
| --- | --- |
| **Files** | `livewire/auth/forgot-password.blade.php`, `livewire/auth/reset-password.blade.php` |
| **Routes** | `GET /forgot-password`, `GET /reset-password/{token}` |
| **Components** | `field`, `input`, `button`, `link`, `alert` |
| **Verify** | Forgot-password submits and shows status; reset-password accepts new password |

#### Step 28 — Auth: `verify-email` + `confirm-password` (2 files)

| | |
| --- | --- |
| **Files** | `livewire/auth/verify-email.blade.php`, `livewire/auth/confirm-password.blade.php` |
| **Routes** | `GET /verify-email`, `GET /confirm-password` |
| **Components** | `field`, `input`, `button`, `link`, `alert` |
| **Verify** | Resend verification link works; confirm-password submits |

### Phase G — Layouts & chrome (parent nodes — Steps 29–35)

> Now we move **up** the tree to the shared layouts. All pages should
> already be on `<x-ui.*>` by the time we start this phase.

#### Step 29 — `components/layouts/app.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `resources/views/components/layouts/app.blade.php` |
| **Note** | This is the page wrapper used by every dashboard page. Swap the inner content card from `bg-white dark:bg-zinc-800` to `<x-ui.card>` style classes. Keep `<x-layouts.app.sidebar>` slot at the top — the sidebar is migrated in Step 30. |
| **Verify** | All dashboard pages still render; page-level flash container (if any) is consistent |

#### Step 30 — `components/layouts/app/sidebar.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `resources/views/components/layouts/app/sidebar.blade.php` |
| **Note** | Replace any remaining `gray-*` palette tokens with `zinc-*`. The navlist and profile dropdowns can stay as `<flux:navlist>` / `<flux:profile>` for now (they live in their own scope and aren't part of the user-facing button/table standardisation). |
| **Verify** | Sidebar shows correct items per role, current page highlighted, mobile drawer toggles |

#### Step 31 — `components/layouts/app/header.blade.php` (1 file)

| | |
| --- | --- |
| **File** | `resources/views/components/layouts/app/header.blade.php` |
| **Note** | Same as sidebar — align colours to zinc, keep `<flux:navbar>` / `<flux:profile>` etc. |
| **Verify** | Top header renders on mobile, profile dropdown works, navbar search button works |

#### Step 32 — Auth layouts (4 files)

| | |
| --- | --- |
| **Files** | `components/layouts/auth.blade.php`, `components/layouts/auth/card.blade.php`, `components/layouts/auth/split.blade.php`, `components/layouts/auth/simple.blade.php` |
| **Note** | All four are already mostly on modern palette. Only change body classes from `bg-neutral-100` / `bg-muted` to `bg-zinc-50` to match the dashboard tokens. |
| **Verify** | `/login`, `/register`, `/forgot-password`, `/reset-password/{token}`, `/verify-email`, `/confirm-password` all render correctly |

#### Step 33 — Misc small components (5 files)

| | |
| --- | --- |
| **Files** | `components/action-message.blade.php`, `components/app-logo.blade.php`, `components/app-logo-icon.blade.php`, `components/auth-header.blade.php`, `components/auth-session-status.blade.php` |
| **Note** | These are small helper components. Most don't need visual changes — they already use neutral colours. Only update if they explicitly use a deprecated palette. |
| **Verify** | Auth pages still render header, logos still appear, action messages still show |

#### Step 34 — Settings layout + heading (2 files)

| | |
| --- | --- |
| **Files** | `components/settings/layout.blade.php`, `partials/settings-heading.blade.php` |
| **Note** | `<x-settings.layout>` wraps all settings pages. Replace the inner card classes with zinc tokens. |
| **Verify** | `/settings/profile`, `/settings/password`, `/settings/appearance` all render with consistent shell |

#### Step 35 — `head` + `flash-messages` (2 files)

| | |
| --- | --- |
| **Files** | `partials/head.blade.php`, `components/flash-messages.blade.php` |
| **Note** | **`head.blade.php`** — remove the inline `<style>` block that defines input/select styles (those live in `<x-ui.input>` / `<x-ui.select>` now). **Remove `class="dark"` from `<html>`** (per Decision 4, light is default). **`flash-messages.blade.php`** — re-implement on top of `<x-ui.alert position="toast">`. Keep the Livewire `morph.updated` hook so newly flashed messages on Livewire actions still appear. Keep the 5/7/10-second auto-dismiss timeouts. |
| **Verify** | Toasts appear top-right, auto-dismiss, dismiss button works, Livewire-flashed messages appear, no inline `<style>` for input/select in `<head>`, `<html>` no longer has `class="dark"` |

### Phase H — CSS & controllers (Steps 36–37)

#### Step 36 — `resources/css/app.css` (1 file)

| | |
| --- | --- |
| **File** | `resources/css/app.css` |
| **Note** | Remove the `@import '../../vendor/livewire/flux/dist/flux.css';` line (no Flux used anymore). Keep `@custom-variant dark (&:where(.dark, .dark *));` so dark mode still works if the user opts in. |
| **Verify** | `npm run build` succeeds, app styles still load |

#### Step 37 — Controllers cleanup (4 files)

| | |
| --- | --- |
| **Files** | `app/Http/Controllers/AdminOrderController.php`, `app/Http/Controllers/OrderController.php`, `app/Http/Controllers/SubPromoterController.php`, `app/Http/Controllers/OrderController1.php` |
| **Note** | **Remove** the local `$jobStatusColors = [ … ]` array from each of the first three controllers. **Remove** the `'jobStatusColors'` key from the `compact(...)` call. The view now reads `App\Support\Status::classes(...)` directly. **`OrderController1.php`** is not referenced in `routes/web.php` — confirm with the user, then **delete** the whole file. |
| **Verify** | `/admin/orders`, `/promoter/orders`, `/sub-promoter/orders` still show coloured status pills (Status::classes provides the same colours). No "Undefined variable: jobStatusColors" errors. |

---

## 3. Out of scope

| File | Reason |
| --- | --- |
| `resources/views/pages/promoters/help.blade.php` | User decision 3 |
| `resources/views/emails/customer/tickets.blade.php` | Different audience (email) |
| `resources/views/flux/icon/*`, `resources/views/flux/navlist/*` | Internal Flux components, not user-facing |
| `resources/views/components/app-logo*.blade.php` | Visual identity, not part of the dashboard standardisation |
| `app/Http/Controllers/OrderController1.php` | Likely dead code — confirmed in Step 37 |
| `app/Http/Controllers/PaymentController.php` | Doesn't render any blade directly (only handles POSTs) |

---

## 4. Risks

| Risk | Mitigation |
| --- | --- |
| Layout regressions on dashboard pages | Browser-test after every step in both light and dark (if opted in) |
| Alpine component breakage | Never touch `orderStatusBoard`, `supremeOverview`, `ticketOrder`, `emailEditor` — only their surrounding HTML |
| Livewire wiring breakage | Never touch `wire:click`, `wire:model`, `wire:submit`, `wire:loading` — only surrounding HTML |
| Translation regression | No translation key changes; only markup wrapping |
| Removing `class="dark"` from `<html>` flips the app to light | Done in a single small commit (Step 35) so it's easy to revert if light-mode breaks some view |
| Flux CSS import removal breaks auth pages | Done in Step 36, **after** all auth pages are migrated (Step 25–28) |

---

## 5. Quick reference — recurring patterns

### List page (table + filter + empty state)

```blade
<x-layouts.app :title="__('…')">
    <div class="space-y-6">
        <x-ui.page-header :title="__('…')" :subtitle="__('…')">
            <x-slot:actions>
                <x-ui.button variant="primary" :href="route('…')" icon="plus">{{ __('…') }}</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.filter-form :action="route('…')">
            <x-ui.field label="Status" for="status">
                <x-ui.select name="status" onchange="this.form.submit()">…</x-ui.select>
            </x-ui.field>
            <x-ui.input name="search" leadingIcon="search" placeholder="Search…" />
            <x-ui.button type="submit" variant="primary" icon="search">Filter</x-ui.button>
            <x-ui.button variant="secondary" :href="route('…')">Clear</x-ui.button>
        </x-ui.filter-form>

        <x-ui.card>
            <x-ui.table>
                <x-ui.table-header>
                    <x-ui.table-row>
                        <x-ui.table-cell header>Name</x-ui.table-cell>
                        <x-ui.table-cell header align="right">Total</x-ui.table-cell>
                    </x-ui.table-row>
                </x-ui.table-header>
                <x-ui.table-body>
                    @forelse ($rows as $row)
                        <x-ui.table-row>
                            <x-ui.table-cell>{{ $row->name }}</x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric>{{ number_format($row->total, 2) }}</x-ui.table-cell>
                        </x-ui.table-row>
                    @empty
                        <x-ui.table-row>
                            <x-ui.table-cell colspan="2">
                                <x-ui.empty-state icon="users" title="No rows yet" />
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @endforelse
                </x-ui.table-body>
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
```

### Form page (create / edit)

```blade
<x-layouts.app :title="__('…')">
    <div class="space-y-6 max-w-3xl">
        <x-ui.page-header :title="__('…')">
            <x-slot:actions>
                <x-ui.link variant="secondary" :href="route('…')">{{ __('Cancel') }}</x-ui.link>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.card>
            <form method="POST" action="{{ route('…') }}" class="space-y-5 p-6">
                @csrf
                @method('PUT') {{-- if editing --}}

                <x-ui.field label="Name" for="name" :error="$errors->first('name')" required>
                    <x-ui.input id="name" name="name" :value="old('name', $item->name ?? '')" required />
                </x-ui.field>

                <x-ui.field label="Email" for="email" :error="$errors->first('email')" required>
                    <x-ui.input id="email" name="email" type="email" :value="old('email', $item->email ?? '')" required />
                </x-ui.field>

                <div class="flex justify-end gap-2 pt-2">
                    <x-ui.button variant="secondary" :href="route('…')">Cancel</x-ui.button>
                    <x-ui.button variant="primary" type="submit">Save</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
```

### Dashboard (KPI strip + content)

```blade
<x-layouts.app :title="__('…')">
    <div class="space-y-6">
        <x-ui.page-header :title="__('…')" :subtitle="__('…')" />

        <x-ui.kpi-strip :cols="4">
            <x-ui.stat-card label="Revenue" :value="number_format($rev, 2)" icon="banknotes" tone="success" />
            <x-ui.stat-card label="Orders" :value="number_format($orders)" icon="shopping-bag" tone="indigo" />
            …
        </x-ui.kpi-strip>

        <x-ui.card>
            <x-ui.card.header :title="__('Recent orders')" />
            <x-ui.table>
                <x-ui.table-header>…</x-ui.table-header>
                <x-ui.table-body>…</x-ui.table-body>
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>
```

---

## 6. Tracking

| Step | Status | Verified on | Notes |
| --- | --- | --- | --- |
| 1 — Pilot `admin/promoters/index` | ⬜ | | |
| 2 — `admin/promoters/{create,edit}` | ⬜ | | |
| 3 — `admin/promoter_managers/index` | ⬜ | | |
| 4 — `admin/promoter_managers/{create,edit}` | ⬜ | | |
| 5 — `admin/ticket_type/index` | ⬜ | | |
| 6 — `admin/ticket_type/{create,edit}` | ⬜ | | |
| 7 — `admin/orders/index` | ⬜ | | |
| 8 — `admin/orders/create` | ⬜ | | |
| 9 — `livewire/admin/order-details` | ⬜ | | |
| 10 — `admin/email_settings/index` | ⬜ | | |
| 11 — `admin/email_settings/create` | ⬜ | | |
| 12 — `admin/email_settings/edit` | ⬜ | | |
| 13 — `supremeadmin/overview` | ⬜ | | |
| 14 — `subpromoters/{dashboard,orders}` | ⬜ | | |
| 15 — `promoter_managers/dashboard` | ⬜ | | |
| 16 — `promoter_managers/sub_promoters/index` | ⬜ | | |
| 17 — `promoter_managers/sub_promoters/{create,edit}` | ⬜ | | |
| 18 — `promoters/dashboard` | ⬜ | | |
| 19 — `promoters/orders/index` | ⬜ | | |
| 20 — `promoters/orders/create` | ⬜ | | |
| 21 — `promoters/orders/show` | ⬜ | | |
| 22 — `welcome` | ⬜ | | |
| 23 — settings `profile` + `password` | ⬜ | | |
| 24 — settings `delete-user-form` + `appearance` | ⬜ | | |
| 25 — auth `login` | ⬜ | | |
| 26 — auth `register` | ⬜ | | |
| 27 — auth `forgot-password` + `reset-password` | ⬜ | | |
| 28 — auth `verify-email` + `confirm-password` | ⬜ | | |
| 29 — layout `app.blade.php` | ⬜ | | |
| 30 — layout `app/sidebar` | ⬜ | | |
| 31 — layout `app/header` | ⬜ | | |
| 32 — auth layouts (4 files) | ⬜ | | |
| 33 — misc components (5 files) | ⬜ | | |
| 34 — `settings/layout` + `settings-heading` | ⬜ | | |
| 35 — `head` + `flash-messages` | ⬜ | | |
| 36 — `app.css` | ⬜ | | |
| 37 — controllers cleanup (4 files) | ⬜ | | |

---

**Next step:** start with **Step 1 — Pilot `admin/promoters/index.blade.php`**.
The pilot will validate every component pattern before we sweep the
remaining 36 steps.
