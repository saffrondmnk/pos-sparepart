@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Products</h1>
                <p class="mt-1 text-sm text-gray-600">Manage your product inventory</p>
            </div>
            <a href="{{ route('products.create') }}" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center">
                Add Product
            </a>
        </div>

        <div class="mb-4">
            <form method="GET" action="{{ route('products.index') }}" class="flex flex-col sm:flex-row gap-2" id="filter-form">
                <input type="text" name="search" placeholder="Search products..." value="{{ request('search') }}"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <select name="category" id="category-select" class="w-full sm:w-auto px-4 py-2 pr-8 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 appearance-none bg-white">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">
                    Search
                </button>
            </form>
        </div>

        @push('scripts')
        <script>
            document.getElementById('category-select').addEventListener('change', function() {
                document.getElementById('filter-form').submit();
            });
        </script>
        @endpush

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($products as $product)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->image)
                                <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="h-12 w-12 object-cover rounded">
                                @else
                                <div class="h-12 w-12 bg-gray-200 rounded flex items-center justify-center">
                                    <span class="text-gray-400">No Image</span>
                                </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <a href="{{ route('products.sku.edit', $product) }}" class="text-blue-600 hover:text-blue-900 hover:underline" title="Click to edit SKU">
                                    {{ $product->sku }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->category->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ format_currency($product->price) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->isLowStock())
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    {{ $product->stock_quantity }} (Low)
                                </span>
                                @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ $product->stock_quantity }}
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('products.edit', $product->id) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                    <a href="{{ route('products.stock.show', $product->id) }}" class="text-purple-600 hover:text-purple-900">Stock</a>
                                    <a href="{{ route('products.stock.history', $product->id) }}" class="text-green-600 hover:text-green-900">History</a>
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No products found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
