@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Transaction Details</h1>
                <p class="mt-1 text-sm text-gray-600">Transaction #{{ $transaction->transaction_number }}</p>
            </div>
            <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                <a href="{{ route('receipt.download', $transaction->id) }}" class="flex-1 sm:flex-none px-3 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition text-center">
                    Download Receipt
                </a>
                <a href="{{ route('transactions.index') }}" class="flex-1 sm:flex-none px-3 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition text-center">
                    List Transaction
                </a>
                <a href="{{ route('transactions.create') }}" class="flex-1 sm:flex-none px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition text-center">
                    Back to New Sale
                </a>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Transaction Number</p>
                        <p class="font-semibold text-gray-900">{{ $transaction->transaction_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Date</p>
                        <p class="font-semibold text-gray-900">{{ $transaction->created_at->format('M d, Y H:i:s') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Cashier</p>
                        <p class="font-semibold text-gray-900">{{ $transaction->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Payment Method</p>
                        <p class="font-semibold text-gray-900 capitalize">{{ $transaction->payment_method }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Items</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($transaction->items as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->product->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ format_currency($item->unit_price) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ format_currency($item->subtotal) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end">
                    <div class="w-64">
                        <div class="flex justify-between py-2">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-semibold">{{ format_currency($transaction->total_amount) }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-t border-gray-200 text-lg font-bold">
                            <span>Total:</span>
                            <span class="text-green-600">{{ format_currency($transaction->total_amount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
