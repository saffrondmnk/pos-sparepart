@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="max-w-full mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex gap-2 overflow-x-auto pb-2" id="category-filters">
                            <button type="button" 
                                data-category-id="all"
                                class="category-btn active px-4 py-2 rounded-full text-sm font-medium bg-blue-600 text-white whitespace-nowrap">
                                All
                            </button>
                            @foreach($categories as $category)
                            <button type="button"
                                data-category-id="{{ $category->id }}"
                                class="category-btn px-4 py-2 rounded-full text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300 whitespace-nowrap">
                                {{ $category->name }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="p-4">
                        <div id="products-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-4">
                            @foreach($categories as $category)
                                @foreach($category->products as $product)
                                    @if($product->stock_quantity > 0)
                                    <div class="product-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition" 
                                         data-category="{{ $category->id }}">
                                        <div class="aspect-square bg-gray-100 flex items-center justify-center">
                                            @if($product->image)
                                                <img src="/{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                            @else
                                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="p-3">
                                            <h3 class="font-medium text-sm text-gray-900 truncate">{{ $product->name }}</h3>
                                            <div class="flex justify-between items-center mt-2">
                                                <span class="text-lg font-bold text-blue-600">{{ format_currency($product->price) }}</span>
                                                <span class="text-xs {{ $product->stock_quantity <= $product->min_stock_level ? 'text-red-600' : 'text-green-600' }}">
                                                    Stock: {{ $product->stock_quantity }}
                                                </span>
                                            </div>
                                            <button type="button" 
                                                class="add-to-cart-btn w-full mt-2 px-3 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition"
                                                data-id="{{ $product->id }}"
                                                data-name="{{ $product->name }}"
                                                data-price="{{ $product->price }}"
                                                data-stock="{{ $product->stock_quantity }}">
                                                Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                    <div class="p-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                    </div>
                    <div class="p-4">
                        <div id="cart-items" class="space-y-3 max-h-96 overflow-y-auto mb-4">
                            <p class="text-center text-gray-500 py-8">No items in cart</p>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex justify-between text-lg font-semibold mb-4">
                                <span>Total:</span>
                                <span id="cart-total">{{ format_currency(0) }}</span>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                <select id="payment-method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="digital">Digital Payment</option>
                                </select>
                            </div>
                            
                            <button type="button" id="checkout-btn" disabled
                                class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:bg-gray-400 disabled:cursor-not-allowed font-semibold">
                                Complete Sale
                            </button>
                            
                            <button type="button" id="clear-cart-btn"
                                class="w-full mt-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Clear Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="checkout-form" method="POST" action="{{ route('transactions.store') }}" class="hidden">
    @csrf
    <input type="hidden" name="items" id="cart-items-input">
    <input type="hidden" name="payment_method" id="payment-method-input">
</form>
@endsection

@push('scripts')
<script>
// Currency configuration from Laravel config
const currencyConfig = {
    symbol: '{{ config("currency.symbol") }}',
    decimal_places: {{ config("currency.decimal_places", 0) }},
    decimal_separator: '{{ config("currency.decimal_separator", ",") }}',
    thousands_separator: '{{ config("currency.thousands_separator", ".") }}',
    locale: '{{ config("currency.locale", "id-ID") }}'
};

let cart = [];

// Format currency for display
function formatCurrency(amount) {
    const config = currencyConfig;
    const numAmount = parseFloat(amount) || 0;
    const formatted = numAmount.toLocaleString(config.locale, {
        minimumFractionDigits: config.decimal_places,
        maximumFractionDigits: config.decimal_places
    });
    return config.symbol + ' ' + formatted;
}

// Category filtering
document.querySelectorAll('.category-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const categoryId = this.getAttribute('data-category-id');
        
        // Update button styles
        document.querySelectorAll('.category-btn').forEach(function(b) {
            b.classList.remove('bg-blue-600', 'text-white');
            b.classList.add('bg-gray-200', 'text-gray-700');
        });
        this.classList.remove('bg-gray-200', 'text-gray-700');
        this.classList.add('bg-blue-600', 'text-white');
        
        // Filter products
        document.querySelectorAll('.product-card').forEach(function(card) {
            if (categoryId === 'all' || card.getAttribute('data-category') === categoryId) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

// Add to cart buttons
document.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id = parseInt(this.getAttribute('data-id'));
        const name = this.getAttribute('data-name');
        const price = parseFloat(this.getAttribute('data-price'));
        const maxStock = parseInt(this.getAttribute('data-stock'));
        
        addToCart(id, name, price, maxStock);
    });
});

