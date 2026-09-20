<x-app-layout header="Customer Master Directory">
    <div class="space-y-6" x-data="{ createModal: false }">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Clients & Customers</h2>
                <p class="text-sm text-slate-500">Manage client directory, GSTIN numbers, and billing ledgers.</p>
            </div>
            <button @click="createModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition-all">
                + Add Customer
            </button>
        </div>

        <!-- Search Bar -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex items-center justify-between">
            <form action="{{ route('customers.index') }}" method="GET" class="w-full max-w-md flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, company, phone, GSTIN..."
                       class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">
                    Search
                </button>
                @if(request('search'))
                <a href="{{ route('customers.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800">Clear</a>
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
                        <tr class="hover:bg-slate-50/80">
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
                                {{ $c->gstin ?: 'UNREGISTERED' }}
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
                            <td colspan="6" class="py-8 text-center text-slate-400">No customers found.</td>
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
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full p-6" @click.outside="createModal = false">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg text-slate-900">Add New Customer</h3>
                    <button @click="createModal = false" class="text-slate-400 hover:text-slate-700 text-xl">&times;</button>
                </div>

                <form action="{{ route('customers.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Contact Name *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Business Name</label>
                            <input type="text" name="company_name" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Phone</label>
                            <input type="text" name="phone" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">State *</label>
                            <input type="text" name="state" value="Bihar" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">GSTIN</label>
                            <input type="text" name="gstin" placeholder="10AAAAA0000A1Z5" class="w-full px-3 py-2 rounded-lg border border-slate-300 uppercase font-mono text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Billing Address</label>
                        <textarea name="billing_address" rows="2" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="createModal = false" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-xs font-semibold">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold">Save Customer</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
