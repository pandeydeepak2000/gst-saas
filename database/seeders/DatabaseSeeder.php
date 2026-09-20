<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceTransaction;
use App\Models\ActivityLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin
        $superAdmin = User::create([
            'name'       => 'Super Administrator',
            'email'      => 'superadmin@gstsaas.com',
            'password'   => Hash::make('password'),
            'role'       => 'super_admin',
            'company_id' => null,
            'phone'      => '+919876543210',
            'is_active'  => true,
        ]);

        // 2. Company 1: Acme Infotech Solutions (Detailed Split GST)
        $acme = Company::create([
            'name'                        => 'Acme Infotech Solutions Pvt Ltd',
            'slug'                        => 'acme-infotech',
            'email'                       => 'billing@acme.com',
            'phone'                       => '+918023456789',
            'address'                     => 'Plot 42, Electronic City Phase 1',
            'city'                        => 'Bangalore',
            'state'                       => 'Karnataka',
            'pincode'                     => '560100',
            'gstin'                       => '29ABCDE1234F1Z5',
            'pan'                         => 'ABCDE1234F',
            'tax_mode'                    => 'detailed', // Split CGST/SGST/IGST
            'invoice_prefix'              => 'ACME/26/',
            'invoice_start_number'        => 1001,
            'allow_manual_invoice_number' => true,
            'bank_name'                   => 'HDFC Bank',
            'bank_account_number'         => '50200012345678',
            'bank_ifsc'                   => 'HDFC0000240',
            'bank_branch'                 => 'Koramangala Branch',
            'upi_id'                      => 'acmeinfotech@hdfcbank',
            'terms_and_conditions'        => '1. Payment due within 15 days of invoice date.\n2. Goods/Services once billed will not be cancelled.',
            'is_active'                   => true,
        ]);

        $acmeAdmin = User::create([
            'name'       => 'Rajesh Kumar (Owner)',
            'email'      => 'admin@acme.com',
            'password'   => Hash::make('password'),
            'role'       => 'company_admin',
            'company_id' => $acme->id,
            'phone'      => '+919988776655',
            'is_active'  => true,
        ]);

        $acmeStaff = User::create([
            'name'       => 'Pooja Sharma (Billing)',
            'email'      => 'staff@acme.com',
            'password'   => Hash::make('password'),
            'role'       => 'staff',
            'company_id' => $acme->id,
            'phone'      => '+919988776644',
            'is_active'  => true,
        ]);

                // Acme Products
        Product::create([
            'company_id'  => $acme->id,
            'name'        => 'Cloud ERP Architecture Consulting',
            'hsn_sac'     => '998313',
            'unit'        => 'Service',
            'rate'        => 30000.00,
            'gst_percent' => 18.00,
            'description' => 'Multi-Tenant Database Design & Consulting'
        ]);

        Product::create([
            'company_id'  => $acme->id,
            'name'        => 'Annual Cloud Hosting & SSL Security',
            'hsn_sac'     => '998315',
            'unit'        => 'Year',
            'rate'        => 20000.00,
            'gst_percent' => 18.00,
            'description' => 'Dedicated cloud server provisioning with 99.9% uptime SLA'
        ]);

        Product::create([
            'company_id'  => $acme->id,
            'name'        => 'Enterprise Security Audit & Vulnerability Assessment',
            'hsn_sac'     => '998314',
            'unit'        => 'Job',
            'rate'        => 25000.00,
            'gst_percent' => 18.00,
            'description' => 'Comprehensive penetration testing and compliance audit'
        ]);

        // Acme Customers
        $cust1 = Customer::create([
            'company_id'      => $acme->id,
            'name'            => 'Zenith Global Systems',
            'company_name'    => 'Zenith Global Systems LLC',
            'email'           => 'finance@zenith.com',
            'phone'           => '+919811223344',
            'billing_address' => 'Floor 8, Manyata Tech Park',
            'city'            => 'Bangalore',
            'state'           => 'Karnataka',
            'pincode'         => '560045',
            'gstin'           => '29ZZZZZ9999Z1Z1',
        ]);

        $cust2 = Customer::create([
            'company_id'      => $acme->id,
            'name'            => 'Apex Retail Network',
            'company_name'    => 'Apex Network Pvt Ltd',
            'email'           => 'accounts@apex.in',
            'phone'           => '+919877001122',
            'billing_address' => 'Road 12, Banjara Hills',
            'city'            => 'Hyderabad',
            'state'           => 'Telangana',
            'pincode'         => '500034',
            'gstin'           => '36AAAAA1111A1Z9',
        ]);

        // Invoice 1 (Paid - Detailed Local CGST/SGST)
        $inv1 = Invoice::create([
            'company_id'      => $acme->id,
            'customer_id'     => $cust1->id,
            'created_by'      => $acmeAdmin->id,
            'invoice_number'  => 'ACME/26/1001',
            'invoice_date'    => now()->subDays(5)->format('Y-m-d'),
            'due_date'        => now()->addDays(10)->format('Y-m-d'),
            'sale_type'       => 'LOCAL',
            'tax_mode'        => 'detailed',
            'taxable_amount'  => 50000.00,
            'cgst_amount'     => 4500.00,
            'sgst_amount'     => 4500.00,
            'igst_amount'     => 0.00,
            'total_amount'    => 59000.00,
            'status'          => 'paid',
            'payment_method'  => 'UPI / NEFT',
            'transaction_ref' => 'HDFC789012345',
            'notes'           => 'Cloud ERP Architecture & Multi-Tenant Setup Phase 1',
        ]);

        InvoiceItem::create([
            'invoice_id'     => $inv1->id,
            'description'    => 'Cloud ERP Architecture Consulting & Database Design',
            'hsn_sac'        => '998313',
            'quantity'       => 1,
            'unit'           => 'Service',
            'rate'           => 30000.00,
            'gst_percent'    => 18.00,
            'taxable_amount' => 30000.00,
            'cgst_amount'    => 2700.00,
            'sgst_amount'    => 2700.00,
            'igst_amount'    => 0.00,
            'line_total'     => 35400.00,
        ]);

        InvoiceItem::create([
            'invoice_id'     => $inv1->id,
            'description'    => 'Annual Cloud Hosting & SSL Security Provisioning',
            'hsn_sac'        => '998315',
            'quantity'       => 1,
            'unit'           => 'Year',
            'rate'           => 20000.00,
            'gst_percent'    => 18.00,
            'taxable_amount' => 20000.00,
            'cgst_amount'    => 1800.00,
            'sgst_amount'    => 1800.00,
            'igst_amount'    => 0.00,
            'line_total'     => 23600.00,
        ]);

        InvoiceTransaction::create([
            'invoice_id'     => $inv1->id,
            'gateway'        => 'HDFC NetBanking',
            'transaction_id' => 'HDFC789012345',
            'amount'         => 59000.00,
            'paid_at'        => now()->subDays(4),
        ]);

        // Invoice 2: Custom Edited Number demonstrating manual freedom!
        $inv2 = Invoice::create([
            'company_id'      => $acme->id,
            'customer_id'     => $cust2->id,
            'created_by'      => $acmeAdmin->id,
            'invoice_number'  => 'ACME-SPECIAL-2026', // Custom edited!
            'invoice_date'    => now()->subDays(2)->format('Y-m-d'),
            'due_date'        => now()->addDays(12)->format('Y-m-d'),
            'sale_type'       => 'CENTRAL', // Interstate IGST
            'tax_mode'        => 'detailed',
            'taxable_amount'  => 25000.00,
            'cgst_amount'     => 0.00,
            'sgst_amount'     => 0.00,
            'igst_amount'     => 4500.00,
            'total_amount'    => 29500.00,
            'status'          => 'unpaid',
            'notes'           => 'Custom customized invoice number overridden manually by company.',
        ]);

        InvoiceItem::create([
            'invoice_id'     => $inv2->id,
            'description'    => 'Enterprise Security Audit & Vulnerability Assessment',
            'hsn_sac'        => '998314',
            'quantity'       => 1,
            'unit'           => 'Job',
            'rate'           => 25000.00,
            'gst_percent'    => 18.00,
            'taxable_amount' => 25000.00,
            'cgst_amount'    => 0.00,
            'sgst_amount'    => 0.00,
            'igst_amount'    => 4500.00,
            'line_total'     => 29500.00,
        ]);

        // Invoice 3: Soft-deleted to show Trash & Recovery!
        $inv3 = Invoice::create([
            'company_id'      => $acme->id,
            'customer_id'     => $cust1->id,
            'created_by'      => $acmeStaff->id,
            'invoice_number'  => 'ACME/26/1003',
            'invoice_date'    => now()->subDays(8)->format('Y-m-d'),
            'due_date'        => now()->subDays(1)->format('Y-m-d'),
            'sale_type'       => 'LOCAL',
            'tax_mode'        => 'detailed',
            'taxable_amount'  => 12000.00,
            'cgst_amount'     => 1080.00,
            'sgst_amount'     => 1080.00,
            'igst_amount'     => 0.00,
            'total_amount'    => 14160.00,
            'status'          => 'unpaid',
            'notes'           => 'Draft test invoice created by mistake.',
        ]);
        $inv3->delete(); // Soft delete into Trash!

        // 3. Company 2: Bharat Trade & Hardware (Simple GST Mode - Single 18% line)
        $bharat = Company::create([
            'name'                        => 'Bharat Trade & Hardware',
            'slug'                        => 'bharat-trade',
            'email'                       => 'bharattrade@gmail.com',
            'phone'                       => '+919431000000',
            'address'                     => 'Main Road, Station Chowk',
            'city'                        => 'Khagaria',
            'state'                       => 'Bihar',
            'pincode'                     => '851204',
            'gstin'                       => '10BBBBB5555B1Z0',
            'pan'                         => 'BBBBB5555B',
            'tax_mode'                    => 'simple', // Single line GST (e.g. GST 18%)
            'invoice_prefix'              => 'BTH-',
            'invoice_start_number'        => 501,
            'allow_manual_invoice_number' => true,
            'bank_name'                   => 'State Bank of India',
            'bank_account_number'         => '31234567890',
            'bank_ifsc'                   => 'SBIN0000115',
            'bank_branch'                 => 'Khagaria Main Branch',
            'upi_id'                      => '9431000000@sbi',
            'terms_and_conditions'        => '1. All disputes subject to Khagaria jurisdiction.\n2. Goods once sold will not be taken back.',
            'is_active'                   => true,
        ]);

        $bharatAdmin = User::create([
            'name'       => 'Sanjay Kumar (Owner)',
            'email'      => 'admin@bharat.com',
            'password'   => Hash::make('password'),
            'role'       => 'company_admin',
            'company_id' => $bharat->id,
            'phone'      => '+919431000000',
            'is_active'  => true,
        ]);

                // Bharat Products
        Product::create([
            'company_id'  => $bharat->id,
            'name'        => 'Heavy Brass Gate Valve 25mm (ISI Mark)',
            'hsn_sac'     => '8481',
            'unit'        => 'Pcs',
            'rate'        => 450.00,
            'gst_percent' => 18.00,
            'description' => 'Tested for 16 Bar pressure, forged brass body'
        ]);

        Product::create([
            'company_id'  => $bharat->id,
            'name'        => 'CPVC Pipe 1 Inch (Heavy Duty 3 Meter)',
            'hsn_sac'     => '3917',
            'unit'        => 'Length',
            'rate'        => 220.00,
            'gst_percent' => 18.00,
            'description' => 'SDR-11 hot and cold water plumbing pipe'
        ]);

        Product::create([
            'company_id'  => $bharat->id,
            'name'        => 'Brass Bib Cock with Wall Flange',
            'hsn_sac'     => '8481',
            'unit'        => 'Pcs',
            'rate'        => 380.00,
            'gst_percent' => 18.00,
            'description' => 'Chrome plated quarter turn water tap'
        ]);

        $bharatCust = Customer::create([
            'company_id'      => $bharat->id,
            'name'            => 'Koshi Constructions',
            'company_name'    => 'Koshi Infra Pvt Ltd',
            'email'           => 'koshiinfra@gmail.com',
            'phone'           => '+919122334455',
            'billing_address' => 'Near Power Grid, Alaudinpur',
            'city'            => 'Khagaria',
            'state'           => 'Bihar',
            'pincode'         => '851205',
            'gstin'           => '10KKKKK8888K1Z3',
        ]);

        $invBTH = Invoice::create([
            'company_id'      => $bharat->id,
            'customer_id'     => $bharatCust->id,
            'created_by'      => $bharatAdmin->id,
            'invoice_number'  => 'BTH-501',
            'invoice_date'    => now()->format('Y-m-d'),
            'due_date'        => now()->addDays(7)->format('Y-m-d'),
            'sale_type'       => 'LOCAL',
            'tax_mode'        => 'simple', // Simple single line GST
            'taxable_amount'  => 15600.00,
            'cgst_amount'     => 1404.00,
            'sgst_amount'     => 1404.00,
            'igst_amount'     => 0.00,
            'total_amount'    => 18408.00,
            'status'          => 'unpaid',
            'notes'           => 'Wholesale sanitary and brass fitting delivery.',
        ]);

        InvoiceItem::create([
            'invoice_id'     => $invBTH->id,
            'description'    => 'Heavy Brass Gate Valve 25mm (ISI Mark)',
            'hsn_sac'        => '8481',
            'quantity'       => 20,
            'unit'           => 'Pcs',
            'rate'           => 450.00,
            'gst_percent'    => 18.00,
            'taxable_amount' => 9000.00,
            'cgst_amount'    => 810.00,
            'sgst_amount'    => 810.00,
            'igst_amount'    => 0.00,
            'line_total'     => 10620.00,
        ]);

        InvoiceItem::create([
            'invoice_id'     => $invBTH->id,
            'description'    => 'CPVC Pipe 1 Inch (Heavy Duty 3 Meter)',
            'hsn_sac'        => '3917',
            'quantity'       => 30,
            'unit'           => 'Length',
            'rate'           => 220.00,
            'gst_percent'    => 18.00,
            'taxable_amount' => 6600.00,
            'cgst_amount'    => 594.00,
            'sgst_amount'    => 594.00,
            'igst_amount'    => 0.00,
            'line_total'     => 7788.00,
        ]);

        ActivityLog::create([
            'company_id'  => $acme->id,
            'user_id'     => $acmeAdmin->id,
            'user_name'   => $acmeAdmin->name,
            'role'        => 'company_admin',
            'action'      => 'create',
            'module'      => 'invoice',
            'module_id'   => $inv1->id,
            'description' => "Generated Invoice #{$inv1->invoice_number} for ₹59,000.00",
        ]);

        ActivityLog::create([
            'company_id'  => $bharat->id,
            'user_id'     => $bharatAdmin->id,
            'user_name'   => $bharatAdmin->name,
            'role'        => 'company_admin',
            'action'      => 'create',
            'module'      => 'invoice',
            'module_id'   => $invBTH->id,
            'description' => "Generated Invoice #{$invBTH->invoice_number} for ₹18,408.00",
        ]);
    }
}