@extends('layouts.app')

@section('title', 'Create Order - Order Service')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Create New Order</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-cart-plus me-1"></i>
        Order Form
    </div>
    <div class="card-body">
        <form id="orderForm" action="/api/orders" method="POST">
            <div class="mb-3">
                <label for="user_id" class="form-label">Select User</label>
                <select class="form-select" id="user_id" name="user_id" required>
                    <option value="">-- Select User --</option>
                    @foreach($users as $user)
                    <option value="{{ $user['id'] }}">{{ $user['name'] }} ({{ $user['email'] }})</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Select Products</label>
                <div id="productsList">
                    <div class="product-item card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <select class="form-select product-select" name="products[0][product_id]" required>
                                        <option value="">-- Select Product --</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product['id'] }}" data-price="{{ $product['price'] }}" data-stock="{{ $product['stock'] }}">
                                            {{ $product['name'] }} - ${{ number_format($product['price'], 2) }} ({{ $product['stock'] }} in stock)
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control quantity-input" name="products[0][quantity]" placeholder="Quantity" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="text" class="form-control subtotal-display" readonly>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger remove-product">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="button" id="addProduct" class="btn btn-outline-primary">
                    <i class="bi bi-plus-circle"></i> Add Another Product
                </button>
            </div>
            
            <div class="card mb-3">
                <div class="card-header">Order Summary</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 text-end">
                            <strong>Total Amount:</strong>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" id="totalAmount" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Create Order
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let productIndex = 0;
        
        // Add product button
        document.getElementById('addProduct').addEventListener('click', function() {
            productIndex++;
            const productItem = document.querySelector('.product-item').cloneNode(true);
            
            // Update name attributes
            productItem.querySelector('.product-select').name = `products[${productIndex}][product_id]`;
            productItem.querySelector('.quantity-input').name = `products[${productIndex}][quantity]`;
            
            // Clear values
            productItem.querySelector('.product-select').value = '';
            productItem.querySelector('.quantity-input').value = '';
            productItem.querySelector('.subtotal-display').value = '';
            
            document.getElementById('productsList').appendChild(productItem);
            
            // Add event listeners to the new product item
            addProductItemListeners(productItem);
        });
        
        // Add event listeners to the initial product item
        addProductItemListeners(document.querySelector('.product-item'));
        
        // Form submission
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const jsonData = {
                user_id: parseInt(formData.get('user_id')),
                products: []
            };
            
            // Get all product items
            const productItems = document.querySelectorAll('.product-item');
            productItems.forEach((item, index) => {
                const productId = item.querySelector('.product-select').value;
                const quantity = item.querySelector('.quantity-input').value;
                
                if (productId && quantity) {
                    jsonData.products.push({
                        product_id: parseInt(productId),
                        quantity: parseInt(quantity)
                    });
                }
            });
            
            // Send API request
            fetch('/api/orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(jsonData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.id) {
                    // Success - redirect to order details
                    window.location.href = `/orders/${data.id}`;
                } else {
                    // Error
                    alert('Error creating order: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating order. Please try again.');
            });
        });
        
        // Helper function to add event listeners to product items
        function addProductItemListeners(productItem) {
            // Remove product button
            productItem.querySelector('.remove-product').addEventListener('click', function() {
                if (document.querySelectorAll('.product-item').length > 1) {
                    productItem.remove();
                    updateTotalAmount();
                } else {
                    alert('You need at least one product.');
                }
            });
            
            // Update subtotal when product or quantity changes
            const productSelect = productItem.querySelector('.product-select');
            const quantityInput = productItem.querySelector('.quantity-input');
            const subtotalDisplay = productItem.querySelector('.subtotal-display');
            
            productSelect.addEventListener('change', function() {
                updateSubtotal(productSelect, quantityInput, subtotalDisplay);
                updateTotalAmount();
            });
            
            quantityInput.addEventListener('input', function() {
                updateSubtotal(productSelect, quantityInput, subtotalDisplay);
                updateTotalAmount();
            });
        }
        
        // Helper function to update subtotal
        function updateSubtotal(productSelect, quantityInput, subtotalDisplay) {
            if (productSelect.value && quantityInput.value) {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const price = parseFloat(selectedOption.dataset.price);
                const quantity = parseInt(quantityInput.value);
                
                if (!isNaN(price) && !isNaN(quantity) && quantity > 0) {
                    const subtotal = price * quantity;
                    subtotalDisplay.value = subtotal.toFixed(2);
                    
                    // Check stock
                    const stock = parseInt(selectedOption.dataset.stock);
                    if (quantity > stock) {
                        alert(`Warning: Only ${stock} items in stock for this product.`);
                        quantityInput.value = stock;
                        updateSubtotal(productSelect, quantityInput, subtotalDisplay);
                    }
                } else {
                    subtotalDisplay.value = '';
                }
            } else {
                subtotalDisplay.value = '';
            }
        }
        
        // Helper function to update total amount
        function updateTotalAmount() {
            const subtotals = Array.from(document.querySelectorAll('.subtotal-display'))
                .map(input => parseFloat(input.value) || 0);
            
            const total = subtotals.reduce((sum, value) => sum + value, 0);
            document.getElementById('totalAmount').value = total.toFixed(2);
        }
    });
</script>
@endsection
