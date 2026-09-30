@php
    $rawItems = old('items');
    if ($rawItems && is_array($rawItems) && count($rawItems) > 0) {
        $initialEditItems = collect($rawItems)->map(function($i) {
            $arr = (array)$i;
            return [
                'description'          => (string)($arr['description'] ?? ''),
                'domain_name'          => (string)($arr['domain_name'] ?? ''),
                'service_period_start' => (string)($arr['service_period_start'] ?? ''),
                'service_period_end'   => (string)($arr['service_period_end'] ?? ''),
                'billing_cycle'        => (string)($arr['billing_cycle'] ?? '1 Year'),
                'hsn_sac'              => (string)($arr['hsn_sac'] ?? ''),
                'quantity'             => isset($arr['quantity']) && is_numeric($arr['quantity']) ? (float)$arr['quantity'] : 1,
                'unit'                 => (string)($arr['unit'] ?? 'Pcs'),
                'rate'                 => isset($arr['rate']) && is_numeric($arr['rate']) ? (float)$arr['rate'] : 0,
                'gst_percent'          => isset($arr['gst_percent']) && is_numeric($arr['gst_percent']) ? (float)$arr['gst_percent'] : 18,
            ];
        })->values()->all();
    } else {
        $initialEditItems = $existingItems->toArray();
    }

    $initialServerErrors = [];
    foreach ($errors->messages() as $field => $msgs) {
        $cleanField = str_replace(['items.', '.'], ['item_', '_'], $field);
        $initialServerErrors[$cleanField] = $msgs[0];
    }
@endphp

