<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
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

        $response = $this->put('/profile/info', [
            'name'  => 'Rajesh Kumar Updated',
            'email' => 'rajesh.updated@acme.com',
            'phone' => '+919988770000',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'    => $acmeAdmin->id,
            'name'  => 'Rajesh Kumar Updated',
            'email' => 'rajesh.updated@acme.com',
            'phone' => '+919988770000',
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
}
