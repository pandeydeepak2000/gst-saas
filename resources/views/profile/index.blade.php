<x-app-layout header=My Profile & Security Settings>
    <div class=max-w-4xl mx-auto space-y-6 x-data={ showEmailModal: false }>
        
        <div>
            <h2 class=text-xl font-bold text-slate-900>User Profile & Account Security</h2>
            <p class=text-sm text-slate-500>Manage your personal credentials, contact details, and account password.</p>
        </div>

        @if()
            <div class=p-4 rounded-2xl bg-amber-50 border border-amber-200 flex items-start gap-3>
                <div class=w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center text-amber-700 flex-shrink-0 text-base>
                    ⏳
                </div>
                <div class=flex-1>
                    <h4 class=text-sm font-bold text-amber-900>Registered Email Change Under Review</h4>
                    <p class=text-xs text-amber-800 mt-0.5>
                        A request to change your registered email to <strong class=font-bold>{{ ->requested_email }}</strong> was submitted on {{ ->created_at->format('d M Y, h:i A') }}. Super Admin verification is currently pending.
                    </p>
                    <div class=mt-2 text-[11px] text-amber-700 bg-amber-100/60 px-2.5 py-1 rounded-lg inline-block font-mono>
                        Reason: {{ Str::limit(->reason, 80) }}
                    </div>
                </div>
            </div>
        @endif

        <!-- 1. PERSONAL INFORMATION (NAME, EMAIL, PHONE) -->
        <div class=p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4>
            <div class=flex items-center justify-between pb-3 border-b border-slate-100>
                <div>
                    <h3 class=font-bold text-slate-900>Personal Information & Login Email</h3>
                    <p class=text-xs text-slate-500>Update your name, contact phone, and review your registered billing email.</p>
                </div>
                <span class=px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-brand-50 text-brand-700 border border-brand-200>
                    {{ str_replace('_', ' ', ->role) }}
                </span>
            </div>

            <form action={{ route('profile.info') }} method=POST class=space-y-4>
                @csrf
                @method('PUT')

                <div class=grid grid-cols-1 sm:grid-cols-2 gap-4>
                    <div>
                        <label class=block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1>Full Name *</label>
                        <input type=text name=name value={{ old('name', ->name) }} required
                               class=w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none>
                    </div>

                    @if(->company_id)
                        <!-- Tenant User: Email is Locked for Security -->
                        <div>
                            <div class=flex items-center justify-between mb-1>
                                <label class=block text-xs font-bold uppercase tracking-wider text-slate-700>Official Company Email</label>
                                <span class=text-[10px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 flex items-center gap-1>
                                    🔒 Locked (Anti-Takeover)
                                </span>
                            </div>
                            <div class=relative>
                                <input type=email value={{ ->email }} readonly
                                       class=w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 text-sm cursor-not-allowed font-medium>
                                <div class=absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400>
                                    🔒
                                </div>
                            </div>
                            <div class=flex items-center justify-between mt-1>
                                <p class=text-[11px] text-slate-500>Registered company billing email.</p>
                                @if(!)
                                    <button type=button @click=showEmailModal = true class=text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline>
                                        Request Change &rarr;
                                    </button>
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- Super Admin: Direct Editable Email -->
                        <div>
                            <label class=block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1>Login Email Address *</label>
                            <input type=email name=email value={{ old('email', ->email) }} required
                                   class=w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none>
                        </div>
                    @endif

                    <div>
                        <label class=block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1>Phone Number</label>
                        <input type=text name=phone value={{ old('phone', ->phone) }} placeholder=+91...
                               class=w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none>
                    </div>

                    <div>
                        <label class=block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1>Assigned Tenant Company</label>
                        <input type=text value={{ ->name ?? 'Global Platform' }} disabled
                               class=w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-sm cursor-not-allowed>
                    </div>
                </div>

                <div class=flex justify-end pt-2>
                    <button type=submit class=px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-sm transition-all>
                        Update Profile Information
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. CHANGE PASSWORD (CURRENT PASSWORD VERIFICATION) -->
        <div class=p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4 x-data={ showCurr: false, showNew: false }>
            <div class=pb-3 border-b border-slate-100>
                <h3 class=font-bold text-slate-900 flex items-center gap-2>
                    <span>Change Account Password</span>
                    <span class=text-xs px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-semibold>Security Critical</span>
                </h3>
                <p class=text-xs text-slate-500 mt-0.5>Ensure your account is using a strong password. Current password verification is mandatory.</p>
            </div>

            <form action={{ route('profile.password') }} method=POST class=space-y-4>
                @csrf
                @method('PUT')

                <div>
                    <label class=block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1>Current Password *</label>
                    <div class=relative max-w-md>
                        <input :type=showCurr ? 'text' : 'password' name=current_password required placeholder=Enter existing password
                               class=w-full px-3.5 py-2.5 pr-10 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none>
                        <button type=button @click=showCurr = !showCurr class=absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600>
                            <span x-show=!showCurr>👁️</span>
                            <span x-show=showCurr x-cloak>🙈</span>
                        </button>
                    </div>
                </div>

                <div class=grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl>
                    <div>
                        <label class=block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1>New Password (Min 8 Chars) *</label>
                        <div class=relative>
                            <input :type=showNew ? 'text' : 'password' name=password required placeholder=Min 8 characters
                                   class=w-full px-3.5 py-2.5 pr-10 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none>
                            <button type=button @click=showNew = !showNew class=absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600>
                                <span x-show=!showNew>👁️</span>
                                <span x-show=showNew x-cloak>🙈</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class=block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1>Confirm New Password *</label>
                        <input :type=showNew ? 'text' : 'password' name=password_confirmation required placeholder=Repeat new password
                               class=w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none>
                    </div>
                </div>

                <div class=flex justify-end pt-2>
                    <button type=submit class=px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm transition-all>
                        Update Password
                    </button>
                </div>
            </form>
        </div>

        <!-- 3. SESSION & SECURITY AUDIT OVERVIEW -->
        <div class=p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4>
            <div>
                <h4 class=font-bold text-sm text-slate-900>Security Audit Trail</h4>
                <p class=text-xs text-slate-500 mt-0.5>All password changes and profile updates are logged immutably in company activity history.</p>
            </div>
            <a href={{ route('dashboard') }} class=text-xs font-bold text-brand-600 hover:text-brand-800>
                View Activity Logs on Dashboard &rarr;
            </a>
        </div>

        <!-- MODAL: REQUEST REGISTERED EMAIL CHANGE -->
        @if(->company_id)
            <div x-show=showEmailModal x-cloak class=fixed inset-0 z-50 overflow-y-auto aria-labelledby=modal-title role=dialog aria-modal=true>
                <div class=flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0>
                    <div x-show=showEmailModal x-transition.opacity class=fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity @click=showEmailModal = false></div>
                    <span class=hidden sm:inline-block sm:align-middle sm:h-screen aria-hidden=true>&#8203;</span>
                    
                    <div x-show=showEmailModal x-transition.scale class=relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200>
                        <div class=bg-gradient-to-r from-slate-900 to-indigo-950 p-6 text-white>
                            <div class=flex items-center justify-between>
                                <div class=flex items-center gap-3>
                                    <div class=w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center text-xl>
                                        🛡️
                                    </div>
                                    <div>
                                        <h3 class=text-lg font-black tracking-tight text-white>Request Registered Email Change</h3>
                                        <p class=text-xs text-indigo-200 mt-0.5>Super Admin Verification Workflow</p>
                                    </div>
                                </div>
                                <button type=button @click=showEmailModal = false class=text-white/60 hover:text-white text-lg>✕</button>
                            </div>
                        </div>

                        <form action={{ route('profile.request_email_change') }} method=POST class=p-6 space-y-4>
                            @csrf
                            <div class=p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-xs text-blue-900>
                                <strong class=font-bold>Security Notice:</strong> To safeguard your invoices, GST records, and sensitive tax information, company email changes require formal administrative review before taking effect.
                            </div>

                            <div>
                                <label class=block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1>Current Email</label>
                                <input type=email value={{ ->email }} disabled class=w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-sm>
                            </div>

                            <div>
                                <label class=block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1>New Requested Email *</label>
                                <input type=email name=requested_email required placeholder=e.g., admin@newdomain.com
                                       class=w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none>
                            </div>

                            <div>
                                <label class=block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1>Reason for Email Change *</label>
                                <textarea name=reason rows=3 required placeholder=Provide justification (e.g. Domain rebranded, corporate email migration, official restructuring)
                                          class=w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none></textarea>
                                <p class=text-[11px] text-slate-400 mt-1>Super Admin will review this note during verification.</p>
                            </div>

                            <div class=flex items-center justify-end gap-3 pt-2>
                                <button type=button @click=showEmailModal = false class=px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50>
                                    Cancel
                                </button>
                                <button type=submit class=px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition-all>
                                    Submit Request to Super Admin
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-app-layout>