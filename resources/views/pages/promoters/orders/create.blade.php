<x-layouts.app :title="__('orders.create_page_title')">
    <div class="space-y-6 max-w-3xl mx-auto w-full">

        <x-ui.page-header :title="__('orders.create_main_heading')">
            <x-slot:actions>
                @if(Auth::user()->isAdmin())
                    <x-ui.link variant="primary" :href="route('admin.orders.index')">{!! __('orders.create_back_to_orders_link') !!}</x-ui.link>
                @else
                    <x-ui.link variant="primary" :href="route('promoter.orders.index')">{!! __('orders.create_back_to_orders_link') !!}</x-ui.link>
                @endif
            </x-slot:actions>
        </x-ui.page-header>

        @if ($errors->any())
            <x-ui.alert variant="danger">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('promoter.orders.store') }}" id="createOrderForm" class="space-y-6">
            @csrf

            <x-ui.card>
                <div class="p-5 sm:p-6">
                    <x-ui.field :label="__('orders.create_customer_email_label')" for="email" :error="$errors->first('email')" required>
                        <x-ui.input id="email" name="email" type="email" :value="old('email')"
                                    placeholder="customer@example.com" required />
                    </x-ui.field>
                </div>
            </x-ui.card>

            {{-- Add Ticket Items Section with Alpine.js --}}
            <x-ui.card x-data="ticketOrder()">
                <div class="p-5 sm:p-6 space-y-4">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-white">{{ __('orders.create_order_items_heading') }}</h2>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-6">
                            <x-ui.field :label="__('orders.create_ticket_type_label')" for="ticket_type_selector">
                                <x-ui.select id="ticket_type_selector" x-model="selectedTicketId">
                                    <option value="">{{ __('orders.create_select_ticket_type_option') }}</option>
                                    @foreach ($ticketTypes as $type)
                                        <option value="{{ $type->id }}"
                                                data-name="{{ $type->name }}"
                                                data-price="{{ $type->price }}">
                                            {{ $type->name }} ({{ number_format($type->price, 2) }} RSD)
                                        </option>
                                    @endforeach
                                </x-ui.select>
                            </x-ui.field>
                        </div>

                        <div class="md:col-span-3">
                            <x-ui.field :label="__('orders.create_quantity_label')" for="quantity_selector">
                                <x-ui.input id="quantity_selector" type="number" min="1" x-model.number="quantity" />
                            </x-ui.field>
                        </div>

                        <div class="md:col-span-3">
                            <button
                                type="button"
                                @click="addItem"
                                class="w-full inline-flex justify-center rounded-lg border border-transparent bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900"
                            >
                                {{ __('orders.create_add_item_button') }}
                            </button>
                        </div>
                    </div>

                    {{-- Table for Added Items --}}
                    <div class="mt-4 flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <table class="min-w-full divide-y divide-zinc-300 dark:divide-zinc-700">
                                    <thead>
                                        <tr>
                                            <th class="py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-white">{{ __('orders.create_items_table_header_ticket') }}</th>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-white">{{ __('orders.create_items_table_header_quantity') }}</th>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-white">{{ __('orders.create_items_table_header_unit_price') }}</th>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-white">{{ __('orders.create_items_table_header_subtotal') }}</th>
                                            <th class="py-3.5 text-right text-sm font-semibold text-zinc-900 dark:text-white">{{ __('orders.create_items_table_header_remove') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                        <template x-if="items.length === 0">
                                            <tr>
                                                <td colspan="5" class="text-center text-sm text-zinc-500 dark:text-zinc-400 py-4">{{ __('orders.create_no_items_message') }}</td>
                                            </tr>
                                        </template>
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr>
                                                <td class="py-4 text-sm font-medium text-zinc-900 dark:text-white" x-text="item.name"></td>
                                                <td class="px-3 py-4 text-sm text-zinc-500 dark:text-zinc-300" x-text="item.quantity"></td>
                                                <td class="px-3 py-4 text-sm text-zinc-500 dark:text-zinc-300" x-text="`${item.unitPrice.toFixed(2)} RSD`"></td>
                                                <td class="px-3 py-4 text-sm text-zinc-500 dark:text-zinc-300" x-text="`${item.subtotal.toFixed(2)} RSD`"></td>
                                                <td class="py-4 text-right">
                                                    <button type="button" class="text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300"
                                                            @click="removeItem(index)">{{ __('orders.create_items_table_header_remove') }}</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden Inputs --}}
                    <div id="hiddenOrderItems">
                        <template x-for="(item, index) in items" :key="'hidden-' + index">
                            <div>
                                <input type="hidden" :name="`items[${index}][quantity]`" :value="item.quantity" />
                                <input type="hidden" :name="`items[${index}][ticket_type_id]`" :value="item.ticketTypeId" />
                            </div>
                        </template>
                    </div>

                    {{-- Order Total --}}
                    <div class="mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <dl class="space-y-1 text-sm font-medium text-zinc-900 dark:text-white">
                            <div class="flex justify-between">
                                <dt>{{ __('orders.create_total_label') }}</dt>
                                <dd x-text="`${total.toFixed(2)} RSD`" class="text-lg font-semibold"></dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </x-ui.card>

            {{-- Submit Button --}}
            <div class="flex items-center justify-end space-x-3 pt-2">
                @if(Auth::user()->isAdmin())
                    <x-ui.button variant="secondary" :href="route('admin.orders.index')">
                        {{ __('orders.create_cancel_button') }}
                    </x-ui.button>
                @else
                    <x-ui.button variant="secondary" :href="route('promoter.orders.index')">
                        {{ __('orders.create_cancel_button') }}
                    </x-ui.button>
                @endif
                <x-ui.button id="submitOrderButton" type="submit" variant="primary">
                    {{ __('orders.create_submit_button') }}
                </x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
</content>
</invoke>