<?php
// setup_controllers.php

function writeFileSafe($path, $content) {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($path, $content);
    echo "Created: $path\n";
}

// 1. AuthController
$authController = <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account is deactivated. Contact administrator.']);
            }

            if ($user->company_id && (!$user->company || !$user->company->is_active)) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your company subscription or access is currently inactive.']);
            }

            ActivityLog::log('login', 'auth', 'User logged in successfully');
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function registerCompany(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'admin_name'   => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'state'        => ['required', 'string', 'max:100'],
            'gstin'        => ['nullable', 'string', 'max:20'],
            'tax_mode'     => ['required', 'in:simple,detailed'],
        ]);

        // Generate clean company slug
        $baseSlug = Str::slug($validated['company_name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Company::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        // Generate prefix from initials
        $words = explode(' ', trim($validated['company_name']));
        $prefix = '';
        foreach ($words as $w) {
            $prefix .= strtoupper(substr($w, 0, 1));
        }
        $prefix = substr($prefix, 0, 4) . '-';

        $company = Company::create([
            'name'                        => $validated['company_name'],
            'slug'                        => $slug,
            'email'                       => $validated['email'],
            'phone'                       => $validated['phone'],
            'state'                       => $validated['state'],
            'gstin'                       => strtoupper($validated['gstin'] ?? ''),
            'tax_mode'                    => $validated['tax_mode'],
            'invoice_prefix'              => $prefix,
            'invoice_start_number'        => 1,
            'allow_manual_invoice_number' => true,
            'is_active'                   => true,
        ]);

        $user = User::create([
            'name'       => $validated['admin_name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'company_id' => $company->id,
            'role'       => 'company_admin',
            'phone'      => $validated['phone'],
            'is_active'  => true,
        ]);

        Auth::login($user);
        ActivityLog::log('register', 'auth', "New company '{$company->name}' onboarded.");

        return redirect()->route('dashboard')->with('success', "Welcome to GST-SaaS! Your company '{$company->name}' is ready.");
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::log('logout', 'auth', 'User logged out');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been logged out.');
    }
}
PHP;
writeFileSafe(__DIR__ . '/app/Http/Controllers/AuthController.php', $authController);

// 2. DashboardController
$dashboardController = <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $company = $user->company;

        // Invoices Query (automatically scoped to tenant via TenantScope)
        $invoicesQuery = Invoice::query();

        $totalRevenue = (clone $invoicesQuery)->where('status', '!=', 'cancelled')->sum('total_amount');
        $paidAmount   = (clone $invoicesQuery)->where('status', 'paid')->sum('total_amount');
        $unpaidAmount = (clone $invoicesQuery)->where('status', 'unpaid')->sum('total_amount');
        $totalTaxes   = (clone $invoicesQuery)->where('status', '!=', 'cancelled')
            ->selectRaw('SUM(cgst_amount + sgst_amount + igst_amount) as total_tax')
            ->value('total_tax') ?? 0;

        $invoicesCount  = (clone $invoicesQuery)->count();
        $customersCount = Customer::count();
        $trashCount     = Invoice::onlyTrashed()->count();

        // Recent Invoices
        $recentInvoices = Invoice::with('customer')
            ->latest('id')
            ->take(6)
            ->get();

        // Recent Activity
        $recentLogs = ActivityLog::latest('id')->take(6)->get();

        return view('dashboard', compact(
            'company',
            'user',
            'totalRevenue',
            'paidAmount',
            'unpaidAmount',
            'totalTaxes',
            'invoicesCount',
            'customersCount',
            'trashCount',
            'recentInvoices',
            'recentLogs'
        ));
    }
}
PHP;
writeFileSafe(__DIR__ . '/app/Http/Controllers/DashboardController.php', $dashboardController);

