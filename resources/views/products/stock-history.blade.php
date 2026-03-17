@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Stock History</h1>
                <p class="mt-1 text-sm text-gray-600">
                    {{ $product->name }} (SKU: {{ $product->sku }})
                </p>
            </div>
            <a href="{{ route('products.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                Back to Products
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-gray-50 border-b">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Current Stock</p>
                        <p class="text-2xl font-bold {{ $product->isLowStock() ? 'text-red-600' : 'text-green-600' }}">
                            {{ $product->stock_quantity }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Minimum Stock Level</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $product->min_stock_level }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Category</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $product->category->name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Before</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Change</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">After</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($histories as $history)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $history->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $history->user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $history->getChangeTypeColor() }}-100 text-{{ $history->getChangeTypeColor() }}-800">
                                    {{ $history->getChangeTypeLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $history->quantity_before }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $history->type === 'add' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $history->type === 'add' ? '+' : '-' }}{{ $history->quantity_changed }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                {{ $history->quantity_after }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $history->notes ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No stock history found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4">
                {{ $histories->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
