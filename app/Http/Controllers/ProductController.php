<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');
        
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('sku', 'like', '%' . $request->search . '%');
        }
        
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }
        
        $products = $query->latest()->paginate(20);
        $categories = Category::all();
        
        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/products'), $imageName);
            $validated['image'] = 'images/products/' . $imageName;
        }

        $validated['sku'] = 'SKU-' . strtoupper(Str::random(8));

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image && file_exists(public_path($product->image))) {
                unlink(public_path($product->image));
            }
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/products'), $imageName);
            $validated['image'] = 'images/products/' . $imageName;
        }

        $oldQuantity = $product->stock_quantity;
        $newQuantity = $validated['stock_quantity'];
        $quantityChanged = $newQuantity - $oldQuantity;

        $product->update($validated);

        if ($quantityChanged !== 0) {
            $product->stockHistories()->create([
                'user_id' => auth()->id(),
                'quantity_before' => $oldQuantity,
                'quantity_after' => $newQuantity,
                'quantity_changed' => abs($quantityChanged),
                'type' => $quantityChanged > 0 ? 'add' : 'subtract',
                'notes' => 'Stock updated via product edit',
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->image && file_exists(public_path($product->image))) {
            unlink(public_path($product->image));
        }
        $product->delete();
        
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function showUpdateStock(Product $product)
    {
        return view('products.update-stock', compact('product'));
    }

    public function updateStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'stock_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldQuantity = $product->stock_quantity;
        $newQuantity = $validated['stock_quantity'];
        $quantityChanged = $newQuantity - $oldQuantity;

        if ($quantityChanged !== 0) {
            $product->update(['stock_quantity' => $newQuantity]);

            $product->stockHistories()->create([
                'user_id' => auth()->id(),
                'quantity_before' => $oldQuantity,
                'quantity_after' => $newQuantity,
                'quantity_changed' => abs($quantityChanged),
                'type' => $quantityChanged > 0 ? 'add' : 'subtract',
                'notes' => $validated['notes'] ?? null,
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Stock updated successfully.');
    }

    public function stockHistory(Product $product)
    {
        $histories = $product->stockHistories()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('products.stock-history', compact('product', 'histories'));
    }
}
