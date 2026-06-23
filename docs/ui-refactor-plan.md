# UI Refactor Plan — Promoteri Dashboard

> **Status:** Confirmed by user — ready to start code migration.
> **Scope:** Standardise the visual language so future features look
> consistent across every page. **No business logic changes.**

---

## 1. Decisions confirmed by user

| # | Question | Decision |
| --- | --- | --- |
| 1 | Flash messages — keep the floating top-right toast or move to inline alerts? | **Keep floating top-right** — re-implement the existing `x-flash-messages` toast on top of `<x-ui.alert position="toast">` |
| 2 | Auth/settings — keep `<flux:*>` or unify under `<x-ui.*>`? | **Unify everything, no Flux** — auth & settings pages will use `<x-ui.*>` too |
| 3 | `pages/promoters/help.blade.php` — translate + migrate, or leave untouched? | **Leave untouched** — out of scope |
| 4 | Dark mode — stay always-dark or support light? | **Force light, dark is optional** — remove `class="dark"` from `<html>`, keep `dark:` variants everywhere so the user can toggle later |
| 5 | `<x-ui.input>` — standalone or wrap `<flux:input>`? | **Standalone Tailwind** |
| 6 | Pilot page — happy with `admin/promoters/index.blade.php`? | **Yes** — and make it more responsive |

---

## 2. Component inventory (built)

> **All files below already exist** (created during this planning phase). They
> are the only new files; no existing resource was modified.

### 2.1 Backend support

| Path | Purpose |
| --- | --- |
| `app/Support/Status.php` | Single source of truth for status → variant mapping; replaces the 4 duplicated `$jobStatusColors` arrays |

### 2.2 UI components — atoms

| Path | Component | Key props |
| --- | --- | --- |
| `resources/views/components/ui/button.blade.php` | `<x-ui.button>` | `variant` (primary/secondary/danger/success/warning/ghost/link), `size` (sm/md/lg), `href`, `icon`, `iconTrailing`, `loading`, `disabled`, `fullWidth` |
| `resources/views/components/ui/link.blade.php` | `<x-ui.link>` | `variant` (primary/secondary/danger/success/warning/muted), `size`, `href`, `icon`, `iconTrailing` |
| `resources/views/components/ui/icon.blade.php` | `<x-ui.icon>` | `name` (plus, search, x-mark, chevron-*, arrow-*, pencil-square, trash, eye, calendar, banknotes, chart-bar, ticket, users, user, envelope, cog, home, arrow-trending-up, arrow-up-right, arrow-down-right, shopping-bag, currency-dollar, user-group, document-duplicate, arrow-up, arrow-down), `class` |
| `resources/views/components/ui/field.blade.php` | `<x-ui.field>` | `label`, `for`, `error`, `hint`, `required`, `inline` |
| `resources/views/components/ui/input.blade.php` | `<x-ui.input>` | `type`, `size`, `error`, `leadingIcon`, `trailingIcon`, `disabled` |
| `resources/views/components/ui/textarea.blade.php` | `<x-ui.textarea>` | `size`, `error`, `rows` |
| `resources/views/components/ui/select.blade.php` | `<x-ui.select>` | `size`, `error`, `options` (array) **or** slot, `placeholder` |
| `resources/views/components/ui/checkbox.blade.php` | `<x-ui.checkbox>` | `label`, `error` |
| `resources/views/components/ui/radio.blade.php` | `<x-ui.radio>` | `label`, `value` |

### 2.3 UI components — molecules

| Path | Component | Notes |
| --- | --- | --- |
| `resources/views/components/ui/card.blade.php` | `<x-ui.card>` | Card wrapper with zinc palette, `padding`, `overflow` props |
| `resources/views/components/ui/card/header.blade.php` | `<x-ui.card.header>` | Card header with `title`, `subtitle`, `actions` slot |
| `resources/views/components/ui/page-header.blade.php` | `<x-ui.page-header>` | Standard page header with `title`, `subtitle`, `eyebrow`, `actions` slot |
| `resources/views/components/ui/kpi-strip.blade.php` | `<x-ui.kpi-strip>` | 4-cell KPI grid; `cols` 2/3/4/5 |
| `resources/views/components/ui/stat-card.blade.php` | `<x-ui.stat-card>` | Single KPI tile; `label`, `value`, `subtext`, `icon`, `tone`, `trend`, `trendUp` |
| `resources/views/components/ui/badge.blade.php` | `<x-ui.badge>` | Pill with variants + sizes |
| `resources/views/components/ui/status-pill.blade.php` | `<x-ui.status-pill>` | Order status pill; reads `App\Support\Status` |
| `resources/views/components/ui/alert.blade.php` | `<x-ui.alert>` | `variant`, `position` (inline/toast), `dismissable`, `title` |
| `resources/views/components/ui/empty-state.blade.php` | `<x-ui.empty-state>` | `icon`, `title`, `description`, `actions` slot |

