<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('hsn_sac', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $products = $query->latest('id')->paginate(15);
        $totalProducts = Product::count();

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
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'hsn_sac'    => ['nullable', 'string', 'max:20'],
            'unit'        => ['required', 'string', 'max:20'],
            'rate'        => ['required', 'numeric', 'min:0'],
            'gst_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $product->update($validated);
        ActivityLog::log('update', 'product', "Updated product '{$product->name}' details.", $product->id);

        return back()->with('success', "Product '{$product->name}' updated successfully!");
    }

    public function destroy(Product $product)
    {
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
        $query = Product::where('is_active', true);

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