<x-app-layout header="Edit Invoice #{{ $invoice->invoice_number }}">
    <div class="max-w-6xl mx-auto space-y-6" x-data="invoiceEditor()">
        
        <!-- Instant Real-Time Error Alert Banner -->
        <div x-show="formErrorMessage" x-cloak class="p-4 rounded-2xl bg-rose-50 border-2 border-rose-300 text-rose-900 shadow-sm flex items-start justify-between gap-3 transition-all">
            <div class="flex items-start gap-2.5">
                <span class="text-xl leading-none">⚠️</span>
                <div>
                    <h4 class="font-bold text-sm" x-text="formErrorMessage"></h4>
                    <p class="text-xs text-rose-700 mt-0.5">Niche red border wale fields ko check karein aur sahi karein. Aapka koi data reset nahi hua hai.</p>
                </div>
            </div>
            <button type="button" @click="formErrorMessage = ''" class="text-rose-400 hover:text-rose-700 font-bold text-xl leading-none">&times;</button>
        </div>

        <form action="{{ route('invoices.update', $invoice->id) }}" method="POST" novalidate @submit="validateForm($event)" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Top Header Card -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Modify Invoice #{{ $invoice->invoice_number }}</h2>
                        <p class="text-xs text-slate-500">Issuer: <strong class="text-slate-800">{{ $company->name }}</strong></p>
                    </div>
                    
                    <!-- Tax Mode Switcher on Invoice -->
                    <div class="flex items-center gap-2 bg-slate-100 p-1.5 rounded-xl text-xs font-semibold">
                        <span class="text-slate-500 px-2">Tax Mode:</span>
                        <label class="px-3 py-1 rounded-lg cursor-pointer transition-colors" :class="taxMode === 'simple' ? 'bg-white shadow-sm text-violet-700 font-bold' : 'text-slate-600'">
                            <input type="radio" name="tax_mode" value="simple" x-model="taxMode" class="hidden"> Simple GST (18%)
                        </label>
                        <label class="px-3 py-1 rounded-lg cursor-pointer transition-colors" :class="taxMode === 'detailed' ? 'bg-white shadow-sm text-violet-700 font-bold' : 'text-slate-600'">
                            <input type="radio" name="tax_mode" value="detailed" x-model="taxMode" class="hidden"> Split CGST/SGST
                        </label>
                    </div>
                </div>

                <!-- Invoice Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    
                    <!-- EDITABLE INVOICE NUMBER -->
                    <div class="md:col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Invoice Number *
                        </label>
                        <input type="text" name="invoice_number" id="field_invoice_number"
                               value="{{ old('invoice_number', $invoice->invoice_number) }}"
                               @input="clearError('invoice_number')"
                               :class="fieldErrors['invoice_number'] ? 'border-rose-500 ring-2 ring-rose-200 bg-rose-50/40 text-rose-900' : 'border-violet-200 text-violet-900 bg-violet-50/20'"
                               class="w-full px-3.5 py-2 rounded-xl border-2 font-mono font-bold focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none transition-colors">
                        <p x-show="fieldErrors['invoice_number']" x-text="fieldErrors['invoice_number']" class="text-[11px] text-rose-600 font-bold mt-1 flex items-center gap-1"></p>
                        <p x-show="!fieldErrors['invoice_number']" class="text-[11px] text-violet-600 font-medium mt-1">
                            ✏️ Freely editable by company
                        </p>
                    </div>

                    <!-- CUSTOMER SELECTOR -->
                    <div class="md:col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Customer / Client *</label>
                        <select name="customer_id" id="field_customer_id" x-model="selectedCustomerId"
                                @change="clearError('customer_id')"
                                :class="fieldErrors['customer_id'] ? 'border-rose-500 ring-2 ring-rose-200 bg-rose-50/40 text-rose-900' : 'border-slate-300'"
                                class="w-full px-3.5 py-2 rounded-xl border text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none transition-colors">
                            <option value="">Select Customer...</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', $invoice->customer_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} {{ $c->company_name ? "({$c->company_name})" : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p x-show="fieldErrors['customer_id']" x-text="fieldErrors['customer_id']" class="text-[11px] text-rose-600 font-bold mt-1 flex items-center gap-1"></p>
                    </div>

                    <!-- DATES -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Invoice Date *</label>
                        <input type="date" name="invoice_date" id="field_invoice_date"
                               value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}"
                               @input="clearError('invoice_date')"
                               :class="fieldErrors['invoice_date'] ? 'border-rose-500 ring-2 ring-rose-200 bg-rose-50/40 text-rose-900' : 'border-slate-300'"
                               class="w-full px-3.5 py-2 rounded-xl border text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none transition-colors">
                        <p x-show="fieldErrors['invoice_date']" x-text="fieldErrors['invoice_date']" class="text-[11px] text-rose-600 font-bold mt-1 flex items-center gap-1"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Due Date</label>
                        <input type="date" name="due_date" value="{{ old('due_date', optional($invoice->due_date)->format('Y-m-d')) }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Supply Region *</label>
                        <select name="sale_type" x-model="saleType" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                            <option value="LOCAL" {{ old('sale_type', $invoice->sale_type) === 'LOCAL' ? 'selected' : '' }}>Intra-State: {{ $company->state ?? 'Local' }} (CGST + SGST)</option>
                            <option value="CENTRAL" {{ old('sale_type', $invoice->sale_type) === 'CENTRAL' ? 'selected' : '' }}>Inter-State (IGST)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Payment Status</label>
                        <select name="status" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                            <option value="unpaid" {{ old('status', $invoice->status) === 'unpaid' ? 'selected' : '' }}>Unpaid / Due</option>
                            <option value="paid" {{ old('status', $invoice->status) === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="partially_paid" {{ old('status', $invoice->status) === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                            <option value="draft" {{ old('status', $invoice->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Payment Method</label>
                        <input type="text" name="payment_method" value="{{ old('payment_method', $invoice->payment_method) }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Line Items Table -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900">Line Items & Products</h3>
                        <p class="text-xs text-slate-500">Single-line streamlined item rows. Press <kbd class="px-1.5 py-0.5 rounded bg-slate-100 border text-[11px]">Enter</kbd> to add row automatically.</p>
                    </div>
                    <button type="button" @click="addItem()" class="px-3.5 py-1.5 rounded-xl bg-violet-50 hover:bg-violet-100 text-violet-700 text-xs font-bold border border-violet-200 transition-colors">
                        + Add Item Row
                    </button>
                </div>

                <!-- Product Autocomplete Datalist -->
                <datalist id="products-catalog">
                    @foreach($products as $prod)
                        <option value="{{ $prod->name }}">{{ $prod->name }} (₹{{ number_format($prod->rate, 2) }} - HSN: {{ $prod->hsn_sac ?: 'N/A' }})</option>
                    @endforeach
                </datalist>

                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3 w-12 text-center">#</th>
                                <th class="py-2.5 px-3 min-w-[240px]">Item Description *</th>
                                <th class="py-2.5 px-3 w-28">HSN/SAC</th>
                                <th class="py-2.5 px-3 w-24 text-center">Qty *</th>
                                <th class="py-2.5 px-3 w-24">Unit</th>
                                <th class="py-2.5 px-3 w-32 text-right">Rate (₹) *</th>
                                <th class="py-2.5 px-3 w-24" x-show="taxMode !== 'simple'">GST %</th>
                                <th class="py-2.5 px-3 w-32 text-right">Total (₹)</th>
                                <th class="py-2.5 px-2 w-10 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2 px-3 text-center text-xs font-mono font-bold text-slate-400 align-top pt-3" x-text="index + 1"></td>
                                    
                                    <td class="py-2 px-3 align-top">
                                        <input type="text"
                                               :name="`items[${index}][description]`"
                                               :id="`field_item_${index}_description`"
                                               x-model="item.description"
                                               @input="clearError('item_' + index + '_description'); checkCatalog(item)"
                                               @keydown.enter.prevent="addItem()"
                                               placeholder="Item description or service..."
                                               list="products-catalog"
                                               :class="fieldErrors['item_' + index + '_description'] ? 'border-rose-500 ring-2 ring-rose-200 bg-rose-50/40 text-rose-900 placeholder-rose-300' : 'border-slate-300'"
                                               class="w-full px-3 py-1.5 text-sm rounded-lg border focus:ring-2 focus:ring-violet-500 focus:outline-none transition-colors">
                                        <p x-show="fieldErrors['item_' + index + '_description']" x-text="fieldErrors['item_' + index + '_description']" class="text-[11px] text-rose-600 font-bold mt-1"></p>
                                        
                                        <!-- Hosting / Domain & Service Period Inputs -->
                                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5 text-[11px] bg-slate-50 p-1.5 rounded-lg border border-slate-200/70">
                                            <span class="text-violet-700 font-bold">🌐 Domain/Cloud:</span>
                                            <input type="text" :name="`items[${index}][domain_name]`" x-model="item.domain_name" placeholder="e.g. greenstudio.jixsite.com"
                                                   class="px-2 py-0.5 text-xs rounded border border-slate-300 w-44 font-mono text-slate-900 bg-white">
                                            <span class="text-slate-400">Period:</span>
                                            <input type="date" :name="`items[${index}][service_period_start]`" x-model="item.service_period_start" class="px-1.5 py-0.5 text-[10px] rounded border border-slate-300 bg-white">
                                            <span class="text-slate-400">to</span>
                                            <input type="date" :name="`items[${index}][service_period_end]`" x-model="item.service_period_end" class="px-1.5 py-0.5 text-[10px] rounded border border-slate-300 bg-white">
                                            <select :name="`items[${index}][billing_cycle]`" x-model="item.billing_cycle" class="px-1.5 py-0.5 text-[10px] rounded border border-slate-300 bg-white font-medium">
                                                <option value="">Cycle</option>
                                                <option value="1 Year">1 Year</option>
                                                <option value="Monthly">Monthly</option>
                                                <option value="3 Years">3 Years</option>
                                                <option value="One-Time">One-Time</option>
                                            </select>
                                        </div>
                                    </td>

                                    <td class="py-2 px-3 align-top">
                                        <input type="text" :name="`items[${index}][hsn_sac]`" x-model="item.hsn_sac" placeholder="HSN"
                                               class="w-full px-2.5 py-1.5 text-xs font-mono rounded-lg border border-slate-300 focus:ring-2 focus:ring-violet-500 focus:outline-none">
                                    </td>

                                    <td class="py-2 px-3 align-top">
                                        <input type="number" step="0.01" min="0.01"
                                               :name="`items[${index}][quantity]`"
                                               :id="`field_item_${index}_quantity`"
                                               x-model.number="item.quantity"
                                               @input="clearError('item_' + index + '_quantity')"
                                               :class="fieldErrors['item_' + index + '_quantity'] ? 'border-rose-500 ring-2 ring-rose-200 bg-rose-50/40 text-rose-900' : 'border-slate-300'"
                                               class="w-full px-2.5 py-1.5 text-sm text-center rounded-lg border focus:ring-2 focus:ring-violet-500 focus:outline-none transition-colors">
                                        <p x-show="fieldErrors['item_' + index + '_quantity']" x-text="fieldErrors['item_' + index + '_quantity']" class="text-[10px] text-rose-600 font-bold mt-1 text-center"></p>
                                    </td>

                                    <td class="py-2 px-3 align-top">
                                        <select :name="`items[${index}][unit]`" x-model="item.unit" class="w-full px-1.5 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-violet-500 focus:outline-none">
                                            <option value="Pcs">Pcs</option>
                                            <option value="Nos">Nos</option>
                                            <option value="Kg">Kg</option>
                                            <option value="Box">Box</option>
                                            <option value="Set">Set</option>
                                            <option value="Service">Service</option>
                                            <option value="Hours">Hours</option>
                                            <option value="Length">Length</option>
                                            <option value="Year">Year</option>
                                            <option value="Job">Job</option>
                                        </select>
                                    </td>

                                    <td class="py-2 px-3 align-top">
                                        <div class="relative">
                                            <span class="absolute left-2.5 top-1.5 text-xs font-bold" :class="fieldErrors['item_' + index + '_rate'] ? 'text-rose-500' : 'text-slate-400'">₹</span>
                                            <input type="number" step="0.01" min="0"
                                                   :name="`items[${index}][rate]`"
                                                   :id="`field_item_${index}_rate`"
                                                   x-model.number="item.rate"
                                                   @input="clearError('item_' + index + '_rate')"
                                                   @keydown.enter.prevent="addItem()"
                                                   :class="fieldErrors['item_' + index + '_rate'] ? 'border-rose-500 ring-2 ring-rose-200 bg-rose-50/40 text-rose-900' : 'border-slate-300'"
                                                   class="w-full pl-6 pr-2.5 py-1.5 text-sm font-mono text-right rounded-lg border focus:ring-2 focus:ring-violet-500 focus:outline-none transition-colors">
                                        </div>
                                        <p x-show="fieldErrors['item_' + index + '_rate']" x-text="fieldErrors['item_' + index + '_rate']" class="text-[10px] text-rose-600 font-bold mt-1 text-right"></p>
                                    </td>

                                    <td class="py-2 px-3 align-top" x-show="taxMode !== 'simple'">
                                        <select :name="`items[${index}][gst_percent]`" x-model.number="item.gst_percent" class="w-full px-2 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 focus:ring-2 focus:ring-violet-500 focus:outline-none">
                                            <option value="0">0%</option>
                                            <option value="5">5%</option>
                                            <option value="12">12%</option>
                                            <option value="18">18%</option>
                                            <option value="28">28%</option>
                                        </select>
                                    </td>
                                    <template x-if="taxMode === 'simple'">
                                        <input type="hidden" :name="`items[${index}][gst_percent]`" :value="item.gst_percent || 18">
                                    </template>

                                    <td class="py-2 px-3 text-right font-mono font-bold text-slate-800 align-top pt-3" x-text="formatCurrency(calculateLineTotal(item))"></td>

                                    <td class="py-2 px-2 text-center align-top pt-2">
                                        <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="w-7 h-7 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 font-bold">&times;</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-slate-50 border-t border-slate-200">
                            <tr>
                                <td colspan="9" class="p-2">
                                    <button type="button" @click="addItem()" class="w-full py-2 rounded-lg border-2 border-dashed border-slate-300 hover:border-violet-500 text-xs font-bold text-slate-600 hover:text-violet-600 transition-colors">
                                        + Add Another Item (Or press Enter)
                                    </button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Summary Bar -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Customer Notes / Terms</label>
                        <textarea name="notes" rows="3" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">{{ old('notes', $invoice->notes) }}</textarea>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 font-mono text-sm space-y-2">
                        <div class="flex justify-between text-slate-600">
                            <span>Taxable Subtotal:</span>
                            <span class="font-bold" x-text="formatCurrency(totalTaxable())"></span>
                        </div>

                        <template x-if="taxMode === 'simple'">
                            <div class="flex justify-between text-violet-700 font-semibold">
                                <span>GST (Total Tax):</span>
                                <span x-text="formatCurrency(totalTax())"></span>
                            </div>
                        </template>

                        <template x-if="taxMode === 'detailed'">
                            <div class="space-y-1 border-t border-slate-200/60 pt-1">
                                <template x-if="saleType === 'LOCAL'">
                                    <div>
                                        <div class="flex justify-between text-emerald-700 text-xs">
                                            <span>CGST (Central Tax):</span>
                                            <span x-text="formatCurrency(totalTax() / 2)"></span>
                                        </div>
                                        <div class="flex justify-between text-emerald-700 text-xs">
                                            <span>SGST (State Tax):</span>
                                            <span x-text="formatCurrency(totalTax() / 2)"></span>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="saleType === 'CENTRAL'">
                                    <div class="flex justify-between text-purple-700 text-xs">
                                        <span>IGST (Integrated Tax):</span>
                                        <span x-text="formatCurrency(totalTax())"></span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <div class="flex justify-between text-base font-extrabold text-slate-900 border-t-2 border-slate-300 pt-2">
                            <span>Grand Total:</span>
                            <span class="text-violet-700" x-text="formatCurrency(grandTotal())"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('invoices.show', $invoice->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold shadow-md shadow-violet-600/20 active:scale-[0.99] transition-all">
                    Update Invoice
                </button>
            </div>
        </form>

    </div>

    @push('scripts')
    <script>
        function invoiceEditor() {
            return {
                taxMode: '{{ old('tax_mode', $invoice->tax_mode) }}',
                catalog: @json($products),
                selectedCustomerId: '{{ old('customer_id', $invoice->customer_id) }}',
                saleType: '{{ old('sale_type', $invoice->sale_type) }}',
                fieldErrors: @json($initialServerErrors),
                formErrorMessage: '{{ $errors->any() ? "Kuch fields me galti hai. Kripya niche highlighted red fields ko check karein." : "" }}',
                items: @json($initialEditItems),
                checkCatalog(item) {
                    const found = this.catalog.find(p => p.name.toLowerCase() === (item.description || '').trim().toLowerCase());
                    if (found) {
                        if (found.hsn_sac) item.hsn_sac = found.hsn_sac;
                        if (found.unit) item.unit = found.unit;
                        if (found.rate) item.rate = found.rate;
                        if (found.gst_percent !== undefined) item.gst_percent = found.gst_percent;
                    }
                },
                addItem() {
                    this.items.push({
                        description: '',
                        domain_name: '',
                        service_period_start: '',
                        service_period_end: '',
                        billing_cycle: '1 Year',
                        hsn_sac: '998313',
                        quantity: 1,
                        unit: 'Pcs',
                        rate: 0,
                        gst_percent: 18
                    });
                    this.$nextTick(() => {
                        const newIdx = this.items.length - 1;
                        const newEl = document.getElementById(`field_item_${newIdx}_description`);
                        if (newEl) newEl.focus();
                    });
                },
                removeItem(idx) {
                    if (this.items.length > 1) {
                        this.items.splice(idx, 1);
                        const updated = {};
                        Object.keys(this.fieldErrors).forEach(k => {
                            if (!k.startsWith('item_')) updated[k] = this.fieldErrors[k];
                        });
                        this.fieldErrors = updated;
                    }
                },
                calculateLineTotal(item) {
                    const taxable = (Number(item.quantity) || 0) * (Number(item.rate) || 0);
                    const tax = taxable * ((Number(item.gst_percent) || 0) / 100);
                    return taxable + tax;
                },
                totalTaxable() {
                    return this.items.reduce((sum, item) => sum + ((Number(item.quantity) || 0) * (Number(item.rate) || 0)), 0);
                },
                totalTax() {
                    return this.items.reduce((sum, item) => {
                        const taxable = (Number(item.quantity) || 0) * (Number(item.rate) || 0);
                        return sum + (taxable * ((Number(item.gst_percent) || 0) / 100));
                    }, 0);
                },
                grandTotal() {
                    return this.totalTaxable() + this.totalTax();
                },
                formatCurrency(val) {
                    return '₹' + (Number(val) || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
                clearError(fieldKey) {
                    if (this.fieldErrors[fieldKey]) {
                        delete this.fieldErrors[fieldKey];
                        if (Object.keys(this.fieldErrors).length === 0) {
                            this.formErrorMessage = '';
                        }
                    }
                },
                validateForm(e) {
                    this.fieldErrors = {};
                    this.formErrorMessage = '';

                    const invEl = document.getElementById('field_invoice_number');
                    if (!invEl || !invEl.value.trim()) {
                        this.fieldErrors['invoice_number'] = 'Invoice number is required.';
                    }

                    if (!this.selectedCustomerId) {
                        this.fieldErrors['customer_id'] = 'Please select a customer.';
                    }

                    const dateEl = document.getElementById('field_invoice_date');
                    if (!dateEl || !dateEl.value) {
                        this.fieldErrors['invoice_date'] = 'Invoice date is required.';
                    }

                    if (!this.items || this.items.length === 0) {
                        this.fieldErrors['items_general'] = 'Please add at least one item.';
                    } else {
                        this.items.forEach((item, idx) => {
                            const desc = (item.description || '').trim();
                            if (!desc) {
                                this.fieldErrors['item_' + idx + '_description'] = `Row #${idx + 1}: Item description is required.`;
                            }

                            const qty = Number(item.quantity);
                            if (item.quantity === '' || item.quantity === null || isNaN(qty) || qty <= 0) {
                                this.fieldErrors['item_' + idx + '_quantity'] = `Row #${idx + 1}: Qty must be > 0.`;
                            }

                            const rate = Number(item.rate);
                            if (item.rate === '' || item.rate === null || isNaN(rate) || rate < 0) {
                                this.fieldErrors['item_' + idx + '_rate'] = `Row #${idx + 1}: Valid rate is required.`;
                            }
                        });
                    }

                    if (Object.keys(this.fieldErrors).length > 0) {
                        e.preventDefault();
                        this.formErrorMessage = 'Kuch details khali ya galat hain. Niche lal (red) highlight kiye gaye fields ko fill karein.';

                        this.$nextTick(() => {
                            const firstErr = document.querySelector('.border-rose-500');
                            if (firstErr) {
                                firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                firstErr.focus();
                            }
                        });
                        return false;
                    }
                    return true;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