### 2.4 UI components — tables

| Path | Component | Notes |
| --- | --- | --- |
| `resources/views/components/ui/table.blade.php` | `<x-ui.table>` | Wrapper, responsive `overflow-x-auto` |
| `resources/views/components/ui/table/header.blade.php` | `<x-ui.table-header>` | `<thead>` with zinc-50 background |
| `resources/views/components/ui/table/body.blade.php` | `<x-ui.table-body>` | `<tbody>` with divider rows |
| `resources/views/components/ui/table/row.blade.php` | `<x-ui.table-row>` | `<tr>` with hover by default |
| `resources/views/components/ui/table/cell.blade.php` | `<x-ui.table-cell>` | `<th>`/`<td>` with `header`, `align`, `numeric`, `nowrap`, `colspan`, `width` |

### 2.5 Filter form

| Path | Component |
| --- | --- |
| `resources/views/components/ui/filter-form.blade.php` | `<x-ui.filter-form>` (with `action`, `method`, `autosubmit`) |

---

## 3. Target visual language (canonical tokens)

These are baked into every component. Light is the default; `dark:`
variants are present everywhere so the user can opt in.

| Token | Light | Dark |
| --- | --- | --- |
| Page bg | `bg-zinc-50` | `dark:bg-zinc-900` |
| Card bg | `bg-white` | `dark:bg-zinc-900/60` |
| Border | `border-zinc-200` | `dark:border-zinc-800` |
| Primary text | `text-zinc-900` | `dark:text-zinc-50` |
| Muted text | `text-zinc-500` | `dark:text-zinc-400` |
| Primary action | `bg-indigo-600 hover:bg-indigo-700 text-white` | (same) |
| Danger | `bg-rose-600 hover:bg-rose-700 text-white` | (same) |
| Success | `bg-emerald-600 hover:bg-emerald-700 text-white` | (same) |
| Warning | `bg-amber-500 hover:bg-amber-600 text-white` | (same) |
| Neutral | `bg-white text-zinc-700 ring-1 ring-inset ring-zinc-300` | `dark:bg-zinc-900 dark:text-zinc-200 dark:ring-zinc-700` |
| Card radius | `rounded-xl` | (same) |
| Button radius | `rounded-lg` | (same) |
| Spacing scale | `gap-3`, `gap-4`, `p-4`, `p-5`, `p-6` | (same) |

Status colour map (in `App\Support\Status`):

```php
'pending'    => 'warning'   // amber
'processing' => 'info'      // sky
'failed'     => 'danger'    // rose
'blocked'    => 'neutral'   // zinc
'completed'  => 'success'   // emerald
'sent'       => 'success'   // emerald
'unknown'    => 'neutral'
'failed_clickable' => 'danger'
'N/A'        => 'neutral'
```

---

## 4. Implementation order

Bottom-up: prove the atoms work on the pilot, then sweep the rest.

1. **Phase 0 — Theme toggle** (already partly done, see §5)
2. **Phase 1 — Pilot page**: `admin/promoters/index.blade.php` + the small supporting controllers
3. **Phase 2 — Sweep admin pages**
4. **Phase 3 — Sweep promoter / promoter-manager / sub-promoter pages**
5. **Phase 4 — Sweep supreme-admin / settings / auth**
6. **Phase 5 — Layout chrome & global styles**
7. **Phase 6 — Cleanup**: remove duplicate `$jobStatusColors` arrays

---

## 5. Files that will need to be edited (the "todo")

> **Read-only list.** No edits yet. These are the resource files we will
> change when implementation begins. Each entry names the file and the kind
> of changes that will happen.

### 5.1 Layout & chrome

