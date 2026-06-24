<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="grid min-h-screen bg-zinc-50 dark:bg-zinc-900"
      style="grid-template-rows: auto 1fr auto; grid-template-columns: min-content minmax(0, 1fr) min-content; grid-template-areas: 'header header header' 'sidebar main aside' 'sidebar footer aside';">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            @php
                $user = Auth::user();
                $homeRoute = match (true) {
                    $user?->isAdmin()          => 'dashboard',
                    $user?->isPromoterManager() => 'promoter_manager.dashboard',
                    $user?->isSubPromoter()    => 'sub_promoter.dashboard',
                    default                    => 'promoter.dashboard',
                };
            @endphp

            <a href="{{ route($homeRoute) }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('navigation.sidebar.group_platform')" class="grid">
                    @if($user?->isAdmin())
                        <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('navigation.sidebar.admin_dashboard') }}</flux:navlist.item>
                        <flux:navlist.item icon="user" :href="route('admin.promoters.index')" :current="request()->routeIs('admin.promoters.*')" wire:navigate>{{ __('navigation.sidebar.promoters') }}</flux:navlist.item>
                        <flux:navlist.item icon="users" :href="route('admin.promoter_managers.index')" :current="request()->routeIs('admin.promoter_managers.*')" wire:navigate>{{ __('navigation.sidebar.promoter_managers') }}</flux:navlist.item>
                        @if(in_array($user->role, ['supreme', 'superadmin'], true))
                            <flux:navlist.item icon="chart-bar" :href="route('supremeadmin.overview')" :current="request()->routeIs('supremeadmin.overview')" wire:navigate>{{ __('navigation.sidebar.supremeadmin_overview') }}</flux:navlist.item>
                            <flux:navlist.item icon="users" :href="route('superadmin.users.index')" :current="request()->routeIs('superadmin.users.*')" wire:navigate>{{ __('navigation.sidebar.supremeadmin_users') }}</flux:navlist.item>
                        @endif
                        <flux:navlist.item icon="ticket" :href="route('ticket_type.index')" :current="request()->routeIs('ticket_type.*')" wire:navigate>{{ __('navigation.sidebar.ticket_types') }}</flux:navlist.item>
                        <flux:navlist.item icon="ticket" :href="route('admin.orders.index')" :current="request()->routeIs('admin.orders.*')" wire:navigate>{{ __('navigation.sidebar.admin_sold_tickets') }}</flux:navlist.item>
                        <flux:navlist.item icon="envelope" :href="route('admin.email-settings.index')" :current="request()->routeIs('admin.email-settings.*')" wire:navigate>{{ __('navigation.sidebar.email_settings') }}</flux:navlist.item>
                    @elseif($user?->isPromoterManager())
                        <flux:navlist.item icon="home" :href="route('promoter_manager.dashboard')" :current="request()->routeIs('promoter_manager.dashboard')" wire:navigate>{{ __('navigation.sidebar.promoter_manager_dashboard') }}</flux:navlist.item>
                        <flux:navlist.item icon="users" :href="route('promoter_manager.sub_promoters.index')" :current="request()->routeIs('promoter_manager.sub_promoters.*')" wire:navigate>{{ __('navigation.sidebar.sub_promoters') }}</flux:navlist.item>
                        <flux:navlist.item icon="ticket" :href="route('promoter.orders.create')" :current="request()->routeIs('promoter.orders.create')" wire:navigate>{{ __('navigation.sidebar.sales') }}</flux:navlist.item>
                        <flux:navlist.item icon="ticket" :href="route('promoter.orders.index')" :current="request()->routeIs('promoter.orders.index')" wire:navigate>{{ __('navigation.sidebar.promoter_sold_tickets') }}</flux:navlist.item>
                        <flux:navlist.item icon="user" :href="route('promoter.help')" :current="request()->routeIs('promoter.help')" wire:navigate>{{ __('navigation.sidebar.support') }}</flux:navlist.item>
                    @elseif($user?->isSubPromoter())
                        <flux:navlist.item icon="home" :href="route('sub_promoter.dashboard')" :current="request()->routeIs('sub_promoter.dashboard')" wire:navigate>{{ __('navigation.sidebar.sub_promoter_dashboard') }}</flux:navlist.item>
                        <flux:navlist.item icon="ticket" :href="route('sub_promoter.orders.create')" :current="request()->routeIs('sub_promoter.orders.create')" wire:navigate>{{ __('navigation.sidebar.sales') }}</flux:navlist.item>
                        <flux:navlist.item icon="ticket" :href="route('sub_promoter.orders.index')" :current="request()->routeIs('sub_promoter.orders.index')" wire:navigate>{{ __('navigation.sidebar.sub_promoter_sold_tickets') }}</flux:navlist.item>
                        <flux:navlist.item icon="user" :href="route('promoter.help')" :current="request()->routeIs('promoter.help')" wire:navigate>{{ __('navigation.sidebar.support') }}</flux:navlist.item>
                    @else
                        <flux:navlist.item icon="home" :href="route('promoter.dashboard')" :current="request()->routeIs('promoter.dashboard')" wire:navigate>{{ __('navigation.sidebar.promoter_dashboard') }}</flux:navlist.item>
                        <flux:navlist.item icon="ticket" :href="route('promoter.orders.create')" :current="request()->routeIs('promoter.orders.create')" wire:navigate>{{ __('navigation.sidebar.sales') }}</flux:navlist.item>
                        <flux:navlist.item icon="ticket" :href="route('promoter.orders.index')" :current="request()->routeIs('promoter.orders.index')" wire:navigate>{{ __('navigation.sidebar.promoter_sold_tickets') }}</flux:navlist.item>
                        <flux:navlist.item icon="user" :href="route('promoter.help')" :current="request()->routeIs('promoter.help')" wire:navigate>{{ __('navigation.sidebar.support') }}</flux:navlist.item>
                    @endif
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <flux:dropdown position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                />
                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('navigation.usermenu.settings') }}</flux:menu.item>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('navigation.usermenu.logout') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:spacer />
            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />
                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('navigation.usermenu.settings') }}</flux:menu.item>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('navigation.usermenu.logout') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
