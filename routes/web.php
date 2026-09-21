<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TaxReportController;
use App\Http\Controllers\PublicInvoiceController;
use App\Http\Controllers\StaffController;

/*
|--------------------------------------------------------------------------
| Web Routes - GST-SaaS Enterprise Multi-Tenant ERP
|--------------------------------------------------------------------------
*/

// Public Client Invoice Portal (Unauthenticated, UUID-guarded)
Route::get('/view/{uuid}', [PublicInvoiceController::class, 'show'])->name('public.invoice.show');
Route::post('/view/{uuid}/razorpay-callback', [PublicInvoiceController::class, 'razorpayCallback'])
    ->name('public.invoice.razorpay')
    ->middleware('throttle:10,1');

// Public / Guest Auth Routes with Security Rate Limiting
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register/send-otp', [AuthController::class, 'sendRegistrationOtp'])
        ->name('register.send_otp')
        ->middleware('throttle:3,1');
    Route::post('/register', [AuthController::class, 'registerCompany'])->middleware('throttle:5,1');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
        ->name('password.email')
        ->middleware('throttle:3,1');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('password.update')
        ->middleware('throttle:5,1');
});

// Authenticated & Multi-Tenant Routes
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Holding page for unverified/pending company onboarding
    Route::get('/pending-approval', function () {
        return view('auth.pending-approval');
    })->name('company.pending');

    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Invoices Management (RBAC: invoices)
    Route::middleware(['permission:invoices'])->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{id}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/invoices/{id}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::post('/invoices/{id}/restore', [InvoiceController::class, 'restore'])->name('invoices.restore');
        Route::delete('/invoices/{id}/force', [InvoiceController::class, 'forceDelete'])->name('invoices.force_delete');
        Route::get('/invoices/{id}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::post('/invoices/{id}/convert-tax', [InvoiceController::class, 'convertToTaxInvoice'])->name('invoices.convert-tax');
    });

    // Payment Operations (RBAC: payments)
    Route::middleware(['permission:payments'])->group(function () {
        Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'recordPayment'])->name('invoices.payments.store');
        Route::post('/invoices/{id}/mark-as-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');
        Route::delete('/payments/{transaction}', [PaymentController::class, 'destroy'])->name('payments.destroy');
        Route::get('/payments/{transaction}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    });

    // GSTR-1 Tax Reports & CA Export (RBAC: reports)
    Route::middleware(['permission:reports'])->group(function () {
        Route::get('/reports/gstr1', [TaxReportController::class, 'gstr1'])->name('reports.gstr1');
        Route::get('/reports/gstr1/export-csv', [TaxReportController::class, 'exportCsv'])->name('reports.gstr1.export');
    });

    // Customers (RBAC: customers)
    Route::middleware(['permission:customers'])->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // Products & Services Master (RBAC: products)
    Route::middleware(['permission:products'])->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });
    // Search products for invoice builder (Accessible with invoices or products permission)
    Route::get('/api/products/search', [ProductController::class, 'apiSearch'])->name('products.search');

    // Company Settings & Dedicated Integrations (RBAC: settings)
    Route::middleware(['permission:settings'])->group(function () {
        Route::get('/settings', [CompanySettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [CompanySettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-mail', [CompanySettingsController::class, 'sendTestMail'])->name('settings.test_mail');
    });

    // User Profile & Security (Self-Service)
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/info', [ProfileController::class, 'updateInfo'])->name('profile.info');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/request-email-change', [ProfileController::class, 'requestEmailChange'])->name('profile.request_email_change');
    Route::post('/profile/signature', [ProfileController::class, 'updateSignature'])->name('profile.signature');

    // Tenant Team & Staff Access Control (RBAC: team / company admin)
    Route::middleware(['permission:team'])->group(function () {
        Route::get('/team', [StaffController::class, 'index'])->name('team.index');
        Route::post('/team', [StaffController::class, 'store'])->name('team.store');
        Route::put('/team/{id}', [StaffController::class, 'update'])->name('team.update');
        Route::post('/team/{id}/toggle-status', [StaffController::class, 'toggleStatus'])->name('team.toggle_status');
        Route::delete('/team/{id}', [StaffController::class, 'destroy'])->name('team.destroy');
    });

    // Super Admin Master Control Panel (Dedicated super_admin role required)
    Route::middleware(['role:super_admin'])->prefix('super-admin')->group(function () {
        Route::get('/', [SuperAdminController::class, 'index'])->name('superadmin.index');
        Route::post('/companies/{id}/toggle-status', [SuperAdminController::class, 'toggleStatus'])->name('superadmin.toggle_status');
        Route::post('/companies/{id}/approve', [SuperAdminController::class, 'approveCompany'])->name('superadmin.approve_company');
        Route::post('/companies/{id}/reject', [SuperAdminController::class, 'rejectCompany'])->name('superadmin.reject_company');
        Route::post('/email-requests/{id}/approve', [SuperAdminController::class, 'approveEmailChange'])->name('superadmin.approve_email_change');
        Route::post('/email-requests/{id}/reject', [SuperAdminController::class, 'rejectEmailChange'])->name('superadmin.reject_email_change');
        Route::post('/onboarding-policy/toggle', [SuperAdminController::class, 'toggleOnboardingPolicy'])->name('superadmin.toggle_onboarding_policy');
        Route::post('/companies/{id}/impersonate', [SuperAdminController::class, 'impersonate'])->name('superadmin.impersonate');
    });
    Route::post('/super-admin/stop-impersonate', [SuperAdminController::class, 'stopImpersonate'])->name('superadmin.stop_impersonate');
});