| # | File | Planned changes |
| --- | --- | --- |
| 1 | `resources/views/components/layouts/app.blade.php` | Swap inner content card from `bg-white dark:bg-zinc-800` to `bg-white dark:bg-zinc-900/60`; use `<x-ui.alert>` for the page-level flash container (or keep `<x-flash-messages>` as the toast, your call) |
| 2 | `resources/views/components/layouts/app/sidebar.blade.php` | Replace any remaining `gray-*` palette with `zinc-*` to match cards |
| 3 | `resources/views/components/layouts/app/header.blade.php` | Same as sidebar |
| 4 | `resources/views/components/layouts/auth/card.blade.php` | Already on modern palette — only minor tweaks (remove unused classes, adopt shared radius) |
| 5 | `resources/views/components/layouts/auth/split.blade.php` | Same |
| 6 | `resources/views/components/layouts/auth/simple.blade.php` | Same |
| 7 | `resources/css/app.css` | Import the (optional) `resources/css/components.css`; remove Flux import if no longer used (`@import '../../vendor/livewire/flux/dist/flux.css';`); ensure `@custom-variant dark (&:where(.dark, .dark *));` remains so `dark:` variants work |
| 8 | `resources/views/components/flash-messages.blade.php` | Re-implement using `<x-ui.alert position="toast">` (keeps top-right floating behaviour, auto-dismiss, Livewire hook integration) |
| 9 | `resources/views/partials/head.blade.php` | Remove the huge inline `<style>` block that defines input/select styles — those rules now live in `<x-ui.input>` and `<x-ui.select>`; also remove `class="dark"` from `<html>` (per Decision 4, light is default; user can re-add it to opt into dark mode) |
| 10 | `resources/views/welcome.blade.php` | Tweak body classes to use the new palette tokens (currently uses `bg-[#FDFDFC] dark:bg-[#0a0a0a]` which conflicts with the zinc palette). Keep Serbian/English copy intact |

### 5.2 Admin pages

| # | File | Planned changes |
| --- | --- | --- |
| 11 | `resources/views/pages/admin/dashboard.blade.php` | `<x-ui.page-header>`, `<x-ui.stat-card>` x4, `<x-ui.card>` for the top-ticket-types / promoter / recent-orders blocks, `<x-ui.table>` family for the 3 tables, `<x-ui.status-pill>` for status badges, `<x-ui.empty-state>` for the no-data branches |
| 12 | `resources/views/pages/admin/orders/index.blade.php` | `<x-ui.page-header>`, `<x-ui.filter-form>`, `<x-ui.status-pill>` (replaces inline status pills and the `failed_clickable` behavior), `<x-ui.badge>` for source pill, `<x-ui.link>` for action links, `<x-ui.empty-state>` for empty rows; **preserve** the existing `<script>orderStatusBoard</script>` and toast — only surrounding markup changes |
| 13 | `resources/views/pages/admin/orders/create.blade.php` | `<x-ui.page-header>`, `<x-ui.field>` + `<x-ui.input>`/`<x-ui.select>` for every field, `<x-ui.button>` for submit, `<x-ui.alert>` for errors |
| 14 | `resources/views/pages/admin/orders/edit.blade.php` | Same as create |
| 15 | `resources/views/pages/admin/promoters/index.blade.php` | **PILOT.** Full migration: `<x-ui.page-header>`, `<x-ui.table>` family, `<x-ui.status-pill>` (not used here — actions), `<x-ui.link>` for edit/delete, `<x-ui.button>` for add, `<x-ui.empty-state>`; also make responsive (hide less-important columns under `md:`/`sm:`, stack filter row, smaller padding on mobile) |
| 16 | `resources/views/pages/admin/promoters/create.blade.php` | `<x-ui.page-header>`, `<x-ui.field>` + `<x-ui.input>`, `<x-ui.button>`, `<x-ui.alert>` for errors |
| 17 | `resources/views/pages/admin/promoters/edit.blade.php` | Same as create |
| 18 | `resources/views/pages/admin/promoter_managers/index.blade.php` | `<x-ui.page-header>`, `<x-ui.table>` family, `<x-ui.link>` for action links, `<x-ui.button>` for add |
| 19 | `resources/views/pages/admin/promoter_managers/create.blade.php` | `<x-ui.field>`, `<x-ui.input>`, `<x-ui.button>`, `<x-ui.alert>` |
| 20 | `resources/views/pages/admin/promoter_managers/edit.blade.php` | Same as create |
| 21 | `resources/views/pages/admin/ticket_type/index.blade.php` | `<x-ui.page-header>`, `<x-ui.table>` family, `<x-ui.empty-state>`, `<x-ui.link>`, `<x-ui.button>` |
| 22 | `resources/views/pages/admin/ticket_type/create.blade.php` | `<x-ui.field>`, `<x-ui.input>` (text/number/file), `<x-ui.button>`, `<x-ui.alert>` |
| 23 | `resources/views/pages/admin/ticket_type/edit.blade.php` | Same as create |
| 24 | `resources/views/pages/admin/email_settings/index.blade.php` | `<x-ui.page-header>`, `<x-ui.card>` for sections, `<x-ui.alert>` for flash messages, `<x-ui.button>` for actions, `<x-ui.table>` family for the templates list |
| 25 | `resources/views/pages/admin/email_settings/create.blade.php` | `<x-ui.field>`, `<x-ui.input>`, `<x-ui.textarea>`, `<x-ui.radio>` (source type), `<x-ui.checkbox>` (make default), `<x-ui.button>`, `<x-ui.alert>` |
| 26 | `resources/views/pages/admin/email_settings/edit.blade.php` | `<x-ui.field>`, `<x-ui.input>`, `<x-ui.checkbox>`, `<x-ui.button>`, `<x-ui.alert>`; card layout for metadata + split editor / preview |
| 27 | `resources/views/pages/admin/email_settings/_preview_frame.blade.php` | No change (rendered inside an iframe) |

