<x-app-layout header="My Profile & Security Settings">
    <div class="max-w-4xl mx-auto space-y-6" x-data="{ showEmailModal: false }">
        
        <div>
            <h2 class="text-xl font-bold text-slate-900">User Profile & Account Security</h2>
            <p class="text-sm text-slate-500">Manage your personal credentials, contact details, account password, and invoice signature.</p>
        </div>

        @if($pendingEmailRequest)
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center text-amber-700 flex-shrink-0 text-base">
                    ⏳
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-amber-900">Registered Email Change Under Review</h4>
                    <p class="text-xs text-amber-800 mt-0.5">
                        A request to change your registered email to <strong class="font-bold">{{ $pendingEmailRequest->requested_email }}</strong> was submitted on {{ $pendingEmailRequest->created_at->format('d M Y, h:i A') }}. Super Admin verification is currently pending.
                    </p>
                    <div class="mt-2 text-[11px] text-amber-700 bg-amber-100/60 px-2.5 py-1 rounded-lg inline-block font-mono">
                        Reason: {{ Str::limit($pendingEmailRequest->reason, 80) }}
                    </div>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold">
                ⚠️ {{ session('warning') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
                @foreach($errors->all() as $err)
                    <div>⚠️ {{ $err }}</div>
                @endforeach
            </div>
        @endif

        <!-- 1. PERSONAL INFORMATION (NAME, EMAIL, PHONE) -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900">Personal Information & Login Email</h3>
                    <p class="text-xs text-slate-500">Update your name, contact phone, and review your registered billing email.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ str_replace('_', ' ', $user->role) }}
                </span>
            </div>

            <form action="{{ route('profile.info') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    @if($user->company_id)
                        <!-- Tenant User: Email is Locked for Security -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Official Company Email</label>
                                <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 flex items-center gap-1">
                                    🔒 Locked (Anti-Takeover)
                                </span>
                            </div>
                            <div class="relative">
                                <input type="email" value="{{ $user->email }}" readonly
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 text-sm cursor-not-allowed font-medium">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                    🔒
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-1">
                                <p class="text-[11px] text-slate-500">Registered company billing email.</p>
                                @if(!$pendingEmailRequest)
                                    <button type="button" @click="showEmailModal = true" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline">
                                        Request Change &rarr;
                                    </button>
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- Super Admin: Direct Editable Email -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Login Email Address *</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+91 98765 43210"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    @if($company)
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Company Workspace</label>
                        <input type="text" value="{{ $company->name }} ({{ $company->state }})" readonly
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 text-sm cursor-not-allowed font-semibold">
                    </div>
                    @endif
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-md transition-all">
                        Save Personal Info
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. INVOICE SIGNATURE & DIGITAL SIGN-OFF (FOR TENANT COMPANIES) -->
        @if($company)
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900">Official Invoice Signature & Stamp</h3>
                    <p class="text-xs text-slate-500">Upload a scanned signature image OR write a digital sign-off text. If left blank, invoices will not print any signature placeholder.</p>
                </div>
                <div>
                    @if($company->signature_path)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                            ✓ Image Uploaded
                        </span>
                    @elseif($company->digital_signature_text)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-teal-100 text-teal-800 border border-teal-200">
                            ✓ Digital Text Active
                        </span>
                    @else
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                            No Signature (Hidden on Invoice)
                        </span>
                    @endif
                </div>
            </div>

            <!-- Current Signature Preview Card -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-2xl shadow-sm">
                        ✍️
                    </div>
                    <div>
                        <strong class="text-xs text-slate-900 block font-bold">Current Print Preview:</strong>
                        @if($company->signature_path)
                            <div class="mt-1">
                                <img src="{{ asset('storage/' . $company->signature_path) }}" alt="Current Signature" class="h-12 max-w-[160px] object-contain border border-slate-200 bg-white p-1 rounded-lg">
                            </div>
                        @elseif($company->digital_signature_text)
                            <div class="mt-1 font-serif text-lg text-slate-900 italic font-bold" style="font-family: 'Brush Script MT', 'Dancing Script', cursive, serif;">
                                {{ $company->digital_signature_text }}
                            </div>
                            <span class="text-[10px] text-slate-500 font-mono">✓ Digital Verified Signatory</span>
                        @else
                            <p class="text-xs text-slate-500 italic mt-0.5">
                                No signature configured. Invoices will cleanly display "Computer generated invoice no signature required."
                            </p>
                        @endif
                    </div>
                </div>

                @if($company->signature_path || $company->digital_signature_text)
                <form action="{{ route('profile.signature') }}" method="POST" onsubmit="return confirm('Remove current signature from all future invoice prints?');">
                    @csrf
                    <input type="hidden" name="remove_signature" value="1">
                    <button type="submit" class="px-3.5 py-1.5 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-bold transition-all">
                        🗑️ Remove Signature
                    </button>
                </form>
                @endif
            </div>

            <form action="{{ route('profile.signature') }}" method="POST" enctype="multipart/form-data" class="space-y-4 pt-2">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Option A: Upload Picture -->
                    <div class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Option A: Upload Signature Image
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">Upload a transparent PNG or clear JPEG photo of your authorized signature / official seal stamp (Max 2MB).</p>
                        <input type="file" name="signature_file" accept="image/png,image/jpeg,image/jpg,image/svg+xml"
                               class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-800 hover:file:bg-slate-200">
                    </div>

                    <!-- Option B: Digital Signature Text -->
                    <div class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 bg-white">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Option B: Type Digital Signature Text
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">If you do not have an image, type the signatory name & designation. It prints in a beautiful digital script.</p>
                        <input type="text" name="digital_signature_text" value="{{ old('digital_signature_text', $company->digital_signature_text) }}" placeholder="e.g. Deepak Pandey, Managing Director"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <p class="text-[11px] text-slate-500">
                        💡 <strong>Rule:</strong> Agar aap signature upload ya enter nahi karenge to invoice par koi empty box nahi dikhega.
                    </p>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all">
                        Update Signature Details
                    </button>
                </div>
            </form>
        </div>
        @endif

        <!-- 3. SECURITY & PASSWORD MANAGEMENT -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
            <div class="pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900">Change Account Password</h3>
                <p class="text-xs text-slate-500">Ensure your account is using a long, random password to stay secure.</p>
            </div>

            <form action="{{ route('profile.password') }}" method="POST" class="space-y-4 max-w-md">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Current Password *</label>
                    <input type="password" name="current_password" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">New Password *</label>
                    <input type="password" name="password" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Confirm New Password *</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="pt-2">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-md transition-all">
                        Update Password
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- REQUEST EMAIL CHANGE MODAL -->
    @if($company)
    <div x-show="showEmailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in-95 duration-200">
            <button type="button" @click="showEmailModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 font-bold text-lg">✕</button>

            <div class="mb-4">
                <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                    Security Verification Gate
                </span>
                <h4 class="text-xl font-black text-slate-900 mt-1">Request Email Change</h4>
                <p class="text-xs text-slate-500 mt-1">
                    To prevent malicious workspace takeover, changes to the registered company billing email must be approved by the Super Admin.
                </p>
            </div>

            <form action="{{ route('profile.request_email_change') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Current Registered Email</label>
                    <input type="email" value="{{ $company->email }}" readonly
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 text-xs font-mono cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">New Desired Email *</label>
                    <input type="email" name="requested_email" required placeholder="new-owner@company.com"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Business Reason for Change *</label>
                    <textarea name="reason" rows="3" required placeholder="e.g. Migration to our new company domain or ownership transition..."
                              class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="showEmailModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-md transition-all">
                        Submit for Verification
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</x-app-layout>