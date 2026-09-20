<x-app-layout header="Team & Staff Permissions">
    <div class="space-y-6" x-data="{ 
        showAddModal: false, 
        showEditModal: false,
        activeStaff: { id: null, name: '', phone: '', permissions: [] },
        openEdit(staff) {
            this.activeStaff = {
                id: staff.id,
                name: staff.name,
                phone: staff.phone || '',
                permissions: Array.isArray(staff.permissions) ? staff.permissions : []
            };
            this.showEditModal = true;
        }
    }">
        
        <!-- Header Banner -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $company->name }}</span>
                </div>
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Team Members & Role-Based Access</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Create operators, accountants, or support staff strictly for your company. Assign only the specific modules they need; unassigned features & sidebar links will be automatically hidden.
                </p>
            </div>

            <button type="button" @click="showAddModal = true" 
                    class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 active:scale-[0.99] transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                <span>+ Add Staff Member</span>
            </button>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
                @foreach($errors->all() as $err)
                    <div>⚠️ {{ $err }}</div>
                @endforeach
            </div>
        @endif

        <!-- Staff List Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-sm text-slate-900">Active Staff Accounts ({{ $staffMembers->count() }})</h3>
                <span class="text-xs text-slate-400">Strictly Isolated to Your Company ({{ $company->slug }})</span>
            </div>

            @if($staffMembers->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-3xl mx-auto mb-3">
                        👥
                    </div>
                    <h4 class="text-sm font-bold text-slate-900">No Staff Members Yet</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-4">
                        Add billing operators, sales representatives, or accountants and control which sidebar modules they can view.
                    </p>
                    <button type="button" @click="showAddModal = true" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition-all">
                        + Add First Staff Member
                    </button>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="p-4">Staff Member</th>
                                <th class="p-4">Role</th>
                                <th class="p-4">Granted Module Access</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($staffMembers as $member)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="p-4">
                                    <div class="font-bold text-slate-900 text-sm">{{ $member->name }}</div>
                                    <div class="text-slate-500 text-xs">{{ $member->email }}</div>
                                    @if($member->phone)<div class="text-[11px] text-slate-400">{{ $member->phone }}</div>@endif
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $member->role }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-1.5 max-w-md">
                                        @php $perms = $member->permissions ?? []; @endphp
                                        @if(in_array('invoices', $perms))
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">📄 Invoices</span>
                                        @endif
                                        @if(in_array('customers', $perms))
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">👤 Clients</span>
                                        @endif
                                        @if(in_array('products', $perms))
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">📦 Products</span>
                                        @endif
                                        @if(in_array('payments', $perms))
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">💳 Payments</span>
                                        @endif
                                        @if(in_array('reports', $perms))
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">📊 GSTR-1</span>
                                        @endif
                                        @if(in_array('settings', $perms))
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300">⚙️ Settings</span>
                                        @endif
                                        @if(empty($perms))
                                            <span class="text-slate-400 italic text-[11px]">No modules assigned (Dashboard only)</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    @if($member->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Active</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Deactivated</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" @click="openEdit({{ json_encode($member) }})"
                                                class="px-2.5 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-bold transition-all">
                                            ✏️ Edit Access
                                        </button>
                                        <form action="{{ route('team.toggle_status', $member->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg border text-xs font-bold transition-all {{ $member->is_active ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}">
                                                {{ $member->is_active ? 'Suspend' : 'Activate' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('team.destroy', $member->id) }}" method="POST" class="inline" onsubmit="return confirm('Permanently remove this staff member?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition-all">
                                                ✕
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- MODAL 1: ADD NEW STAFF MEMBER -->
        <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in-95 duration-200">
                <button type="button" @click="showAddModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 font-bold text-lg">✕</button>

                <div class="mb-4">
                    <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                        {{ $company->name }}
                    </span>
                    <h4 class="text-xl font-black text-slate-900 mt-1">Add New Staff Member</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Assign login credentials and select which modules this user can access.</p>
                </div>

                <form action="{{ route('team.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Full Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Ankit Verma"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Email (Login) *</label>
                            <input type="email" name="email" required placeholder="ankit@company.com"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Password *</label>
                            <input type="password" name="password" required placeholder="••••••••"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Phone Number (Optional)</label>
                        <input type="text" name="phone" placeholder="+91 98765 00000"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- Permissions Selection -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Granted Modules (Sidebar Access)</label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="invoices" checked class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                                <div><strong class="text-slate-900 block font-bold">📄 Invoices</strong><span class="text-[10px] text-slate-500">Create & manage bills</span></div>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="customers" checked class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                                <div><strong class="text-slate-900 block font-bold">👤 Clients</strong><span class="text-[10px] text-slate-500">Add & view customers</span></div>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="products" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                                <div><strong class="text-slate-900 block font-bold">📦 Products</strong><span class="text-[10px] text-slate-500">Catalog & pricing</span></div>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="payments" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                                <div><strong class="text-slate-900 block font-bold">💳 Payments</strong><span class="text-[10px] text-slate-500">Record settlements</span></div>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="reports" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                                <div><strong class="text-slate-900 block font-bold">📊 GSTR-1</strong><span class="text-[10px] text-slate-500">Tax analytics & exports</span></div>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="settings" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                                <div><strong class="text-slate-900 block font-bold">⚙️ Settings</strong><span class="text-[10px] text-slate-500">Gateways & preferences</span></div>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all">
                            Save Staff Member
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: EDIT STAFF PERMISSIONS -->
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in-95 duration-200">
                <button type="button" @click="showEditModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 font-bold text-lg">✕</button>

                <div class="mb-4">
                    <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                        Edit Access
                    </span>
                    <h4 class="text-xl font-black text-slate-900 mt-1" x-text="'Permissions for ' + activeStaff.name"></h4>
                    <p class="text-xs text-slate-500 mt-0.5">Toggle the modules this staff member can access in the sidebar.</p>
                </div>

                <form :action="'{{ url('/team') }}/' + activeStaff.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Full Name *</label>
                        <input type="text" name="name" x-model="activeStaff.name" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Phone Number</label>
                            <input type="text" name="phone" x-model="activeStaff.phone" placeholder="+91 98765 00000"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Reset Password (Optional)</label>
                            <input type="password" name="password" placeholder="Leave blank to keep"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Permissions Selection -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Granted Modules</label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="invoices" :checked="activeStaff.permissions.includes('invoices')" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                                <div><strong class="text-slate-900 block font-bold">📄 Invoices</strong><span class="text-[10px] text-slate-500">Create & manage bills</span></div>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="customers" :checked="activeStaff.permissions.includes('customers')" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                                <div><strong class="text-slate-900 block font-bold">👤 Clients</strong><span class="text-[10px] text-slate-500">Add & view customers</span></div>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="products" :checked="activeStaff.permissions.includes('products')" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                                <div><strong class="text-slate-900 block font-bold">📦 Products</strong><span class="text-[10px] text-slate-500">Catalog & pricing</span></div>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="payments" :checked="activeStaff.permissions.includes('payments')" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                                <div><strong class="text-slate-900 block font-bold">💳 Payments</strong><span class="text-[10px] text-slate-500">Record settlements</span></div>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="reports" :checked="activeStaff.permissions.includes('reports')" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                                <div><strong class="text-slate-900 block font-bold">📊 GSTR-1</strong><span class="text-[10px] text-slate-500">Tax analytics & exports</span></div>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="settings" :checked="activeStaff.permissions.includes('settings')" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                                <div><strong class="text-slate-900 block font-bold">⚙️ Settings</strong><span class="text-[10px] text-slate-500">Gateways & preferences</span></div>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>