### 5.3 Promoter-manager pages

| # | File | Planned changes |
| --- | --- | --- |
| 28 | `resources/views/pages/promoter_managers/dashboard.blade.php` | `<x-ui.page-header>`, `<x-ui.kpi-strip>` for the financials row, `<x-ui.stat-card>` for team overview, `<x-ui.card>` for sub-promoters section, `<x-ui.table>` family for desktop, mobile card list, `<x-ui.link>` for edit actions |
| 29 | `resources/views/pages/promoter_managers/sub_promoters/index.blade.php` | `<x-ui.page-header>`, `<x-ui.table>` family, `<x-ui.badge>` for per-type override pills, `<x-ui.empty-state>`, `<x-ui.link>`, `<x-ui.button>` |
| 30 | `resources/views/pages/promoter_managers/sub_promoters/create.blade.php` | `<x-ui.field>`, `<x-ui.input>`, `<x-ui.button>`, `<x-ui.alert>`; commission split section becomes a `<x-ui.card>` |
| 31 | `resources/views/pages/promoter_managers/sub_promoters/edit.blade.php` | Same as create |

### 5.4 Promoter pages

| # | File | Planned changes |
| --- | --- | --- |
| 32 | `resources/views/pages/promoters/dashboard.blade.php` | Migrate gray → zinc palette, `<x-ui.page-header>`, `<x-ui.kpi-strip>` (financials), `<x-ui.stat-card>` (general performance), `<x-ui.card>` for top ticket sales, `<x-ui.table>` family for the table inside |
| 33 | `resources/views/pages/promoters/orders/index.blade.php` | `<x-ui.page-header>`, `<x-ui.table>` family, `<x-ui.status-pill>`, `<x-ui.empty-state>`, `<x-ui.alert>` for flash messages, `<x-ui.link>` for view action |
| 34 | `resources/views/pages/promoters/orders/create.blade.php` | `<x-ui.page-header>`, `<x-ui.field>`, `<x-ui.input>`, `<x-ui.select>`, `<x-ui.button>`, `<x-ui.alert>`; the Alpine `ticketOrder` script is **kept as-is** — only markup changes |
| 35 | `resources/views/pages/promoters/orders/edit.blade.php` | Same as create |
| 36 | `resources/views/pages/promoters/orders/show.blade.php` | `<x-ui.page-header>`, `<x-ui.kpi-strip>` for the order summary, `<x-ui.card>` for items table + tickets grid, `<x-ui.status-pill>`, `<x-ui.alert>` |

### 5.5 Sub-promoter pages

