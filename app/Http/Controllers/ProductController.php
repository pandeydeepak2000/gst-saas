<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $query = Product::where('company_id', $companyId);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('hsn_sac', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $products = $query->latest('id')->paginate(15);
        $totalProducts = Product::where('company_id', $companyId)->count();

        return view('products.index', compact('products', 'totalProducts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'hsn_sac'    => ['nullable', 'string', 'max:20'],
            'unit'        => ['required', 'string', 'max:20'],
            'rate'        => ['required', 'numeric', 'min:0'],
            'gst_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        $product = Product::create($validated);

        ActivityLog::log('create', 'product', "Added product '{$product->name}' with rate ₹" . number_format($product->rate, 2), $product->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'product' => $product]);
        }

        return back()->with('success', "Product '{$product->name}' created successfully!");
    }

    public function update(Request $request, Product $product)
    {
        $user = auth()->user();
        if ($product->company_id !== $user->company_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized product access: This item does not belong to your company.');
        }

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'hsn_sac'    => ['nullable', 'string', 'max:20'],
            'unit'        => ['required', 'string', 'max:20'],
            'rate'        => ['required', 'numeric', 'min:0'],
            'gst_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        unset($validated['company_id']);

        $product->update($validated);
        ActivityLog::log('update', 'product', "Updated product '{$product->name}' details.", $product->id);

        return back()->with('success', "Product '{$product->name}' updated successfully!");
    }

    public function destroy(Product $product)
    {
        $user = auth()->user();
        if ($product->company_id !== $user->company_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized product access: This item does not belong to your company.');
        }

        $name = $product->name;
        $product->delete();

        ActivityLog::log('delete', 'product', "Deleted product '{$name}'.");
        return back()->with('success', "Product '{$name}' deleted successfully.");
    }

    /**
     * JSON search endpoint for dynamic autocomplete on Invoice builder
     */
    public function apiSearch(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermission('invoices') && !$user->hasPermission('products')) {
            abort(403, 'Unauthorized autocomplete access.');
        }

        $query = Product::where('company_id', $user->company_id)->where('is_active', true);

        if ($request->filled('q')) {
            $s = $request->q;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('hsn_sac', 'like', "%{$s}%");
            });
        }

        $items = $query->take(20)->get(['id', 'name', 'hsn_sac', 'unit', 'rate', 'gst_percent']);
        return response()->json($items);
    }
}