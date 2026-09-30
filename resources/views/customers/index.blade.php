<x-app-layout header="Customer Master Directory">
    <div class="space-y-6" x-data="{ createModal: false }">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Clients & Customers</h2>
                <p class="text-sm text-slate-500">Manage client directory, GSTIN numbers, and billing ledgers.</p>
            </div>
            <button type="button" @click="createModal = true" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 active:scale-[0.98] text-white text-sm font-bold shadow-md shadow-violet-600/25 transition-all">
                <span class="text-base font-black">+</span> Add Customer
            </button>
        </div>

        <!-- Search Bar -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex items-center justify-between">
            <form action="{{ route('customers.index') }}" method="GET" class="w-full max-w-md flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, company, phone, GSTIN..."
                       class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 text-white text-sm font-bold hover:bg-slate-800 transition-colors">
                    Search
                </button>
                @if(request('search'))
                <a href="{{ route('customers.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800 font-semibold">Clear</a>
                @endif
            </form>
        </div>

        <!-- Customers Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Customer / Business</th>
                            <th class="py-3 px-4">Contact</th>
                            <th class="py-3 px-4">GSTIN</th>
                            <th class="py-3 px-4">State</th>
                            <th class="py-3 px-4 text-center">Invoices</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($customers as $c)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $c->name }}</div>
                                @if($c->company_name)
                                <div class="text-xs text-slate-500">{{ $c->company_name }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <div>{{ $c->phone ?: 'No phone' }}</div>
                                <div class="text-slate-400">{{ $c->email ?: 'No email' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs">
                                @if($c->gstin)
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 font-semibold">{{ $c->gstin }}</span>
                                @else
                                    <span class="text-slate-400 italic">UNREGISTERED</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs font-medium">
                                {{ $c->state }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-700">
                                {{ $c->invoices_count }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <form action="{{ route('customers.destroy', $c) }}" method="POST" onsubmit="return confirm('Remove customer {{ $c->name }}?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-semibold">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-14 text-center">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-14 h-14 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-3xl mx-auto shadow-sm">
                                        👥
                                    </div>
                                    <h4 class="text-base font-bold text-slate-900">No Customers Added Yet</h4>
                                    <p class="text-xs text-slate-500 leading-relaxed">
                                        Add your buyer companies, clients, or retail parties with their GSTIN and billing address to start issuing tax invoices.
                                    </p>
                                    <div class="pt-2">
                                        <button type="button" @click="createModal = true" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 active:scale-[0.98] text-white text-xs font-bold shadow-md shadow-violet-600/25 transition-all">
                                            <span>+</span> Add Your First Customer
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($customers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
            @endif
        </div>

        <!-- Create Customer Modal -->
        <div x-show="createModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full p-6" @click.outside="createModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg font-bold">
                            👤
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-slate-900">Add New Customer</h3>
                            <p class="text-xs text-slate-500">Save client profile for instant billing and GSTR-1 filings.</p>
                        </div>
                    </div>
                    <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-700 text-2xl leading-none">&times;</button>
                </div>

                <form action="{{ route('customers.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Contact Person / Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Ramesh Sharma" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Business / Company Name</label>
                            <input type="text" name="company_name" placeholder="e.g. Sharma Traders Pvt Ltd" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Phone Number</label>
                            <input type="text" name="phone" placeholder="+91 9876543210" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Email (Optional)</label>
                            <input type="email" name="email" placeholder="billing@client.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">GSTIN (Optional)</label>
                            <input type="text" name="gstin" placeholder="10AAAAA0000A1Z5" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 uppercase font-mono text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">State / Supply Region *</label>
                        <input type="text" name="state" value="{{ auth()->user()->company->state ?? 'Bihar' }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Billing Address</label>
                        <textarea name="billing_address" rows="2" placeholder="Full street address, city, pincode" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="createModal = false" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold shadow-md shadow-violet-600/20 transition-all">Save Customer</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