| # | File | Planned changes |
| --- | --- | --- |
| 37 | `resources/views/pages/subpromoters/dashboard.blade.php` | `<x-ui.page-header>`, `<x-ui.stat-card>` x3, `<x-ui.card>` for commission split + recent orders, `<x-ui.table>` family, `<x-ui.status-pill>`, `<x-ui.button>`, `<x-ui.link>` |
| 38 | `resources/views/pages/subpromoters/orders.blade.php` | `<x-ui.page-header>`, `<x-ui.card>`, `<x-ui.table>` family, `<x-ui.status-pill>`, `<x-ui.empty-state>`, `<x-ui.alert>` |

### 5.6 Supreme-admin pages

| # | File | Planned changes |
| --- | --- | --- |
| 39 | `resources/views/pages/supremeadmin/overview.blade.php` | `<x-ui.page-header>`, `<x-ui.stat-card>` for the 7 KPI tiles, `<x-ui.filter-form>` for both filter bar and search form, `<x-ui.field>`, `<x-ui.select>`, `<x-ui.input>`, `<x-ui.button>`, `<x-ui.card>`, `<x-ui.table>` family for both main and sub tables; **keep** the Alpine `supremeOverview` component intact — only markup changes |

### 5.7 Livewire components

| # | File | Planned changes |
| --- | --- | --- |
| 40 | `resources/views/livewire/admin/order-details.blade.php` | `<x-ui.page-header>`, `<x-ui.kpi-strip>` for the 4-cell summary, `<x-ui.stat-card>` would also fit, `<x-ui.status-pill>` for status, `<x-ui.field>` for the paid-amount form, `<x-ui.button>` for bulk actions, `<x-ui.card>` for tickets grid wrapper, `<x-ui.alert>` for flash, `<x-ui.empty-state>` for missing QR; **keep** all Livewire `wire:click` / `wire:model` wiring unchanged |
| 41 | `resources/views/livewire/settings/profile.blade.php` | Replace `<flux:input>` / `<flux:button>` / `<flux:link>` with `<x-ui.input>` / `<x-ui.button>` / `<x-ui.link>` / `<x-ui.field>` (per Decision 2) |
| 42 | `resources/views/livewire/settings/password.blade.php` | Same as profile |
| 43 | `resources/views/livewire/settings/delete-user-form.blade.php` | Replace `<flux:*>` with `<x-ui.*>` |
| 44 | `resources/views/livewire/settings/appearance.blade.php` | Same |
| 45 | `resources/views/livewire/auth/login.blade.php` | Replace `<flux:input>`/`<flux:button>`/`<flux:link>`/`<flux:checkbox>` with `<x-ui.*>` |
| 46 | `resources/views/livewire/auth/register.blade.php` | Same |
| 47 | `resources/views/livewire/auth/forgot-password.blade.php` | Same |
| 48 | `resources/views/livewire/auth/reset-password.blade.php` | Same |
| 49 | `resources/views/livewire/auth/confirm-password.blade.php` | Same |
| 50 | `resources/views/livewire/auth/verify-email.blade.php` | Same |

### 5.8 Out of scope (per Decision 3)

| File | Status |
| --- | --- |
| `resources/views/pages/promoters/help.blade.php` | **Do not touch** |

### 5.9 Controllers (cleanup pass)

> These are PHP files, not blade, but they need edits to drop the duplicate
> status arrays.

| # | File | Planned changes |
| --- | --- | --- |
| 51 | `app/Http/Controllers/AdminOrderController.php` | Drop the local `$jobStatusColors` array; remove `'jobStatusColors'` from `compact(...)` so the view reads from `App\Support\Status` instead |
| 52 | `app/Http/Controllers/OrderController.php` | Same |
| 53 | `app/Http/Controllers/OrderController1.php` | Same (consider also deleting if unused — see §6) |
| 54 | `app/Http/Controllers/SubPromoterController.php` | Same |

---

## 6. Dead-code audit (handled during Phase 6 cleanup)

> These items will not be removed during the page sweeps. They will be
> removed only after every page is migrated and we are confident nothing
> else references them.

