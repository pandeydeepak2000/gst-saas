<x-app-layout header="Edit Invoice #{{ $invoice->invoice_number }}">
    <div class="max-w-6xl mx-auto space-y-6" x-data="invoiceEditor()">
        
        <form action="{{ route('invoices.update', $invoice->id) }}" method="POST" @submit="validateForm($event)" class="space-y-6">
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
                        <label class="px-3 py-1 rounded-lg cursor-pointer transition-colors" :class="taxMode === 'simple' ? 'bg-white shadow-sm text-brand-700 font-bold' : 'text-slate-600'">
                            <input type="radio" name="tax_mode" value="simple" x-model="taxMode" class="hidden"> Simple GST (18%)
                        </label>
                        <label class="px-3 py-1 rounded-lg cursor-pointer transition-colors" :class="taxMode === 'detailed' ? 'bg-white shadow-sm text-brand-700 font-bold' : 'text-slate-600'">
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
                        <input type="text" name="invoice_number" value="{{ old('invoice_number', $invoice->invoice_number) }}" required
                               class="w-full px-3.5 py-2 rounded-xl border-2 border-brand-200 text-brand-900 font-mono font-bold focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none bg-brand-50/20">
                        <p class="text-[11px] text-brand-600 font-medium mt-1">
                            âœï¸ Freely editable by company
                        </p>
                    </div>

                    <!-- CUSTOMER SELECTOR -->
                    <div class="md:col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Customer / Client *</label>
                        <select name="customer_id" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', $invoice->customer_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} {{ $c->company_name ? "({$c->company_name})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- DATES -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Invoice Date *</label>
                        <input type="date" name="invoice_date" value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Due Date</label>
                        <input type="date" name="due_date" value="{{ old('due_date', optional($invoice->due_date)->format('Y-m-d')) }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Supply Region *</label>
                        <select name="sale_type" x-model="saleType" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            <option value="LOCAL" {{ $invoice->sale_type === 'LOCAL' ? 'selected' : '' }}>Intra-State: Bihar (CGST + SGST)</option>
                            <option value="CENTRAL" {{ $invoice->sale_type === 'CENTRAL' ? 'selected' : '' }}>Inter-State (IGST)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Payment Status</label>
                        <select name="status" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            <option value="unpaid" {{ $invoice->status === 'unpaid' ? 'selected' : '' }}>Unpaid / Due</option>
                            <option value="paid" {{ $invoice->status === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="partially_paid" {{ $invoice->status === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                            <option value="draft" {{ $invoice->status === 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Payment Method</label>
                        <input type="text" name="payment_method" value="{{ old('payment_method', $invoice->payment_method) }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
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
                                        <button type="button" @click="addItem()" class="px-3 py-1.5 rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-bold border border-brand-200 transition-colors">
                        + Add Item Row
                    </button>
                </div>

                <!-- Product Autocomplete Datalist -->
                <datalist id="products-catalog">
                    @foreach($products as $prod)
                        <option value="{{ $prod->name }}">{{ $prod->name }} (â‚¹{{ number_format($prod->rate, 2) }} - HSN: {{ $prod->hsn_sac ?: 'N/A' }})</option>
                    @endforeach
                </datalist>

                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3 w-12 text-center">#</th>
                                <th class="py-2.5 px-3 min-w-[240px]">Item Description *</th>
                                <th class="py-2.5 px-3 w-28">HSN/SAC</th>
                                <th class="py-2.5 px-3 w-24">Qty *</th>
                                <th class="py-2.5 px-3 w-24">Unit</th>
                                <th class="py-2.5 px-3 w-32">Rate (â‚¹) *</th>
                                <th class="py-2.5 px-3 w-24">GST %</th>
                                <th class="py-2.5 px-3 w-32 text-right">Total (â‚¹)</th>
                                <th class="py-2.5 px-2 w-10 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2 px-3 text-center text-xs font-mono font-bold text-slate-400" x-text="index + 1"></td>
                                    
                                    <td class="py-2 px-3">
                                        <input type="text" :name="`items[${index}][description]`" x-model="item.description" required placeholder="Item description or service..."
                                               list="products-catalog"
                                               @input="checkCatalog(item)"
                                               @keydown.enter.prevent="addItem()"
                                               class="w-full px-3 py-1.5 text-sm rounded-lg border border-slate-300 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                        
                                        <!-- Hosting / Domain & Service Period Inputs -->
                                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5 text-[11px] bg-slate-50 p-1.5 rounded-lg border border-slate-200/70">
                                            <span class="text-brand-700 font-bold">ðŸŒ Domain/Cloud:</span>
                                            <input type="text" :name="`items[${index}][domain_name]`" x-model="item.domain_name" placeholder="e.g. greenstudio.jixsite.com"
                                                   class="px-2 py-0.5 text-xs rounded border border-slate-300 w-44 font-mono text-brand-900 bg-white">
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

                                    <td class="py-2 px-3">
                                        <input type="text" :name="`items[${index}][hsn_sac]`" x-model="item.hsn_sac" placeholder="HSN"
                                               class="w-full px-2.5 py-1.5 text-xs font-mono rounded-lg border border-slate-300 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                    </td>

                                    <td class="py-2 px-3">
                                        <input type="number" step="0.01" min="0.01" :name="`items[${index}][quantity]`" x-model.number="item.quantity" required
                                               class="w-full px-2.5 py-1.5 text-sm text-center rounded-lg border border-slate-300 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                    </td>

                                    <td class="py-2 px-3">
                                        <select :name="`items[${index}][unit]`" x-model="item.unit" class="w-full px-1.5 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-1 focus:ring-brand-500 focus:outline-none">
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

                                    <td class="py-2 px-3">
                                        <input type="number" step="0.01" min="0" :name="`items[${index}][rate]`" x-model.number="item.rate" required
                                               @keydown.enter.prevent="addItem()"
                                               class="w-full px-2.5 py-1.5 text-sm font-mono text-right rounded-lg border border-slate-300 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                    </td>

                                    <td class="py-2 px-3">
                                        <select :name="`items[${index}][gst_percent]`" x-model.number="item.gst_percent" class="w-full px-2 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                            <option value="0">0%</option>
                                            <option value="5">5%</option>
                                            <option value="12">12%</option>
                                            <option value="18">18%</option>
                                            <option value="28">28%</option>
                                        </select>
                                    </td>

                                    <td class="py-2 px-3 text-right font-mono font-bold text-slate-800" x-text="formatCurrency(calculateLineTotal(item))"></td>

                                    <td class="py-2 px-2 text-center">
                                        <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-slate-400 hover:text-rose-600 font-bold">&times;</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-slate-50 border-t border-slate-200">
                            <tr>
                                <td colspan="9" class="p-2">
                                    <button type="button" @click="addItem()" class="w-full py-2 rounded-lg border-2 border-dashed border-slate-300 hover:border-brand-500 text-xs font-bold text-slate-600 hover:text-brand-600 transition-colors">
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
                        <textarea name="notes" rows="3" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('notes', $invoice->notes) }}</textarea>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 font-mono text-sm space-y-2">
                        <div class="flex justify-between text-slate-600">
                            <span>Taxable Subtotal:</span>
                            <span class="font-bold" x-text="formatCurrency(totalTaxable())"></span>
                        </div>

                        <template x-if="taxMode === 'simple'">
                            <div class="flex justify-between text-brand-700 font-semibold">
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
                            <span class="text-brand-700" x-text="formatCurrency(grandTotal())"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('invoices.show', $invoice->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold shadow-md shadow-brand-600/20 active:scale-[0.99] transition-all">
                    Update Invoice
                </button>
            </div>
        </form>

    </div>

    @push('scripts')
    <script>
        function invoiceEditor() {
            const existingItems = @json($existingItems);

            return {
                taxMode: '{{ $invoice->tax_mode }}',
                catalog: @json($products),
                checkCatalog(item) {
                    const found = this.catalog.find(p => p.name.toLowerCase() === (item.description || '').trim().toLowerCase());
                    if (found) {
                        if (found.hsn_sac) item.hsn_sac = found.hsn_sac;
                        if (found.unit) item.unit = found.unit;
                        if (found.rate) item.rate = found.rate;
                        if (found.gst_percent !== undefined) item.gst_percent = found.gst_percent;
                    }
                },
                saleType: '{{ $invoice->sale_type }}',
                items: existingItems.length > 0 ? existingItems : [{ description: '', domain_name: '', service_period_start: '', service_period_end: '', billing_cycle: '1 Year', hsn_sac: '', quantity: 1, unit: 'Pcs', rate: 0, gst_percent: 18 }],
                addItem() {
                    this.items.push({ description: '', domain_name: '', service_period_start: '', service_period_end: '', billing_cycle: '1 Year', hsn_sac: '', quantity: 1, unit: 'Pcs', rate: 0, gst_percent: 18 });
                },
                removeItem(idx) {
                    if (this.items.length > 1) {
                        this.items.splice(idx, 1);
                    }
                },
                calculateLineTotal(item) {
                    const taxable = (item.quantity || 0) * (item.rate || 0);
                    const tax = taxable * ((item.gst_percent || 0) / 100);
                    return taxable + tax;
                },
                totalTaxable() {
                    return this.items.reduce((sum, item) => sum + ((item.quantity || 0) * (item.rate || 0)), 0);
                },
                totalTax() {
                    return this.items.reduce((sum, item) => {
                        const taxable = (item.quantity || 0) * (item.rate || 0);
                        return sum + (taxable * ((item.gst_percent || 0) / 100));
                    }, 0);
                },
                grandTotal() {
                    return this.totalTaxable() + this.totalTax();
                },
                formatCurrency(val) {
                    return 'â‚¹' + (Number(val) || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
                validateForm(e) {
                    if (this.items.length === 0) {
                        alert('Please add at least one line item.');
                        e.preventDefault();
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