// 3. CompanySettingsController
$settingsController = <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingsController extends Controller
{
    public function index()
    {
        $company = auth()->user()->company;
        return view('settings.index', compact('company'));
    }

    public function update(Request $request)
    {
        $company = auth()->user()->company;

        $validated = $request->validate([
            'name'                        => ['required', 'string', 'max:255'],
            'email'                       => ['nullable', 'email', 'max:255'],
            'phone'                       => ['nullable', 'string', 'max:20'],
            'address'                     => ['nullable', 'string'],
            'city'                        => ['nullable', 'string', 'max:100'],
            'state'                       => ['required', 'string', 'max:100'],
            'pincode'                     => ['nullable', 'string', 'max:10'],
            'gstin'                       => ['nullable', 'string', 'max:20'],
            'pan'                         => ['nullable', 'string', 'max:15'],

            // Tax display toggle: 'simple' vs 'detailed'
            'tax_mode'                    => ['required', 'in:simple,detailed'],

            // Custom Invoice Numbering settings
            'invoice_prefix'              => ['required', 'string', 'max:20'],
            'invoice_start_number'        => ['required', 'integer', 'min:1'],
            'allow_manual_invoice_number' => ['nullable', 'boolean'],

            // Bank & Payment QR
            'bank_name'                   => ['nullable', 'string', 'max:150'],
            'bank_account_number'         => ['nullable', 'string', 'max:50'],
            'bank_ifsc'                   => ['nullable', 'string', 'max:20'],
            'bank_branch'                 => ['nullable', 'string', 'max:100'],
            'upi_id'                      => ['nullable', 'string', 'max:100'],
            'terms_and_conditions'        => ['nullable', 'string'],

            // Files
            'logo'                        => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
            'signature'                   => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
        ]);

        $validated['allow_manual_invoice_number'] = $request->has('allow_manual_invoice_number');
        if (!empty($validated['gstin'])) {
            $validated['gstin'] = strtoupper($validated['gstin']);
        }
        if (!empty($validated['pan'])) {
            $validated['pan'] = strtoupper($validated['pan']);
        }

        if ($request->hasFile('logo')) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        if ($request->hasFile('signature')) {
            if ($company->signature_path) {
                Storage::disk('public')->delete($company->signature_path);
            }
            $validated['signature_path'] = $request->file('signature')->store('signatures', 'public');
        }

        $company->update($validated);

        ActivityLog::log('update', 'settings', "Updated company settings and billing preferences.");

        return back()->with('success', 'Company preferences and invoice settings updated successfully!');
    }
}
PHP;
writeFileSafe(__DIR__ . '/app/Http/Controllers/CompanySettingsController.php', $settingsController);

// 4. CustomerController
$customerController = <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('company_name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('gstin', 'like', "%{$s}%");
            });
        }

        $customers = $query->withCount('invoices')->latest('id')->paginate(15);
        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'company_name'     => ['nullable', 'string', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'email'            => ['nullable', 'email', 'max:255'],
            'gstin'            => ['nullable', 'string', 'max:20'],
            'state'            => ['required', 'string', 'max:100'],
            'billing_address'  => ['nullable', 'string'],
            'city'             => ['nullable', 'string', 'max:100'],
            'pincode'          => ['nullable', 'string', 'max:10'],
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        if (!empty($validated['gstin'])) {
            $validated['gstin'] = strtoupper($validated['gstin']);
        }

        $customer = Customer::create($validated);
        ActivityLog::log('create', 'customer', "Added customer: {$customer->name}", $customer->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'customer' => $customer]);
        }

        return back()->with('success', "Customer '{$customer->name}' created successfully!");
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'company_name'     => ['nullable', 'string', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'email'            => ['nullable', 'email', 'max:255'],
            'gstin'            => ['nullable', 'string', 'max:20'],
            'state'            => ['required', 'string', 'max:100'],
            'billing_address'  => ['nullable', 'string'],
            'city'             => ['nullable', 'string', 'max:100'],
            'pincode'          => ['nullable', 'string', 'max:10'],
        ]);

        if (!empty($validated['gstin'])) {
            $validated['gstin'] = strtoupper($validated['gstin']);
        }

        $customer->update($validated);
        ActivityLog::log('update', 'customer', "Updated customer details: {$customer->name}", $customer->id);

        return back()->with('success', "Customer '{$customer->name}' updated successfully!");
    }

    public function destroy(Customer $customer)
    {
        $name = $customer->name;
        $customer->delete();
        ActivityLog::log('delete', 'customer', "Soft deleted customer: {$name}");

        return back()->with('success', "Customer '{$name}' removed.");
    }
}
PHP;
writeFileSafe(__DIR__ . '/app/Http/Controllers/CustomerController.php', $customerController);

