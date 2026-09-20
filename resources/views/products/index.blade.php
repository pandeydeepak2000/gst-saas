<x-app-layout header="Products & Services Catalog">
    <div class="space-y-6" x-data="{ createModal: false }">
        
        <!-- Action Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Goods & Services Master</h2>
                <p class="text-sm text-slate-500">Configure catalog items, HSN/SAC codes, default tax slabs, and rates for lightning-fast billing.</p>
            </div>
            <button @click="createModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition-all">
                + Add Item / Service
            </button>
        </div>

        <!-- Search Bar -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex items-center justify-between">
            <form action="{{ route('products.index') }}" method="GET" class="w-full max-w-md flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Product name, HSN/SAC code..."
                       class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('products.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800">Clear</a>
                @endif
            </form>
            <div class="hidden sm:block text-xs text-slate-400 font-mono">
                Total Products: <strong class="text-slate-700">{{ $totalProducts }}</strong>
            </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Item Name / Service</th>
                            <th class="py-3 px-4">HSN / SAC</th>
                            <th class="py-3 px-4">Default Unit</th>
                            <th class="py-3 px-4 text-right">Default Rate</th>
                            <th class="py-3 px-4 text-center">GST Slab</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($products as $p)
                        <tr class="hover:bg-slate-50/80">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $p->name }}</div>
                                @if($p->description)
                                    <div class="text-xs text-slate-400 truncate max-w-xs">{{ $p->description }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold text-brand-700">
                                {{ $p->hsn_sac ?: 'N/A' }}
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-600 font-medium">
                                {{ $p->unit }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900">
                                ₹{{ number_format($p->rate, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ $p->gst_percent }}% GST
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <form action="{{ route('products.destroy', $p) }}" method="POST" onsubmit="return confirm('Remove product {{ $p->name }}?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-semibold">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                No products catalogued yet. Click "+ Add Item / Service" to build your inventory!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
            @endif
        </div>

        <!-- Create Product Modal -->
        <div x-show="createModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full p-6" @click.outside="createModal = false">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg text-slate-900">Add Product or Service</h3>
                    <button @click="createModal = false" class="text-slate-400 hover:text-slate-700 text-xl">&times;</button>
                </div>

                <form action="{{ route('products.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Item / Service Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Brass Gate Valve 25mm" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">HSN / SAC Code</label>
                            <input type="text" name="hsn_sac" placeholder="e.g. 8481" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Unit *</label>
                            <select name="unit" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                                <option value="Pcs">Pcs (Pieces)</option>
                                <option value="Nos">Nos</option>
                                <option value="Kg">Kg</option>
                                <option value="Box">Box</option>
                                <option value="Set">Set</option>
                                <option value="Service">Service</option>
                                <option value="Hours">Hours</option>
                                <option value="Length">Length / Mtr</option>
                                <option value="Year">Year</option>
                                <option value="Job">Job</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Default Rate (₹) *</label>
                            <input type="number" step="0.01" min="0" name="rate" required placeholder="0.00" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm font-mono text-right focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">GST Tax Rate *</label>
                            <select name="gst_percent" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                                <option value="0">0% (Exempt)</option>
                                <option value="5">5%</option>
                                <option value="12">12%</option>
                                <option value="18" selected>18% (Standard)</option>
                                <option value="28">28%</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Short Description / Remarks</label>
                        <textarea name="description" rows="2" placeholder="Optional notes for quotation or billing" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="createModal = false" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-xs font-semibold">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold">Save Product</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