// Clear cart button
document.getElementById('clear-cart-btn').addEventListener('click', function() {
    cart = [];
    renderCart();
});

// Checkout button
document.getElementById('checkout-btn').addEventListener('click', function() {
    if (cart.length === 0) {
        alert('Cart is empty');
        return;
    }
    
    document.getElementById('cart-items-input').value = JSON.stringify(cart);
    document.getElementById('payment-method-input').value = document.getElementById('payment-method').value;
    document.getElementById('checkout-form').submit();
});

function addToCart(id, name, price, maxStock) {
    const existingItem = cart.find(function(item) { return item.id === id; });
    const numPrice = parseFloat(price) || 0;
    
    if (existingItem) {
        if (existingItem.quantity < maxStock) {
            existingItem.quantity++;
        } else {
            alert('Maximum stock reached');
            return;
        }
    } else {
        cart.push({ id: id, name: name, price: numPrice, quantity: 1, maxStock: maxStock });
    }
    
    renderCart();
}

function updateQuantity(id, change) {
    const item = cart.find(function(item) { return item.id === id; });
    if (item) {
        const newQty = item.quantity + change;
        if (newQty > 0 && newQty <= item.maxStock) {
            item.quantity = newQty;
        } else if (newQty <= 0) {
            cart = cart.filter(function(i) { return i.id !== id; });
        }
        renderCart();
    }
}

function removeFromCart(id) {
    cart = cart.filter(function(item) { return item.id !== id; });
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cart-items');
    const totalEl = document.getElementById('cart-total');
    const checkoutBtn = document.getElementById('checkout-btn');
    
    if (cart.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-500 py-8">No items in cart</p>';
        totalEl.textContent = 'Rp 0';
        checkoutBtn.disabled = true;
        return;
    }
    
    let total = 0;
    let html = '';
    
    cart.forEach(function(item) {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        
        html += '<div class="flex items-center justify-between border-b border-gray-100 pb-2">' +
            '<div class="flex-1">' +
                '<h4 class="font-medium text-sm text-gray-900">' + item.name + '</h4>' +
                '<p class="text-xs text-gray-500">' + formatCurrency(item.price) + ' each</p>' +
            '</div>' +
            '<div class="flex items-center gap-2">' +
                '<button type="button" class="update-qty-btn w-6 h-6 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center" data-id="' + item.id + '" data-change="-1">-</button>' +
                '<span class="text-sm font-medium w-6 text-center">' + item.quantity + '</span>' +
                '<button type="button" class="update-qty-btn w-6 h-6 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center" data-id="' + item.id + '" data-change="1">+</button>' +
            '</div>' +
            '<div class="text-right ml-2">' +
                '<p class="font-semibold text-sm">' + formatCurrency(itemTotal) + '</p>' +
                '<button type="button" class="remove-item-btn text-xs text-red-600 hover:text-red-800" data-id="' + item.id + '">Remove</button>' +
            '</div>' +
        '</div>';
    });
    
    container.innerHTML = html;
    totalEl.textContent = formatCurrency(total);
    checkoutBtn.disabled = false;
    
    // Attach event listeners to dynamically created buttons
    container.querySelectorAll('.update-qty-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = parseInt(this.getAttribute('data-id'));
            const change = parseInt(this.getAttribute('data-change'));
            updateQuantity(id, change);
        });
    });
    
    container.querySelectorAll('.remove-item-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = parseInt(this.getAttribute('data-id'));
            removeFromCart(id);
        });
    });
}
</script>
@endpush
</content>