| Item | Action |
| --- | --- |
| `<x-flash-messages>` global `<script>` block | Re-implement on top of `<x-ui.alert position="toast">` and keep |
| `resources/views/partials/head.blade.php` inline `<style>` for input/select | Delete once every page uses `<x-ui.input>` / `<x-ui.select>` |
| `@fluxAppearance` directive in `head.blade.php` | Remove (no Flux components used) |
| `app/Http/Controllers/OrderController1.php` | Investigate and likely delete (duplicate of `OrderController.php`) |
| 4× duplicate `$jobStatusColors` arrays | Collapse into `App\Support\Status` |
| `<flux:*>` component imports | Remove (no Flux components left after Phase 4) |

---

## 7. Risks & mitigations

| Risk | Mitigation |
| --- | --- |
| Layout regressions on dashboard pages | Manual dark/light screenshot before & after each migration |
| Behaviour regressions | `<x-ui.status-pill clickable>` preserves the failed-status click-to-expand pattern; `<x-ui.alert position="toast">` keeps the auto-dismiss behaviour; `orderStatusBoard` / `supremeOverview` / `ticketOrder` / `emailEditor` Alpine scripts are **never touched** — only the surrounding HTML changes |
| Translation regressions | No translation key changes; only markup wrapping |
| Removing `class="dark"` changes appearance globally | Done in a single small commit so it's easy to revert if light-mode looks broken in some view |
| `<flux:` removed from auth pages but Flux CSS still loaded | Remove the `@import '../../vendor/livewire/flux/dist/flux.css';` line in `resources/css/app.css` only after auth pages are migrated |

---

## 8. Responsive improvements (Pilot page focus)

Per Decision 6, the pilot page will get extra responsive polish. We'll apply
the same patterns across the rest:

| Pattern | Implementation |
| --- | --- |
| Hide less-important columns on mobile | `<x-ui.table-cell width="0" class="hidden sm:table-cell">` or `hidden md:table-cell` |
| Stack filter form on mobile | `<x-ui.filter-form>` already does this via `flex-col sm:flex-row` |
| Reduce card padding on mobile | The `<x-ui.stat-card>` uses `p-5 sm:p-6` |
| Sidebar collapses to drawer | Already done in `layouts/app/sidebar.blade.php` via `flux:sidebar` |
| Page-header title + actions stack on mobile | `<x-ui.page-header>` is already `flex-col gap-4 sm:flex-row` |
| Tables scroll horizontally on small screens | `<x-ui.table>` wraps in `overflow-x-auto` |

---

## 9. Out of scope (intentionally)

* `pages/promoters/help.blade.php` (per Decision 3)
* `resources/views/emails/customer/tickets.blade.php` (different audience)
* Routes, controllers, business logic, database, tests
* New features

---

## 10. Quick reference — how the components compose

### Status pill

```blade
<x-ui.status-pill :status="$order->job_status" />
{{-- reads App\Support\Status; no controller plumbing --}}

<x-ui.status-pill :status="$order->job_status" clickable
                  label="{{ __('orders.statuses.failed') }}" />
```

### Table

```blade
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
                    <x-ui.empty-state icon="users" title="No promoters" />
                </x-ui.table-cell>
            </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>
```

### Form

```blade
<form method="POST" action="{{ route('admin.promoters.store') }}" class="space-y-5">
    @csrf
    <x-ui.field label="Name" for="name" :error="$errors->first('name')" required>
        <x-ui.input id="name" name="name" :value="old('name')" required />
    </x-ui.field>

    <x-ui.field label="Email" for="email" :error="$errors->first('email')" required>
        <x-ui.input id="email" name="email" type="email" :value="old('email')" required />
    </x-ui.field>

    <div class="flex items-center justify-end gap-2 pt-2">
        <x-ui.button variant="secondary" :href="route('admin.promoters.index')">Cancel</x-ui.button>
        <x-ui.button variant="primary" type="submit" icon="plus">Create promoter</x-ui.button>
    </div>
</form>
```

### Page with flash toast

```blade
<x-ui.page-header :title="__('…')" :subtitle="__('…')">
    <x-slot:actions>
        <x-ui.button variant="primary" :href="route('…')" icon="plus">{{ __('…') }}</x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

<x-ui.alert variant="success" position="toast" :dismissable="true" class="mt-4">
    {{ session('success') }}
</x-ui.alert>
```

---

**When you're ready, the first action will be**: migrate
`resources/views/pages/admin/promoters/index.blade.php` end-to-end against
this plan, validate visually, then proceed phase by phase.