// 5. InvoiceController
$invoiceController = <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'all');
        $query = Invoice::with('customer');

        if ($tab === 'trash') {
            $query = Invoice::onlyTrashed()->with('customer');
        } elseif ($tab === 'paid') {
            $query->where('status', 'paid');
        } elseif ($tab === 'unpaid') {
            $query->where('status', 'unpaid');
        } elseif ($tab === 'partially_paid') {
            $query->where('status', 'partially_paid');
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                  ->orWhereHas('customer', function($cq) use ($s) {
                      $cq->where('name', 'like', "%{$s}%")
                         ->orWhere('company_name', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%");
                  });
            });
        }

        $invoices = $query->latest('id')->paginate(15);
        $trashCount = Invoice::onlyTrashed()->count();

        return view('invoices.index', compact('invoices', 'tab', 'trashCount'));
    }

    public function create()
    {
        $company = auth()->user()->company;
        $customers = Customer::orderBy('name')->get();
        $suggestedNumber = $company->generateNextInvoiceNumber();

        return view('invoices.create', compact('company', 'customers', 'suggestedNumber'));
    }

    public function store(Request $request)
    {
        $company = auth()->user()->company;

        $validated = $request->validate([
            'customer_id'    => ['required', 'exists:customers,id'],
            // Company can edit invoice number freely!
            'invoice_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('invoices', 'invoice_number')
                    ->where('company_id', $company->id)
                    ->whereNull('deleted_at')
            ],
            'invoice_date'   => ['required', 'date'],
            'due_date'       => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'sale_type'      => ['required', 'in:LOCAL,CENTRAL'],
            'tax_mode'       => ['required', 'in:simple,detailed'],
            'status'         => ['required', 'in:draft,unpaid,paid,partially_paid'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'notes'          => ['nullable', 'string'],

            // Items validation
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.description'   => ['required', 'string'],
            'items.*.hsn_sac'       => ['nullable', 'string', 'max:20'],
            'items.*.quantity'      => ['required', 'numeric', 'min:0.01'],
            'items.*.unit'          => ['required', 'string', 'max:20'],
            'items.*.rate'          => ['required', 'numeric', 'min:0'],
            'items.*.gst_percent'   => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'invoice_number.unique' => 'This invoice number already exists for your company. Please choose another number.',
            'items.min' => 'Please add at least one line item to the invoice.',
        ]);

        DB::beginTransaction();
        try {
            $totalTaxable = 0;
            $totalCgst = 0;
            $totalSgst = 0;
            $totalIgst = 0;
            $grandTotal = 0;

            $isLocal = ($validated['sale_type'] === 'LOCAL');

            $invoice = Invoice::create([
                'company_id'      => $company->id,
                'customer_id'     => $validated['customer_id'],
                'created_by'      => auth()->id(),
                'invoice_number'  => trim($validated['invoice_number']),
                'invoice_date'    => $validated['invoice_date'],
                'due_date'        => $validated['due_date'],
                'sale_type'       => $validated['sale_type'],
                'tax_mode'        => $validated['tax_mode'],
                'status'          => $validated['status'],
                'payment_method'  => $validated['payment_method'],
                'notes'           => $validated['notes'],
            ]);

            foreach ($validated['items'] as $itemData) {
                $qty = (float)$itemData['quantity'];
                $rate = (float)$itemData['rate'];
                $gstRate = (float)$itemData['gst_percent'];

                $taxable = round($qty * $rate, 2);
                $taxAmount = round($taxable * ($gstRate / 100), 2);

                $cgst = 0;
                $sgst = 0;
                $igst = 0;

                if ($isLocal) {
                    $cgst = round($taxAmount / 2, 2);
                    $sgst = round($taxAmount - $cgst, 2); // Exact split without fractional penny loss
                } else {
                    $igst = $taxAmount;
                }

                $lineTotal = $taxable + $taxAmount;

                $totalTaxable += $taxable;
                $totalCgst += $cgst;
                $totalSgst += $sgst;
                $totalIgst += $igst;
                $grandTotal += $lineTotal;

                InvoiceItem::create([
                    'invoice_id'     => $invoice->id,
                    'description'    => $itemData['description'],
                    'hsn_sac'        => $itemData['hsn_sac'] ?? null,
                    'quantity'       => $qty,
                    'unit'           => $itemData['unit'] ?? 'Pcs',
                    'rate'           => $rate,
                    'gst_percent'    => $gstRate,
                    'taxable_amount' => $taxable,
                    'cgst_amount'    => $cgst,
                    'sgst_amount'    => $sgst,
                    'igst_amount'    => $igst,
                    'line_total'     => $lineTotal,
                ]);
            }

            $invoice->update([
                'taxable_amount' => $totalTaxable,
                'cgst_amount'    => $totalCgst,
                'sgst_amount'    => $totalSgst,
                'igst_amount'    => $totalIgst,
                'total_amount'   => $grandTotal,
            ]);

            ActivityLog::log('create', 'invoice', "Created Invoice #{$invoice->invoice_number} for ₹" . number_format($grandTotal, 2), $invoice->id);
            DB::commit();

            return redirect()->route('invoices.show', $invoice)->with('success', "Invoice #{$invoice->invoice_number} created successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create invoice: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $invoice = Invoice::withTrashed()->with(['customer', 'items', 'creator'])->findOrFail($id);
        return view('invoices.show', compact('invoice'));
    }

    public function edit($id)
    {
        $invoice = Invoice::with(['customer', 'items'])->findOrFail($id);
        $company = auth()->user()->company;
        $customers = Customer::orderBy('name')->get();

        return view('invoices.edit', compact('invoice', 'company', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $company = auth()->user()->company;

        $validated = $request->validate([
            'customer_id'    => ['required', 'exists:customers,id'],
            'invoice_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('invoices', 'invoice_number')
                    ->where('company_id', $company->id)
                    ->whereNull('deleted_at')
                    ->ignore($invoice->id)
            ],
            'invoice_date'   => ['required', 'date'],
            'due_date'       => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'sale_type'      => ['required', 'in:LOCAL,CENTRAL'],
            'tax_mode'       => ['required', 'in:simple,detailed'],
            'status'         => ['required', 'in:draft,unpaid,paid,partially_paid'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'notes'          => ['nullable', 'string'],

            'items'                 => ['required', 'array', 'min:1'],
            'items.*.description'   => ['required', 'string'],
            'items.*.hsn_sac'       => ['nullable', 'string', 'max:20'],
            'items.*.quantity'      => ['required', 'numeric', 'min:0.01'],
            'items.*.unit'          => ['required', 'string', 'max:20'],
            'items.*.rate'          => ['required', 'numeric', 'min:0'],
            'items.*.gst_percent'   => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::beginTransaction();
        try {
            $isLocal = ($validated['sale_type'] === 'LOCAL');

            $invoice->update([
                'customer_id'     => $validated['customer_id'],
                'invoice_number'  => trim($validated['invoice_number']),
                'invoice_date'    => $validated['invoice_date'],
                'due_date'        => $validated['due_date'],
                'sale_type'       => $validated['sale_type'],
                'tax_mode'        => $validated['tax_mode'],
                'status'          => $validated['status'],
                'payment_method'  => $validated['payment_method'],
                'notes'           => $validated['notes'],
            ]);

            // Rebuild items cleanly
            $invoice->items()->delete();

            $totalTaxable = 0;
            $totalCgst = 0;
            $totalSgst = 0;
            $totalIgst = 0;
            $grandTotal = 0;

            foreach ($validated['items'] as $itemData) {
                $qty = (float)$itemData['quantity'];
                $rate = (float)$itemData['rate'];
                $gstRate = (float)$itemData['gst_percent'];

                $taxable = round($qty * $rate, 2);
                $taxAmount = round($taxable * ($gstRate / 100), 2);

                $cgst = 0;
                $sgst = 0;
                $igst = 0;

                if ($isLocal) {
                    $cgst = round($taxAmount / 2, 2);
                    $sgst = round($taxAmount - $cgst, 2);
                } else {
                    $igst = $taxAmount;
                }

                $lineTotal = $taxable + $taxAmount;

                $totalTaxable += $taxable;
                $totalCgst += $cgst;
                $totalSgst += $sgst;
                $totalIgst += $igst;
                $grandTotal += $lineTotal;

                InvoiceItem::create([
                    'invoice_id'     => $invoice->id,
                    'description'    => $itemData['description'],
                    'hsn_sac'        => $itemData['hsn_sac'] ?? null,
                    'quantity'       => $qty,
                    'unit'           => $itemData['unit'] ?? 'Pcs',
                    'rate'           => $rate,
                    'gst_percent'    => $gstRate,
                    'taxable_amount' => $taxable,
                    'cgst_amount'    => $cgst,
                    'sgst_amount'    => $sgst,
                    'igst_amount'    => $igst,
                    'line_total'     => $lineTotal,
                ]);
            }

            $invoice->update([
                'taxable_amount' => $totalTaxable,
                'cgst_amount'    => $totalCgst,
                'sgst_amount'    => $totalSgst,
                'igst_amount'    => $totalIgst,
                'total_amount'   => $grandTotal,
            ]);

            ActivityLog::log('update', 'invoice', "Updated Invoice #{$invoice->invoice_number} details & items.", $invoice->id);
            DB::commit();

            return redirect()->route('invoices.show', $invoice)->with('success', "Invoice #{$invoice->invoice_number} updated successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update invoice: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $num = $invoice->invoice_number;
        $invoice->delete(); // Soft delete!

        ActivityLog::log('trash', 'invoice', "Moved invoice #{$num} to Trash.", $id);
        return back()->with('warning', "Invoice #{$num} moved to Trash. You can restore it anytime from the Trash tab.");
    }

    public function restore($id)
    {
        $invoice = Invoice::onlyTrashed()->findOrFail($id);
        $invoice->restore();

        ActivityLog::log('restore', 'invoice', "Restored invoice #{$invoice->invoice_number} from Trash.", $id);
        return back()->with('success', "Invoice #{$invoice->invoice_number} restored successfully!");
    }

    public function forceDelete($id)
    {
        $invoice = Invoice::onlyTrashed()->findOrFail($id);
        $num = $invoice->invoice_number;
        $invoice->items()->delete();
        $invoice->forceDelete();

        ActivityLog::log('force_delete', 'invoice', "Permanently deleted invoice #{$num}.");
        return back()->with('danger', "Invoice #{$num} permanently deleted.");
    }

    public function print($id)
    {
        $invoice = Invoice::withTrashed()->with(['customer', 'items', 'company'])->findOrFail($id);
        $company = $invoice->company;
        return view('invoices.print', compact('invoice', 'company'));
    }
}
PHP;
writeFileSafe(__DIR__ . '/app/Http/Controllers/InvoiceController.php', $invoiceController);

echo "Controllers created successfully.\n";
