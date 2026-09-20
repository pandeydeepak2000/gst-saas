<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
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
}
