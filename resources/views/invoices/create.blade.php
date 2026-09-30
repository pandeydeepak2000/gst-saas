<x-app-layout header="Create GST Tax Invoice">
    <div class="max-w-6xl mx-auto space-y-6" x-data="invoiceBuilder()">
        
        <form action="{{ route('invoices.store') }}" method="POST" @submit="validateForm($event)" class="space-y-6">
            @csrf

            <!-- Top Header Card -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">New Tax Invoice</h2>
                        <p class="text-xs text-slate-500">Issuer: <strong class="text-slate-800">{{ $company->name }}</strong> (GSTIN: {{ $company->gstin ?: 'Not set' }})</p>
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
                    
                    <!-- 1. EDITABLE INVOICE NUMBER -->
                    <div class="md:col-span-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Invoice Number *
                        </label>
                        <input type="text" name="invoice_number" value="{{ old('invoice_number', $suggestedNumber) }}" required
                               class="w-full px-3.5 py-2 rounded-xl border-2 border-violet-200 text-violet-900 font-mono font-bold focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none bg-violet-50/20">
                        <p class="text-[11px] text-violet-600 font-medium mt-1 flex items-center gap-1">
                            <span>✏️</span> Freely editable by company
                        </p>
                    </div>

                    <!-- 2. CUSTOMER SELECTOR WITH QUICK ADD -->
                    <div class="md:col-span-1">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Customer / Client *</label>
                            <button type="button" @click="showQuickCustomerModal = true" class="text-xs font-bold text-violet-600 hover:text-violet-800 flex items-center gap-1 transition-colors">
                                <span class="w-4 h-4 rounded-full bg-violet-100 flex items-center justify-center text-violet-700 text-xs font-black">+</span>
                                <span>Quick Add</span>
                            </button>
                        </div>
                        <select name="customer_id" x-model="selectedCustomerId" @change="onCustomerChange($event.target.value)" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                            <option value="">Select Customer...</option>
                            <template x-for="c in customerList" :key="c.id">
                                <option :value="c.id" x-text="c.name + (c.company_name ? ' (' + c.company_name + ')' : '')" :selected="c.id == selectedCustomerId"></option>
                            </template>
                        </select>
                        <template x-if="customerList.length === 0">
                            <div class="mt-1.5 p-2 rounded-lg bg-amber-50 border border-amber-200/80 text-amber-900 text-[11px] flex items-center justify-between">
                                <span>No clients registered yet.</span>
                                <button type="button" @click="showQuickCustomerModal = true" class="font-bold underline text-violet-700 hover:text-violet-900">+ Add First Client</button>
                            </div>
                        </template>
                    </div>

                    <!-- 3. INVOICE DATE -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Invoice Date *</label>
                        <input type="date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    </div>

                    <!-- 4. DUE DATE -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Due Date</label>
                        <input type="date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+15 days'))) }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    </div>
                </div>

                <!-- Secondary Row -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Document Type</label>
                        <select name="type" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-violet-500 focus:outline-none">
                            <option value="tax_invoice" selected>Official GST Tax Invoice</option>
                            <option value="proforma">Proforma / Estimate / Quotation</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Supply Region *</label>
                        <select name="sale_type" x-model="saleType" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                            <option value="LOCAL">Intra-State: {{ $company->state ?? 'Local' }} (CGST + SGST)</option>
                            <option value="CENTRAL">Inter-State (IGST)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Initial Status</label>
                        <select name="status" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                            <option value="unpaid">Unpaid / Due</option>
                            <option value="paid">Paid</option>
                            <option value="partially_paid">Partially Paid</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Payment Method / Note</label>
                        <input type="text" name="payment_method" placeholder="e.g. UPI, NetBanking, Cash"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Line Items Table (Single-line, professional 38px inputs) -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900">Line Items & Products</h3>
                        <p class="text-xs text-slate-500">Single-line streamlined item rows. Press <kbd class="px-1.5 py-0.5 rounded bg-slate-100 border text-[11px]">Enter</kbd> to add row automatically.</p>
                    </div>
                    @if(count($products) > 0)
                        <span class="text-xs text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">
                            💡 {{ count($products) }} catalog products available for auto-fill
                        </span>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border border-slate-200 rounded-xl overflow-hidden">
                        <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3 w-10 text-center">#</th>
                                <th class="py-2.5 px-3 min-w-[280px]">Item Description & Details</th>
                                <th class="py-2.5 px-3 w-28">HSN/SAC</th>
                                <th class="py-2.5 px-3 w-24">Qty</th>
                                <th class="py-2.5 px-3 w-24">Unit</th>
                                <th class="py-2.5 px-3 w-32">Rate (₹)</th>
                                <th class="py-2.5 px-3 w-24" x-show="taxMode !== 'simple'">GST %</th>
                                <th class="py-2.5 px-3 w-36 text-right">Taxable</th>
                                <th class="py-2.5 px-3 w-36 text-right">Total (₹)</th>
                                <th class="py-2.5 px-2 w-10 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <!-- Index -->
                                    <td class="py-2 px-3 text-center text-xs font-mono text-slate-400 font-bold" x-text="index + 1"></td>

                                    <!-- Description & Domain/Hosting Options -->
                                    <td class="py-2 px-3">
                                        <div class="space-y-1.5">
                                            <input type="text" x-model="item.description" @input="checkCatalog(item)" required
                                                   placeholder="Product or service name..." list="catalog-list"
                                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-violet-500 focus:outline-none">
                                            
                                            <!-- Optional Hosting/Domain expansion row -->
                                            <div class="flex items-center gap-2 pt-0.5">
                                                <input type="text" x-model="item.domain_name" placeholder="Optional domain (e.g. client.com)"
                                                       class="w-1/2 px-2.5 py-1 rounded-md border border-slate-200 text-[11px] text-slate-600 font-mono focus:border-violet-500 focus:outline-none bg-slate-50/50">
                                                
                                                <div class="flex items-center gap-1 w-1/2 text-[11px]">
                                                    <input type="date" x-model="item.service_period_start" title="Period Start"
                                                           class="w-1/2 px-1.5 py-1 rounded-md border border-slate-200 text-[10px] text-slate-600 focus:outline-none">
                                                    <span class="text-slate-400">-</span>
                                                    <input type="date" x-model="item.service_period_end" title="Period End"
                                                           class="w-1/2 px-1.5 py-1 rounded-md border border-slate-200 text-[10px] text-slate-600 focus:outline-none">
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- HSN/SAC -->
                                    <td class="py-2 px-3">
                                        <input type="text" x-model="item.hsn_sac" placeholder="998313"
                                               class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-violet-500 focus:outline-none">
                                    </td>

                                    <!-- Quantity -->
                                    <td class="py-2 px-3">
                                        <input type="number" x-model.number="item.quantity" min="0.01" step="any" required
                                               class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-center focus:ring-2 focus:ring-violet-500 focus:outline-none">
                                    </td>

                                    <!-- Unit -->
                                    <td class="py-2 px-3">
                                        <select x-model="item.unit" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-violet-500 focus:outline-none">
                                            <option value="Pcs">Pcs</option>
                                            <option value="Year">Year</option>
                                            <option value="Month">Month</option>
                                            <option value="Hours">Hours</option>
                                            <option value="Service">Service</option>
                                            <option value="Job">Job</option>
                                            <option value="Kg">Kg</option>
                                            <option value="Nos">Nos</option>
                                        </select>
                                    </td>

                                    <!-- Rate -->
                                    <td class="py-2 px-3">
                                        <div class="relative">
                                            <span class="absolute left-2.5 top-1.5 text-xs text-slate-400">₹</span>
                                            <input type="number" x-model.number="item.rate" min="0" step="0.01" required
                                                   class="w-full pl-6 pr-2.5 py-1.5 rounded-lg border border-slate-300 font-mono text-xs font-bold text-right focus:ring-2 focus:ring-violet-500 focus:outline-none">
                                        </div>
                                    </td>

                                    <!-- GST Percent (hidden in simple mode) -->
                                    <td class="py-2 px-3" x-show="taxMode !== 'simple'">
                                        <select x-model.number="item.gst_percent" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-xs font-bold focus:ring-2 focus:ring-violet-500 focus:outline-none">
                                            <option value="0">0%</option>
                                            <option value="5">5%</option>
                                            <option value="12">12%</option>
                                            <option value="18">18%</option>
                                            <option value="28">28%</option>
                                        </select>
                                    </td>

                                    <!-- Taxable Amount (Calculated) -->
                                    <td class="py-2 px-3 text-right font-mono text-xs text-slate-700 font-semibold"
                                        x-text="formatCurrency((item.quantity || 0) * (item.rate || 0))">
                                    </td>

                                    <!-- Line Total (Calculated) -->
                                    <td class="py-2 px-3 text-right font-mono text-xs text-slate-900 font-bold"
                                        x-text="formatCurrency(calculateLineTotal(item))">
                                    </td>

                                    <!-- Remove Action -->
                                    <td class="py-2 px-2 text-center">
                                        <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                                class="w-7 h-7 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition-colors">
                                            &times;
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Add Row Button -->
                <div class="pt-2">
                    <button type="button" @click="addItem()" class="w-full py-2.5 rounded-xl border-2 border-dashed border-slate-300 hover:border-violet-500 text-xs font-bold text-slate-600 hover:text-violet-700 transition-colors flex items-center justify-center gap-2 bg-slate-50/50 hover:bg-violet-50/20">
                        <span>+</span> Add Another Line Item
                    </button>
                </div>
            </div>

            <!-- Datalist for Product Auto-complete -->
            <datalist id="catalog-list">
                @foreach($products as $p)
                    <option value="{{ $p->name }}">{{ $p->hsn_sac ? 'HSN: ' . $p->hsn_sac : '' }} (₹{{ number_format($p->rate, 2) }})</option>
                @endforeach
            </datalist>

            <!-- Bottom Summary & Totals Card -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Notes & Terms -->
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Invoice Notes / Subject</label>
                        <textarea name="notes" rows="2" placeholder="e.g. Website development milestone 1 or Cloud server renewal"
                                  class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Terms & Conditions</label>
                        <textarea name="terms_and_conditions" rows="3"
                                  class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-violet-500 focus:outline-none">{{ $company->terms_and_conditions }}</textarea>
                    </div>
                </div>

                <!-- Live Tax Breakdown & Grand Total -->
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-3">
                    <h3 class="font-bold text-slate-900 text-sm pb-2 border-b border-slate-100 flex items-center justify-between">
                        <span>Invoice Summary</span>
                        <span class="text-xs font-mono font-normal text-slate-500" x-text="taxMode === 'simple' ? 'Simple 18% GST' : 'Detailed Split Tax'"></span>
                    </h3>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Taxable Subtotal:</span>
                            <span class="font-mono font-bold text-slate-900" x-text="formatCurrency(totalTaxable())"></span>
                        </div>

                        <!-- Tax Breakdown based on Sale Type -->
                        <template x-if="saleType === 'LOCAL'">
                            <div class="space-y-1.5 pt-1 border-t border-slate-100">
                                <div class="flex justify-between text-slate-600">
                                    <span>CGST (Central Tax 50%):</span>
                                    <span class="font-mono text-slate-800" x-text="formatCurrency(totalTax() / 2)"></span>
                                </div>
                                <div class="flex justify-between text-slate-600">
                                    <span>SGST (State Tax 50%):</span>
                                    <span class="font-mono text-slate-800" x-text="formatCurrency(totalTax() / 2)"></span>
                                </div>
                            </div>
                        </template>

                        <template x-if="saleType === 'CENTRAL'">
                            <div class="flex justify-between text-slate-600 pt-1 border-t border-slate-100">
                                <span>IGST (Integrated Interstate Tax):</span>
                                <span class="font-mono text-slate-800" x-text="formatCurrency(totalTax())"></span>
                            </div>
                        </template>

                        <div class="flex justify-between text-slate-600 pt-1 border-t border-slate-100">
                            <span>Total GST Calculated:</span>
                            <span class="font-mono font-bold text-slate-900" x-text="formatCurrency(totalTax())"></span>
                        </div>

                        <div class="flex justify-between items-center text-sm font-black pt-3 border-t-2 border-slate-900 text-slate-900">
                            <span class="text-base font-black">Grand Total:</span>
                            <span class="text-2xl font-mono text-violet-700" x-text="formatCurrency(grandTotal())"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit CTA -->
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('invoices.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-7 py-3 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold shadow-lg shadow-violet-600/25 active:scale-[0.99] transition-all flex items-center gap-2">
                    <span>🧾</span> Generate & Save Invoice
                </button>
            </div>
        </form>

        <!-- Quick Add Customer Modal -->
        <div x-show="showQuickCustomerModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 space-y-4" @click.outside="showQuickCustomerModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg font-bold">
                            👤
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-slate-900">Quick Add Customer</h3>
                            <p class="text-xs text-slate-500">Save client details without leaving this invoice page.</p>
                        </div>
                    </div>
                    <button type="button" @click="showQuickCustomerModal = false" class="text-slate-400 hover:text-slate-700 text-2xl leading-none">&times;</button>
                </div>

                <div x-show="quickCustomerError" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium" x-text="quickCustomerError"></div>

                <div class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Contact Person / Name *</label>
                        <input type="text" x-model="quickCustomer.name" placeholder="e.g. Ramesh Kumar" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Business / Company Name</label>
                            <input type="text" x-model="quickCustomer.company_name" placeholder="e.g. Ramesh Enterprises" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Phone Number</label>
                            <input type="text" x-model="quickCustomer.phone" placeholder="+91 9876543210" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Email (Optional)</label>
                            <input type="email" x-model="quickCustomer.email" placeholder="client@domain.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">GSTIN (Optional)</label>
                            <input type="text" x-model="quickCustomer.gstin" placeholder="10AAAAA0000A1Z5" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 uppercase font-mono text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">State / Supply Region *</label>
                        <input type="text" x-model="quickCustomer.state" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Billing Address</label>
                        <textarea x-model="quickCustomer.billing_address" rows="2" placeholder="Full address, city, pincode" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="showQuickCustomerModal = false" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition-colors">Cancel</button>
                        <button type="button" @click="saveQuickCustomer()" :disabled="savingCustomer" class="px-6 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold shadow-md shadow-violet-600/20 disabled:opacity-50 transition-all flex items-center gap-1.5">
                            <span x-show="savingCustomer" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="savingCustomer ? 'Saving...' : 'Save & Select Customer'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function invoiceBuilder() {
            return {
                taxMode: '{{ $company->tax_mode }}',
                catalog: @json($products),
                customerList: @json($customers),
                selectedCustomerId: '{{ old('customer_id', '') }}',
                showQuickCustomerModal: false,
                savingCustomer: false,
                quickCustomerError: '',
                quickCustomer: {
                    name: '',
                    company_name: '',
                    phone: '',
                    email: '',
                    state: '{{ $company->state ?? 'Bihar' }}',
                    gstin: '',
                    billing_address: ''
                },
                saleType: 'LOCAL',
                items: [
                    { description: '', domain_name: '', service_period_start: '', service_period_end: '', billing_cycle: '1 Year', hsn_sac: '', quantity: 1, unit: 'Pcs', rate: 0, gst_percent: 18 }
                ],
                onCustomerChange(custId) {
                    const cust = this.customerList.find(c => String(c.id) === String(custId));
                    if (cust && cust.state) {
                        const companyState = '{{ strtolower(trim($company->state ?? 'bihar')) }}';
                        const custState = cust.state.trim().toLowerCase();
                        if (custState && custState !== companyState) {
                            this.saleType = 'CENTRAL';
                        } else {
                            this.saleType = 'LOCAL';
                        }
                    }
                },
                async saveQuickCustomer() {
                    if (!this.quickCustomer.name || !this.quickCustomer.state) {
                        this.quickCustomerError = 'Please provide Contact Person Name and State.';
                        return;
                    }
                    this.savingCustomer = true;
                    this.quickCustomerError = '';
                    try {
                        const response = await fetch('{{ route('customers.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(this.quickCustomer)
                        });
                        const data = await response.json();
                        if (response.ok && data.success) {
                            this.customerList.push(data.customer);
                            this.selectedCustomerId = String(data.customer.id);
                            this.onCustomerChange(data.customer.id);
                            this.showQuickCustomerModal = false;
                            this.quickCustomer = {
                                name: '',
                                company_name: '',
                                phone: '',
                                email: '',
                                state: '{{ $company->state ?? 'Bihar' }}',
                                gstin: '',
                                billing_address: ''
                            };
                        } else {
                            this.quickCustomerError = data.message || 'Validation error while saving customer.';
                        }
                    } catch (err) {
                        this.quickCustomerError = 'Connection failed: ' + err.message;
                    } finally {
                        this.savingCustomer = false;
                    }
                },
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
                    return '₹' + (Number(val) || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
                validateForm(e) {
                    if (!this.selectedCustomerId) {
                        alert('Please select a customer or click "+ Quick Add" to add one.');
                        e.preventDefault();
                        return;
                    }
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
