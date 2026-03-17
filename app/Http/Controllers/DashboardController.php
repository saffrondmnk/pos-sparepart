<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $todaySales = Transaction::whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total_amount');
        
        $todayTransactions = Transaction::whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();
        
        $totalProducts = Product::count();
        
        $lowStockProducts = Product::whereRaw('stock_quantity <= min_stock_level')
            ->count();
        
        $totalRevenue = Transaction::where('status', 'completed')
            ->sum('total_amount');
        
        $recentTransactions = Transaction::with('user')
            ->where('status', 'completed')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.index', compact(
            'todaySales',
            'todayTransactions',
            'totalProducts',
            'lowStockProducts',
            'totalRevenue',
            'recentTransactions'
        ));
    }
}
