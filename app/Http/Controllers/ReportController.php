<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('user');
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $transactions = $query->where('status', 'completed')->get();
        
        $totalSales = $transactions->sum('total_amount');
        $totalTransactions = $transactions->count();
        
        return view('reports.index', compact('transactions', 'totalSales', 'totalTransactions'));
    }

    public function generateReport(Request $request)
    {
        $query = Transaction::with('user');
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $transactions = $query->where('status', 'completed')->get();
        $totalSales = $transactions->sum('total_amount');
        $settings = Setting::getSettings();
        
        $pdf = Pdf::loadView('reports.pdf', [
            'transactions' => $transactions,
            'totalSales' => $totalSales,
            'dateFrom' => $request->date_from,
            'dateTo' => $request->date_to,
            'settings' => $settings,
        ]);
        
        return $pdf->download('sales-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function downloadReceipt(Transaction $transaction)
    {
        $transaction->load(['user', 'items.product']);
        $settings = Setting::getSettings();
        
        $pdf = Pdf::loadView('transactions.receipt', compact('transaction', 'settings'));
        
        return $pdf->download('receipt-' . $transaction->transaction_number . '.pdf');
    }
}
