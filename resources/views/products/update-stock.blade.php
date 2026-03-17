@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Update Stock</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $product->name }} (SKU: {{ $product->sku }})</p>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4">
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
                    </div>
                </div>

                <form method="POST" action="{{ route('products.stock.update', $product->id) }}">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-6">
                        <div>
                            <label for="stock_quantity" class="block text-sm font-medium text-gray-700">
                                New Stock Quantity
                            </label>
                            <input type="number" name="stock_quantity" id="stock_quantity" 
                                value="{{ old('stock_quantity', $product->stock_quantity) }}" 
                                required min="0"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-sm text-gray-500">
                                Enter the new total stock quantity. The difference will be logged automatically.
                            </p>
                            @error('stock_quantity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">
                                Notes (Optional)
                            </label>
                            <textarea name="notes" id="notes" rows="3" maxlength="500"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Reason for stock change, supplier info, etc.">{{ old('notes') }}</textarea>
                            @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('products.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Update Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('products.stock.history', $product->id) }}" class="text-blue-600 hover:text-blue-900">
                View Stock History →
            </a>
        </div>
    </div>
</div>
@endsection
