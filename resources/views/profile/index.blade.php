<x-app-layout header="My Profile & Security Settings">
    <div class="max-w-4xl mx-auto space-y-6">
        
        <div>
            <h2 class="text-xl font-bold text-slate-900">User Profile & Account Security</h2>
            <p class="text-sm text-slate-500">Manage your personal credentials, contact details, and account password.</p>
        </div>

        <!-- 1. PERSONAL INFORMATION (NAME, EMAIL, PHONE) -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900">Personal Information & Login Email</h3>
                    <p class="text-xs text-slate-500">Update your name and the email address used to sign in to your company portal.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-brand-50 text-brand-700 border border-brand-200">
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
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Login Email Address *</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+91..."
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Assigned Tenant Company</label>
                        <input type="text" value="{{ $company->name ?? 'Global Platform' }}" disabled
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-sm cursor-not-allowed">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-sm transition-all">
                        Update Profile Information
                    </button>
                </div>
            </form>
        </div>
        <!-- 2. CHANGE PASSWORD (CURRENT PASSWORD VERIFICATION) -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4" x-data="{ showCurr: false, showNew: false }">
            <div class="pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 flex items-center gap-2">
                    <span>Change Account Password</span>
                    <span class="text-xs px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">Security Critical</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Ensure your account is using a strong password. Current password verification is mandatory.</p>
            </div>

            <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Current Password *</label>
                    <div class="relative max-w-md">
                        <input :type="showCurr ? 'text' : 'password'" name="current_password" required placeholder="Enter existing password"
                               class="w-full px-3.5 py-2.5 pr-10 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        <button type="button" @click="showCurr = !showCurr" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            <span x-show="!showCurr">👁️</span>
                            <span x-show="showCurr" x-cloak>🙈</span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">New Password (Min 8 Chars) *</label>
                        <div class="relative">
                            <input :type="showNew ? 'text' : 'password'" name="password" required placeholder="Min 8 characters"
                                   class="w-full px-3.5 py-2.5 pr-10 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            <button type="button" @click="showNew = !showNew" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <span x-show="!showNew">👁️</span>
                                <span x-show="showNew" x-cloak>🙈</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Confirm New Password *</label>
                        <input :type="showNew ? 'text' : 'password'" name="password_confirmation" required placeholder="Repeat new password"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm transition-all">
                        Update Password
                    </button>
                </div>
            </form>
        </div>

        <!-- 3. SESSION & SECURITY AUDIT OVERVIEW -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h4 class="font-bold text-sm text-slate-900">Security Audit Trail</h4>
                <p class="text-xs text-slate-500 mt-0.5">All password changes and profile updates are logged immutably in company activity history.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="text-xs font-bold text-brand-600 hover:text-brand-800">
                View Activity Logs on Dashboard &rarr;
            </a>
        </div>

    </div>
</x-app-layout>
