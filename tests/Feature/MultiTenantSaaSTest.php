<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\InvoiceTransaction;
use App\Models\EmailOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MultiTenantSaaSTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_tenant_data_isolation_between_companies(): void
    {
        $acmeAdmin = User::where('email', 'admin@acme.com')->first();
        $bharatAdmin = User::where('email', 'admin@bharat.com')->first();

        // 1. Authenticate as Acme Admin
        $this->actingAs($acmeAdmin);

        $response = $this->get('/invoices');
        $response->assertStatus(200);
        $response->assertSee('ACME/26/1001');
        $response->assertDontSee('BTH-501'); // Bharat invoice must not leak!

        // 2. Authenticate as Bharat Admin
        $this->actingAs($bharatAdmin);

        $response2 = $this->get('/invoices');
        $response2->assertStatus(200);
        $response2->assertSee('BTH-501');
        $response2->assertDontSee('ACME/26/1001'); // Acme invoice must not leak!
    }

    public function test_custom_invoice_number_manual_editing_freedom(): void
    {
        $acmeAdmin = User::where('email', 'admin@acme.com')->first();
        $this->actingAs($acmeAdmin);

        $customer = Customer::first();
        $customNumber = 'SPECIAL-INV-CUSTOM-789';

        $payload = [
            'customer_id'    => $customer->id,
            'invoice_number' => $customNumber, // Custom manual number freely typed!
            'invoice_date'   => now()->format('Y-m-d'),
            'due_date'       => now()->addDays(7)->format('Y-m-d'),
            'sale_type'      => 'LOCAL',
            'tax_mode'       => 'simple',
            'status'         => 'unpaid',
            'items' => [
                [
                    'description' => 'Custom ERP Architecture Module',
                    'hsn_sac'     => '998313',
                    'quantity'    => 2,
                    'unit'        => 'Job',
                    'rate'        => 5000,
                    'gst_percent' => 18,
                ]
            ]
        ];

        $response = $this->post('/invoices', $payload);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('invoices', [
            'company_id'     => $acmeAdmin->company_id,
            'invoice_number' => $customNumber,
            'taxable_amount' => 10000.00,
            'total_amount'   => 11800.00,
        ]);
    }

    public function test_invoice_soft_delete_and_trash_restore(): void
    {
        $acmeAdmin = User::where('email', 'admin@acme.com')->first();
        $this->actingAs($acmeAdmin);

        $invoice = Invoice::where('invoice_number', 'ACME/26/1001')->first();

        // Delete invoice (Move to Trash)
        $response = $this->delete("/invoices/{$invoice->id}");
        $response->assertRedirect();

        // Check soft-deleted
        $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);

        // Restore invoice
        $responseRestore = $this->post("/invoices/{$invoice->id}/restore");
        $responseRestore->assertRedirect();

        // Check active again
        $this->assertNotSoftDeleted('invoices', ['id' => $invoice->id]);
    }

    public function test_company_tax_mode_toggle_settings(): void
    {
        $acmeAdmin = User::where('email', 'admin@acme.com')->first();
        $this->actingAs($acmeAdmin);

        $company = $acmeAdmin->company;

        $response = $this->post('/settings', [
            'name'                        => $company->name,
            'state'                       => $company->state,
            'tax_mode'                    => 'simple',
            'invoice_prefix'              => 'ACME/CUSTOM/',
            'invoice_start_number'        => 2000,
            'allow_manual_invoice_number' => 1,
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('companies', [
            'id'             => $company->id,
            'tax_mode'       => 'simple',
            'invoice_prefix' => 'ACME/CUSTOM/',
        ]);
    }
    public function test_product_catalog_tenant_isolation_and_creation(): void
    {
        $acmeAdmin = User::where('email', 'admin@acme.com')->first();
        $bharatAdmin = User::where('email', 'admin@bharat.com')->first();

        // 1. Acme Admin verifies Acme products only
        $this->actingAs($acmeAdmin);
        $responseAcme = $this->get('/products');
        $responseAcme->assertStatus(200);
        $responseAcme->assertSee('Cloud ERP Architecture Consulting');
        $responseAcme->assertDontSee('Heavy Brass Gate Valve 25mm'); // Bharat product must not leak!

        // 2. Bharat Admin verifies Bharat products only
        $this->actingAs($bharatAdmin);
        $responseBharat = $this->get('/products');
        $responseBharat->assertStatus(200);
        $responseBharat->assertSee('Heavy Brass Gate Valve 25mm');
        $responseBharat->assertDontSee('Cloud ERP Architecture Consulting'); // Acme product must not leak!

        // 3. Create new product
        $createResponse = $this->post('/products', [
            'name'        => 'Stainless Steel Kitchen Sink',
            'hsn_sac'     => '7324',
            'unit'        => 'Pcs',
            'rate'        => 3200.00,
            'gst_percent' => 18.00,
        ]);
        $createResponse->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'company_id' => $bharatAdmin->company_id,
            'name'       => 'Stainless Steel Kitchen Sink',
            'rate'       => 3200.00,
        ]);
    }
    public function test_user_can_update_profile_info_and_email(): void
    {
        $acmeAdmin = User::where('email', 'admin@acme.com')->first();
        $this->actingAs($acmeAdmin);

        // 1. Direct update updates name & phone, but official registered email remains locked for security
        $response = $this->put('/profile/info', [
            'name'  => 'Rajesh Kumar Updated',
            'phone' => '+919988770000',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'    => $acmeAdmin->id,
            'name'  => 'Rajesh Kumar Updated',
            'email' => 'admin@acme.com', // Kept locked against direct alteration
            'phone' => '+919988770000',
        ]);

        // 2. Submit formal email change request with justification
        $requestResponse = $this->post('/profile/request-email-change', [
            'requested_email' => 'rajesh.updated@acme.com',
            'reason'          => 'Official domain rebranded to acme.in',
        ]);
        $requestResponse->assertSessionHas('success');

        $this->assertDatabaseHas('email_change_requests', [
            'company_id'      => $acmeAdmin->company_id,
            'current_email'   => 'admin@acme.com',
            'requested_email' => 'rajesh.updated@acme.com',
            'status'          => 'pending',
        ]);

        // 3. Super Admin approves the email change request
        $superAdmin = User::where('role', 'super_admin')->first();
        $emailReq = \App\Models\EmailChangeRequest::where('company_id', $acmeAdmin->company_id)->latest('id')->first();

        $this->actingAs($superAdmin);
        $approveResponse = $this->post("/super-admin/email-requests/{$emailReq->id}/approve");
        $approveResponse->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'    => $acmeAdmin->id,
            'email' => 'rajesh.updated@acme.com',
        ]);
    }

    public function test_user_can_change_password_with_current_password_verification(): void
    {
        $acmeAdmin = User::where('email', 'admin@acme.com')->first();
        $this->actingAs($acmeAdmin);

        // 1. Wrong current password should fail
        $failResponse = $this->put('/profile/password', [
            'current_password'      => 'wrong-current-password',
            'password'              => 'new-secret-password-123',
            'password_confirmation' => 'new-secret-password-123',
        ]);
        $failResponse->assertSessionHasErrors('current_password');

        // 2. Correct current password should succeed
        $successResponse = $this->put('/profile/password', [
            'current_password'      => 'password',
            'password'              => 'new-secret-password-123',
            'password_confirmation' => 'new-secret-password-123',
        ]);
        $successResponse->assertSessionHas('success');

        // 3. Verify user can now authenticate with new password
        $this->post('/logout');
        $loginResponse = $this->post('/login', [
            'email'    => 'admin@acme.com',
            'password' => 'new-secret-password-123',
        ]);
        $loginResponse->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($acmeAdmin);
    }

    public function test_forgot_password_and_token_reset_flow(): void
    {
        // 1. Request reset link
        $emailResponse = $this->post('/forgot-password', [
            'email' => 'admin@bharat.com',
        ]);
        $emailResponse->assertSessionHas('status');

        $resetToken = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', 'admin@bharat.com')
            ->value('token');
        $this->assertNotNull($resetToken);

        // 2. Simulate raw token reset
        $rawToken = 'demo-test-token-123456';
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => 'admin@bharat.com'],
            ['token' => \Illuminate\Support\Facades\Hash::make($rawToken), 'created_at' => now()]
        );

        $resetResponse = $this->post('/reset-password', [
            'token'                 => $rawToken,
            'email'                 => 'admin@bharat.com',
            'password'              => 'bharat-brand-new-pass-99',
            'password_confirmation' => 'bharat-brand-new-pass-99',
        ]);
        $resetResponse->assertRedirect('/login');
        $resetResponse->assertSessionHas('success');

        // 3. Authenticate with newly reset password
        $loginResponse = $this->post('/login', [
            'email'    => 'admin@bharat.com',
            'password' => 'bharat-brand-new-pass-99',
        ]);
        $loginResponse->assertRedirect('/dashboard');
    }
    public function test_super_admin_governance_and_access_control(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $acmeAdmin = User::where('email', 'admin@acme.com')->first();
        $company = $acmeAdmin->company;

        // 1. Regular company admin cannot access /super-admin (403)
        $this->actingAs($acmeAdmin);
        $forbiddenResponse = $this->get('/super-admin');
        $forbiddenResponse->assertStatus(403);

        // 2. Super admin can access /super-admin
        $this->actingAs($superAdmin);
        $saResponse = $this->get('/super-admin');
        $saResponse->assertStatus(200);
        $saResponse->assertSee('Platform Master Control');
        $saResponse->assertSee($company->name);

        // 3. Super admin can toggle company status (Suspend)
        $toggleResponse = $this->post("/super-admin/companies/{$company->id}/toggle-status");
        $toggleResponse->assertRedirect();
        $this->assertDatabaseHas('companies', [
            'id'        => $company->id,
            'is_active' => false,
        ]);

        // 4. Super admin can re-activate
        $this->post("/super-admin/companies/{$company->id}/toggle-status");
        $this->assertDatabaseHas('companies', [
            'id'        => $company->id,
            'is_active' => true,
        ]);

        // 5. Super admin can impersonate company admin
        $impResponse = $this->post("/super-admin/companies/{$company->id}/impersonate");
        $impResponse->assertRedirect('/dashboard');
        $this->assertEquals($acmeAdmin->id, auth()->id());
    }

    public function test_company_dedicated_smtp_configuration(): void
    {
        $acmeAdmin = User::where('email', 'admin@acme.com')->first();
        $this->actingAs($acmeAdmin);
        $company = $acmeAdmin->company;

        $response = $this->post('/settings', [
            'name'                        => $company->name,
            'state'                       => $company->state,
            'tax_mode'                    => 'detailed',
            'invoice_template'            => 'hosting_domain',
            'invoice_prefix'              => 'ACME/26/',
            'invoice_start_number'        => 1001,
            'mail_host'                   => 'smtp.sendgrid.net',
            'mail_port'                   => 587,
            'mail_username'               => 'apikey',
            'mail_password'               => 'SG.sample_key_12345',
            'mail_encryption'             => 'tls',
            'mail_from_address'           => 'invoices@acme.com',
            'mail_from_name'              => 'Acme Cloud Invoicing',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('companies', [
            'id'                => $company->id,
            'mail_host'         => 'smtp.sendgrid.net',
            'mail_from_address' => 'invoices@acme.com',
            'invoice_template'  => 'hosting_domain',
        ]);
    }

    public function test_hosting_and_domain_invoice_creation(): void
    {
        $acmeAdmin = User::where('email', 'admin@acme.com')->first();
        $this->actingAs($acmeAdmin);
        $customer = Customer::first();

        $payload = [
            'customer_id'    => $customer->id,
            'invoice_number' => 'CLOUD-HOST-2026-01',
            'invoice_date'   => '2026-09-20',
            'due_date'       => '2026-10-05',
            'sale_type'      => 'LOCAL',
            'tax_mode'       => 'detailed',
            'status'         => 'unpaid',
            'items' => [
                [
                    'description'          => 'cPanel NVMe Web Hosting & SSL',
                    'domain_name'          => 'greenstudio.jixsite.com',
                    'service_period_start' => '2026-09-20',
                    'service_period_end'   => '2027-09-19',
                    'billing_cycle'        => '1 Year',
                    'hsn_sac'              => '998315',
                    'quantity'             => 1,
                    'unit'                 => 'Year',
                    'rate'                 => 4500.00,
                    'gst_percent'          => 18.00,
                ]
            ]
        ];

        $response = $this->post('/invoices', $payload);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('invoice_items', [
            'domain_name'   => 'greenstudio.jixsite.com',
            'billing_cycle' => '1 Year',
            'rate'          => 4500.00,
        ]);
    }
    public function test_public_invoice_viewer_without_auth_and_upi_qr_payload(): void
    {
        $company = Company::first();
        $company->update([
            'upi_id' => 'acme@okhdfcbank',
            'upi_name' => 'Acme Infotech',
            'enable_upi_qr' => true,
        ]);

        $invoice = Invoice::withoutGlobalScopes()->where('company_id', $company->id)->first();
        $this->assertNotEmpty($invoice->public_uuid);

        // Guest visitor accesses public magic link
        $response = $this->get('/view/' . $invoice->public_uuid);
        $response->assertStatus(200);
        $response->assertSee($company->name);
        $response->assertSee($invoice->invoice_number);
        $response->assertSee('upi%3A%2F%2Fpay');
    }

    public function test_manual_payment_recording_and_invoice_balance_recalculation(): void
    {
        $admin = User::where('email', 'admin@acme.com')->first();
        $this->actingAs($admin);

        $customer = Customer::first();
        $invoice = Invoice::create([
            'company_id'     => $admin->company_id,
            'customer_id'    => $customer->id,
            'created_by'     => $admin->id,
            'invoice_number' => 'TEST-PAY-' . uniqid(),
            'type'           => 'tax_invoice',
            'invoice_date'   => '2026-09-20',
            'sale_type'      => 'LOCAL',
            'tax_mode'       => 'simple',
            'status'         => 'unpaid',
            'taxable_amount' => 8474.58,
            'total_amount'   => 10000.00,
            'paid_amount'    => 0.00,
            'balance_amount' => 10000.00,
        ]);

        // Record partial payment of 4000
        $response = $this->post("/invoices/{$invoice->id}/payments", [
            'amount' => 4000.00,
            'payment_method' => 'cash',
            'reference_no' => 'CASH-REC-001',
            'paid_at' => date('Y-m-d'),
            'notes' => 'Advance token payment',
        ]);

        $response->assertSessionHas('success');

        $invoice->refresh();
        $this->assertEquals(4000.00, $invoice->paid_amount);
        $this->assertEquals(6000.00, $invoice->balance_amount);
        $this->assertEquals('partially_paid', $invoice->status);

        // Record remaining balance of 6000
        $response2 = $this->post("/invoices/{$invoice->id}/payments", [
            'amount' => 6000.00,
            'payment_method' => 'upi',
            'reference_no' => 'UPI-UTR-99999',
            'paid_at' => date('Y-m-d'),
        ]);

        $response2->assertSessionHas('success');

        $invoice->refresh();
        $this->assertEquals(10000.00, $invoice->paid_amount);
        $this->assertEquals(0.00, $invoice->balance_amount);
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_proforma_to_official_tax_invoice_conversion(): void
    {
        $admin = User::where('email', 'admin@acme.com')->first();
        $this->actingAs($admin);
        $customer = Customer::first();

        // Create Proforma Quote
        $invoice = Invoice::create([
            'company_id' => $admin->company_id,
            'customer_id' => $customer->id,
            'created_by' => $admin->id,
            'invoice_number' => 'EST-QUOTE-99',
            'type' => 'proforma',
            'invoice_date' => '2026-09-20',
            'sale_type' => 'LOCAL',
            'tax_mode' => 'simple',
            'status' => 'draft',
            'taxable_amount' => 5000,
            'total_amount' => 5900,
            'paid_amount' => 0,
            'balance_amount' => 5900,
        ]);

        $this->assertEquals('proforma', $invoice->type);

        // 1-Click Convert to Tax Invoice
        $response = $this->post("/invoices/{$invoice->id}/convert-tax");
        $response->assertSessionHas('success');

        $invoice->refresh();
        $this->assertEquals('tax_invoice', $invoice->type);
        $this->assertNotEquals('EST-QUOTE-99', $invoice->invoice_number);
    }

    public function test_gstr1_b2b_b2c_and_hsn_summary_tax_report(): void
    {
        $admin = User::where('email', 'admin@acme.com')->first();
        $this->actingAs($admin);

        $response = $this->get('/reports/gstr1?month=' . date('Y-m'));
        $response->assertStatus(200);
        $response->assertSee('GSTR-1 Tax Reports');
        $response->assertSee('Table 4: B2B Tax Invoices');

        // CSV Export
        $csvResponse = $this->get('/reports/gstr1/export-csv?month=' . date('Y-m'));
        $csvResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $csvResponse->headers->get('Content-Type'));
    }
    public function test_super_admin_dashboard_redirect_and_print_template_toggles(): void
    {
        $super = User::where('role', 'super_admin')->first();
        $this->actingAs($super);

        // Super Admin accessing /dashboard must safely redirect to super-admin without 500 error
        $response = $this->get('/dashboard');
        $response->assertRedirect(route('superadmin.index'));

        // Switch to tenant admin to test print templates
        $admin = User::where('email', 'admin@acme.com')->first();
        $this->actingAs($admin);

        $invoice = Invoice::where('company_id', $admin->company_id)->first();

        // Test Print Template 1: Modern
        $printModern = $this->get("/invoices/{$invoice->id}/print?template=modern");
        $printModern->assertStatus(200);
        $printModern->assertSee('Template 1: Modern Executive');

        // Test Print Template 2: Classic
        $printClassic = $this->get("/invoices/{$invoice->id}/print?template=classic");
        $printClassic->assertStatus(200);
        $printClassic->assertSee('Template 2: Classic Corporate');
    }

    public function test_superadmin_protected_from_invoice_create_without_impersonation(): void
    {
        $super = User::where('role', 'super_admin')->first();
        $this->actingAs($super);

        // Accessing /invoices/create without impersonation safely redirects to super-admin without 500 error
        $response = $this->get('/invoices/create');
        $response->assertRedirect(route('superadmin.index'));
        $response->assertSessionHas('info');
    }

    public function test_company_onboarding_approval_workflow(): void
    {
        // 1. Enable onboarding verification requirement
        \App\Models\PlatformSetting::set('require_admin_approval_for_onboarding', '1');

        // 2. Register a new tenant company
        $regResponse = $this->post('/register', [
            'company_name'          => 'Kavya Tech Solutions',
            'admin_name'            => 'Kavya Sharma',
            'email'                 => 'kavya@techsolutions.com',
            'password'              => 'KavyaPass123',
            'password_confirmation' => 'KavyaPass123',
            'phone'                 => '+919876543210',
            'state'                 => 'Maharashtra',
            'gstin'                 => '27ABCDE1234F1Z5',
            'tax_mode'              => 'detailed',
            'otp'                   => \App\Models\EmailOtp::generateFor('kavya@techsolutions.com'),
        ]);
        $regResponse->assertRedirect('/pending-approval');

        $company = \App\Models\Company::where('name', 'Kavya Tech Solutions')->first();
        $this->assertNotNull($company);
        $this->assertEquals('pending', $company->approval_status);

        // 3. User is redirected to pending page when trying to access dashboard
        $kavyaUser = \App\Models\User::where('email', 'kavya@techsolutions.com')->first();

        $dashResponse = $this->get('/dashboard');
        $dashResponse->assertRedirect('/pending-approval');

        $pendingPage = $this->get('/pending-approval');
        $pendingPage->assertStatus(200);
        $pendingPage->assertSee('Onboarding Under Review');

        // 4. Super Admin approves company
        $superAdmin = User::where('role', 'super_admin')->first();
        $this->actingAs($superAdmin);

        $approveResponse = $this->post("/super-admin/companies/{$company->id}/approve");
        $approveResponse->assertSessionHas('success');

        $company->refresh();
        $this->assertEquals('approved', $company->approval_status);

        // 5. User can now access dashboard
        $freshUser = \App\Models\User::where('email', 'kavya@techsolutions.com')->first();
        $this->actingAs($freshUser);
        $activeDash = $this->get('/dashboard');
        $activeDash->assertStatus(200);
    }

    public function test_onboarding_4_digit_otp_generation_and_validation(): void
    {
        // 1. Requesting OTP for already taken email returns validation error
        $takenResp = $this->postJson('/register/send-otp', ['email' => 'admin@acme.com']);
        $takenResp->assertStatus(422);

        // 2. Requesting OTP for valid new business email generates 4-digit code
        $otpResp = $this->postJson('/register/send-otp', ['email' => 'founder@newventure.com']);
        $otpResp->assertStatus(200);
        $otpResp->assertJson(['success' => true]);

        $this->assertDatabaseHas('email_otps', [
            'email' => 'founder@newventure.com',
        ]);

        $otpRecord = \App\Models\EmailOtp::where('email', 'founder@newventure.com')->first();
        $this->assertEquals(4, strlen($otpRecord->otp));

        // 3. Registering with invalid OTP fails
        $failReg = $this->post('/register', [
            'company_name'          => 'New Venture Pvt Ltd',
            'admin_name'            => 'Sameer Roy',
            'email'                 => 'founder@newventure.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'state'                 => 'Karnataka',
            'tax_mode'              => 'simple',
            'otp'                   => '9999', // Wrong!
        ]);
        $failReg->assertSessionHasErrors('otp');

        // 4. Registering with correct 4-digit OTP succeeds
        $passReg = $this->post('/register', [
            'company_name'          => 'New Venture Pvt Ltd',
            'admin_name'            => 'Sameer Roy',
            'email'                 => 'founder@newventure.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
            'state'                 => 'Karnataka',
            'tax_mode'              => 'simple',
            'otp'                   => $otpRecord->otp,
        ]);
        $passReg->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'founder@newventure.com']);
    }

    public function test_tenant_staff_creation_and_granular_module_permission_enforcement(): void
    {
        $admin = User::where('email', 'admin@acme.com')->first();
        $this->actingAs($admin);

        // 1. Company Admin accesses Team & Staff page
        $teamView = $this->get('/team');
        $teamView->assertStatus(200);
        $teamView->assertSee('Role-Based Access');

        // 2. Company Admin creates a staff member with LIMITED permissions (invoices & customers only)
        $createStaffResp = $this->post('/team', [
            'name'        => 'Priya Operator',
            'email'       => 'priya@acme.com',
            'password'    => 'PriyaSecret123',
            'phone'       => '+919123456780',
            'permissions' => ['invoices', 'customers'],
        ]);
        $createStaffResp->assertSessionHas('success');

        $staff = User::where('email', 'priya@acme.com')->first();
        $this->assertNotNull($staff);
        $this->assertEquals('staff', $staff->role);
        $this->assertTrue($staff->hasPermission('invoices'));
        $this->assertTrue($staff->hasPermission('customers'));
        $this->assertFalse($staff->hasPermission('settings'));
        $this->assertFalse($staff->hasPermission('reports'));

        // 3. Staff logs in
        $this->actingAs($staff);

        // Allowed modules work
        $invoiceResp = $this->get('/invoices');
        $invoiceResp->assertStatus(200);

        $customerResp = $this->get('/customers');
        $customerResp->assertStatus(200);

        // Disallowed modules return 403 Forbidden
        $settingsResp = $this->get('/settings');
        $settingsResp->assertStatus(403);

        $reportResp = $this->get('/reports/gstr1');
        $reportResp->assertStatus(403);

        $teamResp = $this->get('/team');
        $teamResp->assertStatus(403);
    }

    /**
     * =========================================================================
     * COMPREHENSIVE SECURITY HARDENING AUDIT TEST SUITE
     * =========================================================================
     */

    public function test_cross_tenant_invoice_access_is_blocked(): void
    {
        $acmeUser = User::where('email', 'admin@acme.com')->first();
        $bharatUser = User::where('email', 'admin@bharat.com')->first();

        // Acme has invoice
        $acmeInvoice = Invoice::where('company_id', $acmeUser->company_id)->first();
        $this->assertNotNull($acmeInvoice);

        // Bharat attempts to view Acme's invoice
        $this->actingAs($bharatUser);

        $viewResp = $this->get('/invoices/' . $acmeInvoice->id);
        $this->assertTrue(in_array($viewResp->status(), [403, 404]));

        $editResp = $this->get('/invoices/' . $acmeInvoice->id . '/edit');
        $this->assertTrue(in_array($editResp->status(), [403, 404]));

        $printResp = $this->get('/invoices/' . $acmeInvoice->id . '/print');
        $this->assertTrue(in_array($printResp->status(), [403, 404]));
    }

    public function test_cross_tenant_invoice_update_and_delete_is_blocked(): void
    {
        $acmeUser = User::where('email', 'admin@acme.com')->first();
        $bharatUser = User::where('email', 'admin@bharat.com')->first();

        $acmeInvoice = Invoice::where('company_id', $acmeUser->company_id)->first();
        $originalNumber = $acmeInvoice->invoice_number;

        $this->actingAs($bharatUser);

        // Attempt update
        $updateResp = $this->put('/invoices/' . $acmeInvoice->id, [
            'customer_id'    => 1,
            'invoice_number' => 'HACKED-999',
            'invoice_date'   => '2026-09-01',
            'sale_type'      => 'LOCAL',
            'tax_mode'       => 'simple',
            'status'         => 'paid',
            'items'          => [
                [
                    'description' => 'Tampered item',
                    'quantity'    => 1,
                    'unit'        => 'NOS',
                    'rate'        => 100,
                    'gst_percent' => 18,
                ]
            ]
        ]);
        $this->assertTrue(in_array($updateResp->status(), [403, 404]));

        // Assert database was NOT modified
        $freshAcmeInvoice = Invoice::withoutGlobalScopes()->find($acmeInvoice->id);
        $this->assertEquals($originalNumber, $freshAcmeInvoice->invoice_number);

        // Attempt delete
        $deleteResp = $this->delete('/invoices/' . $acmeInvoice->id);
        $this->assertTrue(in_array($deleteResp->status(), [403, 404]));
        $this->assertFalse($freshAcmeInvoice->fresh()->trashed());

        // Attempt mark as paid
        $markPaidResp = $this->post('/invoices/' . $acmeInvoice->id . '/mark-as-paid');
        $this->assertTrue(in_array($markPaidResp->status(), [403, 404]));
    }

    public function test_cross_tenant_customer_modification_is_blocked(): void
    {
        $acmeUser = User::where('email', 'admin@acme.com')->first();
        $bharatUser = User::where('email', 'admin@bharat.com')->first();

        $acmeCustomer = Customer::where('company_id', $acmeUser->company_id)->first();
        $this->assertNotNull($acmeCustomer);
        $originalName = $acmeCustomer->name;

        $this->actingAs($bharatUser);

        // Bharat attempts to update Acme's customer
        $updateResp = $this->put('/customers/' . $acmeCustomer->id, [
            'name'  => 'Hacked Customer Name',
            'state' => 'Delhi',
        ]);
        $this->assertTrue(in_array($updateResp->status(), [403, 404]));

        $freshCustomer = Customer::withoutGlobalScopes()->find($acmeCustomer->id);
        $this->assertEquals($originalName, $freshCustomer->name);

        // Bharat attempts to delete Acme's customer
        $deleteResp = $this->delete('/customers/' . $acmeCustomer->id);
        $this->assertTrue(in_array($deleteResp->status(), [403, 404]));
        $this->assertFalse($freshCustomer->fresh()->trashed());
    }

    public function test_cross_tenant_product_modification_is_blocked(): void
    {
        $acmeUser = User::where('email', 'admin@acme.com')->first();
        $bharatUser = User::where('email', 'admin@bharat.com')->first();

        $acmeProduct = Product::where('company_id', $acmeUser->company_id)->first();
        $this->assertNotNull($acmeProduct);
        $originalRate = $acmeProduct->rate;

        $this->actingAs($bharatUser);

        // Bharat attempts to update Acme's product
        $updateResp = $this->put('/products/' . $acmeProduct->id, [
            'name'        => 'Hacked Product',
            'unit'        => 'NOS',
            'rate'        => 0.01,
            'gst_percent' => 18,
        ]);
        $this->assertTrue(in_array($updateResp->status(), [403, 404]));

        $freshProduct = Product::withoutGlobalScopes()->find($acmeProduct->id);
        $this->assertEquals($originalRate, $freshProduct->rate);

        // Bharat attempts to delete Acme's product
        $deleteResp = $this->delete('/products/' . $acmeProduct->id);
        $this->assertTrue(in_array($deleteResp->status(), [403, 404]));
        $this->assertFalse($freshProduct->fresh()->trashed());
    }

    public function test_cross_tenant_payment_recording_and_deletion_is_blocked(): void
    {
        $acmeUser = User::where('email', 'admin@acme.com')->first();
        $bharatUser = User::where('email', 'admin@bharat.com')->first();

        $acmeInvoice = Invoice::where('company_id', $acmeUser->company_id)->first();

        // Acme records a payment
        $this->actingAs($acmeUser);
        $this->post('/invoices/' . $acmeInvoice->id . '/payments', [
            'amount'         => 100,
            'payment_method' => 'cash',
            'paid_at'        => now()->format('Y-m-d'),
        ]);

        $acmeTx = InvoiceTransaction::withoutGlobalScopes()->where('invoice_id', $acmeInvoice->id)->latest('id')->first();
        $this->assertNotNull($acmeTx);

        // Bharat attempts to record payment on Acme's invoice
        $this->actingAs($bharatUser);
        $recordResp = $this->post('/invoices/' . $acmeInvoice->id . '/payments', [
            'amount'         => 50,
            'payment_method' => 'cash',
            'paid_at'        => now()->format('Y-m-d'),
        ]);
        $this->assertTrue(in_array($recordResp->status(), [403, 404]));

        // Bharat attempts to delete Acme's transaction
        $deleteResp = $this->delete('/payments/' . $acmeTx->id);
        $this->assertTrue(in_array($deleteResp->status(), [403, 404]));

        // Bharat attempts to view Acme's payment receipt voucher
        $receiptResp = $this->get('/payments/' . $acmeTx->id . '/receipt');
        $this->assertTrue(in_array($receiptResp->status(), [403, 404]));
    }

    public function test_cross_tenant_customer_cannot_be_associated_with_invoice(): void
    {
        $acmeUser = User::where('email', 'admin@acme.com')->first();
        $bharatUser = User::where('email', 'admin@bharat.com')->first();

        // Bharat has customer
        $bharatCustomer = Customer::where('company_id', $bharatUser->company_id)->first();
        $this->assertNotNull($bharatCustomer);

        $this->actingAs($acmeUser);

        // Acme attempts to create invoice with Bharat's customer
        $resp = $this->post('/invoices', [
            'customer_id'    => $bharatCustomer->id,
            'invoice_number' => 'ACME-SEC-001',
            'invoice_date'   => now()->format('Y-m-d'),
            'sale_type'      => 'LOCAL',
            'tax_mode'       => 'simple',
            'status'         => 'draft',
            'items'          => [
                [
                    'description' => 'Test Line Item',
                    'quantity'    => 1,
                    'unit'        => 'NOS',
                    'rate'        => 500,
                    'gst_percent' => 18,
                ]
            ]
        ]);

        $resp->assertSessionHasErrors('customer_id');
        $this->assertDatabaseMissing('invoices', ['invoice_number' => 'ACME-SEC-001']);
    }

    public function test_rbac_blocked_routes_return_403_for_unauthorized_staff(): void
    {
        $acmeAdmin = User::where('email', 'admin@acme.com')->first();

        // Create a staff user with ONLY 'invoices' permission
        $staff = User::create([
            'company_id'  => $acmeAdmin->company_id,
            'name'        => 'Limited Staff User',
            'email'       => 'limited@acme.com',
            'password'    => bcrypt('password'),
            'role'        => 'staff',
            'permissions' => ['invoices'],
            'is_active'   => true,
        ]);

        $this->actingAs($staff);

        // Invoices module works
        $invResp = $this->get('/invoices');
        $invResp->assertStatus(200);

        // Disallowed modules return HTTP 403 Forbidden
        $settingsResp = $this->get('/settings');
        $settingsResp->assertStatus(403);

        $reportsResp = $this->get('/reports/gstr1');
        $reportsResp->assertStatus(403);

        $exportResp = $this->get('/reports/gstr1/export-csv');
        $exportResp->assertStatus(403);

        $teamResp = $this->get('/team');
        $teamResp->assertStatus(403);

        $customersResp = $this->get('/customers');
        $customersResp->assertStatus(403);

        $productsResp = $this->get('/products');
        $productsResp->assertStatus(403);
    }

    public function test_otp_brute_force_lockout_after_5_failed_attempts(): void
    {
        $email = 'victim@securitytest.com';
        $otp = EmailOtp::generateFor($email, true);

        // Attempt 1 to 4 with wrong OTP
        for ($i = 1; $i <= 4; $i++) {
            $result = EmailOtp::verify($email, '0000');
            $this->assertFalse($result);
        }

        $record = EmailOtp::where('email', $email)->first();
        $this->assertNotNull($record);
        $this->assertEquals(4, $record->attempts);

        // 5th failed attempt -> OTP is permanently revoked/deleted
        $fifth = EmailOtp::verify($email, '0000');
        $this->assertFalse($fifth);

        $this->assertDatabaseMissing('email_otps', ['email' => $email]);

        // Even if the correct OTP is provided now, it is rejected
        $lateAttempt = EmailOtp::verify($email, $otp);
        $this->assertFalse($lateAttempt);
    }

    public function test_otp_server_side_resend_cooldown_enforced(): void
    {
        $email = 'cooldown@test.com';
        $this->postJson('/register/send-otp', ['email' => $email])->assertStatus(200);

        // Second request immediately should be rejected due to 60s cooldown
        $second = $this->postJson('/register/send-otp', ['email' => $email]);
        $second->assertStatus(429);
        $this->assertFalse($second->json('success'));
        $this->assertStringContainsString('wait', strtolower($second->json('message')));
    }

    public function test_company_billing_email_cannot_be_overwritten_via_settings(): void
    {
        $acmeAdmin = User::where('email', 'admin@acme.com')->first();
        $company = $acmeAdmin->company;
        $originalEmail = $company->email;

        $this->actingAs($acmeAdmin);

        // Submit settings update with a malicious new email
        $response = $this->post('/settings', [
            'name'                        => $company->name,
            'email'                       => 'takeover@evil.com',
            'state'                       => $company->state,
            'tax_mode'                    => $company->tax_mode,
            'invoice_prefix'              => $company->invoice_prefix,
            'invoice_start_number'        => $company->invoice_start_number,
        ]);

        $response->assertSessionHas('success');

        // Verify company email in DB remains intact
        $freshCompany = Company::find($company->id);
        $this->assertEquals($originalEmail, $freshCompany->email);
        $this->assertNotEquals('takeover@evil.com', $freshCompany->email);
    }

    public function test_impersonation_full_audit_lifecycle(): void
    {
        $superAdmin = User::where('email', 'superadmin@gstsaas.com')->first();
        $this->assertNotNull($superAdmin);

        $bharatCompany = Company::where('slug', 'bharat-trade')->first();
        $this->assertNotNull($bharatCompany);

        $this->actingAs($superAdmin);

        // 1. Super Admin starts impersonation
        $startResp = $this->post('/super-admin/companies/' . $bharatCompany->id . '/impersonate');
        $startResp->assertRedirect(route('dashboard'));

        $this->assertEquals($superAdmin->id, session('impersonator_id'));
        $this->assertNotEmpty(session('impersonation_token'));

        $this->assertDatabaseHas('activity_logs', [
            'action'    => 'impersonate_start',
            'user_name' => $superAdmin->name,
        ]);

        // 2. Currently logged in as Bharat Admin
        $currentAuth = auth()->user();
        $this->assertEquals('company_admin', $currentAuth->role);
        $this->assertEquals($bharatCompany->id, $currentAuth->company_id);

        // 3. Stop impersonation
        $stopResp = $this->post('/super-admin/stop-impersonate');
        $stopResp->assertRedirect(route('superadmin.index'));

        $this->assertNull(session('impersonator_id'));
        $this->assertNull(session('impersonation_token'));

        $this->assertDatabaseHas('activity_logs', [
            'action'    => 'impersonate_end',
            'user_name' => $superAdmin->name,
        ]);

        // Authenticated back as Super Admin
        $this->assertEquals($superAdmin->id, auth()->id());
    }

    public function test_security_headers_are_present_on_response(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_superadmin_can_directly_onboard_and_provision_company(): void
    {
        $superAdmin = User::where('email', 'superadmin@gstsaas.com')->first();
        $this->actingAs($superAdmin);

        $response = $this->post(route('superadmin.companies.store'), [
            'company_name'  => 'Apex Cloud Technologies Pvt Ltd',
            'industry_type' => 'IT & Software Services',
            'admin_name'    => 'Karan Oberoi',
            'email'         => 'karan@apexcloud.io',
            'password'      => 'SecurePassword123!',
            'phone'         => '+919988776655',
            'state'         => 'Karnataka',
            'gstin'         => '29AAAAA0000A1Z5',
            'tax_mode'      => 'detailed',
        ]);

        $response->assertRedirect(route('superadmin.index'));
        $response->assertSessionHas('success');

        $company = Company::where('name', 'Apex Cloud Technologies Pvt Ltd')->first();
        $this->assertNotNull($company);
        $this->assertEquals('approved', $company->approval_status);
        $this->assertTrue($company->is_active);
        $this->assertEquals('Karnataka', $company->state);

        $admin = User::where('email', 'karan@apexcloud.io')->first();
        $this->assertNotNull($admin);
        $this->assertEquals($company->id, $admin->company_id);
        $this->assertEquals('company_admin', $admin->role);
        $this->assertTrue($admin->is_active);

        $this->assertDatabaseHas('activity_logs', [
            'action'    => 'company_onboarded',
            'user_name' => $superAdmin->name,
        ]);
    }

    public function test_users_can_toggle_2fa_in_profile_across_all_roles(): void
    {
        // 1. Super Admin toggle
        $superAdmin = User::where('email', 'superadmin@gstsaas.com')->first();
        $this->actingAs($superAdmin);

        $this->assertFalse((bool) $superAdmin->is_2fa_enabled);
        $resp1 = $this->post(route('profile.2fa.toggle'));
        $resp1->assertSessionHas('success');
        $this->assertTrue((bool) $superAdmin->fresh()->is_2fa_enabled);

        // Toggle back off
        $resp2 = $this->post(route('profile.2fa.toggle'));
        $resp2->assertSessionHas('success');
        $this->assertFalse((bool) $superAdmin->fresh()->is_2fa_enabled);

        // 2. Company Admin toggle
        $companyAdmin = User::where('email', 'admin@acme.com')->first();
        $this->actingAs($companyAdmin);
        $this->post(route('profile.2fa.toggle'))->assertSessionHas('success');
        $this->assertTrue((bool) $companyAdmin->fresh()->is_2fa_enabled);

        // 3. Staff User toggle
        $staff = User::where('email', 'staff@acme.com')->first();
        $this->actingAs($staff);
        $this->post(route('profile.2fa.toggle'))->assertSessionHas('success');
        $this->assertTrue((bool) $staff->fresh()->is_2fa_enabled);
    }

    public function test_two_factor_authentication_login_flow(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        $user = User::where('email', 'admin@acme.com')->first();

        // Case A: 2FA is OFF -> Direct login
        $user->update(['is_2fa_enabled' => false]);
        $directLogin = $this->post('/login', [
            'email'    => 'admin@acme.com',
            'password' => 'password',
        ]);
        $directLogin->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        auth()->logout();
        $this->assertGuest();

        // Case B: 2FA is ON -> Redirects to /login/2fa challenge
        $user->update(['is_2fa_enabled' => true]);
        $challengeResp = $this->post('/login', [
            'email'    => 'admin@acme.com',
            'password' => 'password',
        ]);
        $challengeResp->assertRedirect(route('login.2fa'));
        $this->assertGuest(); // Not authenticated yet!

        $this->assertEquals($user->id, session('2fa:user:id'));
        $freshUser = $user->fresh();
        $this->assertNotNull($freshUser->two_factor_code);
        $this->assertNotNull($freshUser->two_factor_expires_at);

        // Verify 2FA page loads
        $twoFactorPage = $this->get(route('login.2fa'));
        $twoFactorPage->assertStatus(200);
        $twoFactorPage->assertSee('Two-Factor Authentication');
        $twoFactorPage->assertSee('admin@acme.com');

        // Invalid code fails
        $failVerify = $this->post(route('login.2fa.verify'), ['code' => '000000']);
        $failVerify->assertSessionHasErrors('code');
        $this->assertGuest();

        // Test resend generates new code
        $resendResp = $this->withSession(['2fa:user:id' => $user->id])->post(route('login.2fa.resend'));
        $resendResp->assertSessionHas('success');

        // Extract fresh code directly for assertion test
        $freshCode = session('2fa:preview');
        $this->assertNotEmpty($freshCode);

        // Submit valid OTP
        $successVerify = $this->withSession(['2fa:user:id' => $user->id])->post(route('login.2fa.verify'), ['code' => $freshCode]);
        $successVerify->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        // 2FA session cleared
        $this->assertNull(session('2fa:user:id'));
        $this->assertNull($user->fresh()->two_factor_code);
    }
}
