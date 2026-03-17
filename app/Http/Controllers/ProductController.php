<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    private function generateSku(Category $category, string $productName): string
    {
        // Get first letter of each word in category name
        $categoryWords = explode(' ', $category->name);
        $categoryPart = '';
        foreach ($categoryWords as $word) {
            $cleanWord = preg_replace('/[^a-zA-Z]/', '', $word);
            if (!empty($cleanWord)) {
                $categoryPart .= strtoupper(substr($cleanWord, 0, 1));
            }
        }
        
        // Get first letter of each word in product name
        $productWords = explode(' ', $productName);
        $productPart = '';
        foreach ($productWords as $word) {
            $cleanWord = preg_replace('/[^a-zA-Z]/', '', $word);
            if (!empty($cleanWord)) {
                $productPart .= strtoupper(substr($cleanWord, 0, 1));
            }
        }
        
        // Get next sequential number for this category
        $lastProduct = Product::where('category_id', $category->id)
            ->where('sku', 'like', 'SKU-' . $categoryPart . $productPart . '-%')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastProduct) {
            // Extract number from last SKU
            preg_match('/-(\d{3})$/', $lastProduct->sku, $matches);
            $nextNumber = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        } else {
            $nextNumber = 1;
        }
        
        return 'SKU-' . $categoryPart . $productPart . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

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

        $category = Category::find($validated['category_id']);
        $validated['sku'] = $this->generateSku($category, $validated['name']);

        $product = Product::create($validated);

        // Create stock history entry for new product
        $product->stockHistories()->create([
            'user_id' => auth()->id(),
            'quantity_before' => 0,
            'quantity_after' => $validated['stock_quantity'],
            'quantity_changed' => $validated['stock_quantity'],
            'type' => 'add',
            'notes' => 'Initial stock when product created',
        ]);

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

    public function allStockHistory(Request $request)
    {
        $query = StockHistory::with(['product', 'user']);
        
        if ($request->has('product') && $request->product) {
            $query->where('product_id', $request->product);
        }
        
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $histories = $query->orderBy('created_at', 'desc')->paginate(20);
        $products = Product::orderBy('name')->get();
        
        $summary = [
            'total_add' => StockHistory::where('type', 'add')->sum('quantity_changed'),
            'total_subtract' => StockHistory::where('type', 'subtract')->sum('quantity_changed'),
            'total_transactions' => StockHistory::count(),
        ];
        
        return view('products.all-stock-history', compact('histories', 'products', 'summary'));
    }

    public function editSku(Product $product)
    {
        return view('products.edit-sku', compact('product'));
    }

    public function updateSku(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
        ]);

        $oldSku = $product->sku;
        $product->update(['sku' => $validated['sku']]);

        return redirect()->route('products.index')->with('success', 'SKU updated from ' . $oldSku . ' to ' . $validated['sku']);
    }
}
