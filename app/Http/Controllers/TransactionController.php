<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('user');
        
        if ($request->has('search') && $request->search) {
            $query->where('transaction_number', 'like', '%' . $request->search . '%');
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $transactions = $query->latest()->paginate(20);
        
        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $categories = Category::with('products')->get();
        return view('transactions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|string',
            'payment_method' => 'required|in:cash,card,digital',
        ]);

        $items = json_decode($validated['items'], true);
        
        if (!is_array($items) || empty($items)) {
            return back()->with('error', 'Cart is empty or invalid.');
        }

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            foreach ($items as $item) {
                $product = Product::find($item['id']);
                if (!$product || $product->stock_quantity < $item['quantity']) {
                    DB::rollBack();
                    return back()->with('error', 'Insufficient stock for product: ' . ($product ? $product->name : 'Unknown'));
                }
                $totalAmount += $product->price * $item['quantity'];
            }

            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'transaction_number' => Transaction::generateTransactionNumber(),
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'status' => 'completed',
            ]);

            foreach ($items as $item) {
                $product = Product::find($item['id']);
                $quantity = $item['quantity'];
                $unitPrice = $product->price;
                $subtotal = $unitPrice * $quantity;

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $oldQuantity = $product->stock_quantity;
                $product->decrement('stock_quantity', $quantity);

                $product->stockHistories()->create([
                    'user_id' => Auth::id(),
                    'quantity_before' => $oldQuantity,
                    'quantity_after' => $oldQuantity - $quantity,
                    'quantity_changed' => $quantity,
                    'type' => 'subtract',
                    'notes' => 'Transaction #' . $transaction->transaction_number,
                ]);
            }

            DB::commit();

            return redirect()->route('transactions.show', $transaction->id)->with('success', 'Transaction completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transaction failed: ' . $e->getMessage());
        }
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'items.product']);
        return view('transactions.show', compact('transaction'));
    }

    public function getProducts(Request $request)
    {
        $categoryId = $request->category_id;
        
        if ($categoryId) {
            $products = Product::where('category_id', $categoryId)
                ->where('stock_quantity', '>', 0)
                ->get();
        } else {
            $products = Product::where('stock_quantity', '>', 0)->get();
        }
        
        return response()->json($products);
    }
}
