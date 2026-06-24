<x-layouts.app :title="__('Create New Ticket Order')">
    <div class="space-y-6 max-w-3xl">
        <x-ui.page-header :title="__('Create New Ticket Order')">
            <x-slot:actions>
                <x-ui.link variant="secondary" :href="route('admin.orders.index')" icon="arrow-left">
                    {{ __('Back to Orders') }}
                </x-ui.link>
            </x-slot:actions>
        </x-ui.page-header>

        @if($errors->any())
            <x-ui.alert variant="danger">
                <p class="font-semibold">{{ __('Please fix the following errors:') }}</p>
                <ul class="mt-1 list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('promoter.orders.store') }}" id="createOrderForm" class="space-y-6 p-6">
                @csrf

                {{-- Customer Email --}}
                <x-ui.field label="Customer Email" for="email" :error="$errors->first('email')" required>
                    <x-ui.input id="email" name="email" type="email" :value="old('email')" placeholder="customer@example.com" required />
                </x-ui.field>

                {{-- Add Ticket Items Section --}}
                <div class="space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Order Items</h2>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-6">
                            <x-ui.field label="Ticket Type" for="ticket_type_selector">
                                <x-ui.select id="ticket_type_selector">
                                    <option value="">Select a ticket type...</option>
                                    @foreach ($ticketTypes as $type)
                                        <option value="{{ $type->id }}" data-price="{{ $type->price }}" data-name="{{ $type->name }}">
                                            {{ $type->name }} (${{ number_format($type->price, 2) }})
                                        </option>
                                    @endforeach
                                </x-ui.select>
                            </x-ui.field>
                        </div>
                        <div class="md:col-span-3">
                            <x-ui.field label="Quantity" for="quantity_selector">
                                <x-ui.input id="quantity_selector" type="number" :value="1" min="1" />
                            </x-ui.field>
                        </div>
                        <div class="md:col-span-3">
                            <x-ui.button id="addItemButton" type="button" variant="success" fullWidth>
                                {{ __('Add Item') }}
                            </x-ui.button>
                        </div>
                    </div>

                    {{-- Table for Added Items --}}
                    <div class="mt-4 flow-root">
                        <x-ui.table>
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-100 sm:pl-0">Ticket</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-100">Quantity</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-100">Unit Price</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-zinc-900 dark:text-zinc-100">Subtotal</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0"><span class="sr-only">Remove</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700" id="orderItemsTbody">
                                {{-- Items will be added here by JavaScript --}}
                                <tr id="noItemsRow">
                                    <td colspan="5" class="whitespace-nowrap py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">No items added yet.</td>
                                </tr>
                            </tbody>
                        </x-ui.table>
                    </div>
                    @error('items') {{-- For validation error on the items array --}}
                        <p class="mt-1 text-xs text-rose-600 dark:text-rose-400" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Hidden inputs for items will be populated by JS --}}
                <div id="hiddenOrderItems"></div>

                {{-- Order Total --}}
                <div class="mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <dl class="space-y-1 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                        <div class="flex justify-between">
                            <dt>Total</dt>
                            <dd id="orderTotalDisplay" class="text-lg font-semibold">$0.00</dd>
                        </div>
                    </dl>
                </div>

                {{-- Submit Button --}}
                <div class="flex items-center justify-end gap-3 pt-6">
                    <x-ui.button variant="secondary" :href="route('admin.orders.index')">
                        {{ __('Cancel') }}
                    </x-ui.button>
                    <x-ui.button id="submitOrderButton" type="submit" variant="primary">
                        {{ __('Place Order & Send Tickets') }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ticketTypeSelector = document.getElementById('ticket_type_selector');
            const quantitySelector = document.getElementById('quantity_selector');
            const addItemButton = document.getElementById('addItemButton');
            const orderItemsTbody = document.getElementById('orderItemsTbody');
            const orderTotalDisplay = document.getElementById('orderTotalDisplay');
            const hiddenOrderItemsContainer = document.getElementById('hiddenOrderItems');
            const noItemsRow = document.getElementById('noItemsRow');
            const form = document.getElementById('createOrderForm');

            let orderItems = []; // Array to store { ticketTypeId, name, quantity, unitPrice, subtotal }

            addItemButton.addEventListener('click', function () {
                const selectedOption = ticketTypeSelector.options[ticketTypeSelector.selectedIndex];
                const ticketTypeId = selectedOption.value;
                const ticketName = selectedOption.dataset.name;
                const unitPrice = parseFloat(selectedOption.dataset.price);
                const quantity = parseInt(quantitySelector.value);

                if (!ticketTypeId || quantity < 1) {
                    alert('Please select a ticket type and enter a valid quantity.');
                    return;
                }

                // Check if item already exists, if so, update quantity (optional)
                const existingItemIndex = orderItems.findIndex(item => item.ticketTypeId === ticketTypeId);
                if (existingItemIndex > -1) {
                     // For simplicity, we'll just add as a new line.
                     // Or you could update: orderItems[existingItemIndex].quantity += quantity;
                     // orderItems[existingItemIndex].subtotal = orderItems[existingItemIndex].quantity * unitPrice;
                     // For now, let's allow adding the same ticket type multiple times as separate line items.
                }

                const subtotal = quantity * unitPrice;
                orderItems.push({ ticketTypeId, name: ticketName, quantity, unitPrice, subtotal });

                renderOrderItems();
                updateOrderTotal();
                updateHiddenInputs();

                // Reset selectors
                ticketTypeSelector.value = "";
                quantitySelector.value = "1";
            });

            function renderOrderItems() {
                orderItemsTbody.innerHTML = ''; // Clear existing rows
                if (orderItems.length === 0) {
                    orderItemsTbody.appendChild(noItemsRow);
                    return;
                }

                orderItems.forEach((item, index) => {
                    const row = orderItemsTbody.insertRow();
                    row.innerHTML = `
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-0">${item.name}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">${item.quantity}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">$${item.unitPrice.toFixed(2)}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">$${item.subtotal.toFixed(2)}</td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                            <button type="button" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 removeItemButton" data-index="${index}">Remove</button>
                        </td>
                    `;
                });

                document.querySelectorAll('.removeItemButton').forEach(button => {
                    button.addEventListener('click', function () {
                        const itemIndex = parseInt(this.dataset.index);
                        orderItems.splice(itemIndex, 1);
                        renderOrderItems();
                        updateOrderTotal();
                        updateHiddenInputs();
                    });
                });
            }

            function updateOrderTotal() {
                const total = orderItems.reduce((sum, item) => sum + item.subtotal, 0);
                orderTotalDisplay.textContent = `$${total.toFixed(2)}`;
            }

            function updateHiddenInputs() {
                hiddenOrderItemsContainer.innerHTML = ''; // Clear previous hidden inputs
                orderItems.forEach((item, index) => {
                    const ticketIdInput = document.createElement('input');
                    ticketIdInput.type = 'hidden';
                    ticketIdInput.name = `items[${index}][ticket_type_id]`;
                    ticketIdInput.value = item.ticketTypeId;
                    hiddenOrderItemsContainer.appendChild(ticketIdInput);

                    const quantityInput = document.createElement('input');
                    quantityInput.type = 'hidden';
                    quantityInput.name = `items[${index}][quantity]`;
                    quantityInput.value = item.quantity;
                    hiddenOrderItemsContainer.appendChild(quantityInput);
                });
            }

            // Initial render in case of validation errors repopulating the form (more advanced)
            // For now, it starts empty.
             if (orderItems.length > 0) {
                renderOrderItems();
                updateOrderTotal();
                updateHiddenInputs();
            } else {
                 orderItemsTbody.appendChild(noItemsRow);
            }
        });
    </script>
</x-layouts.app>