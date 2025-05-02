@extends('layouts.app')

@section('title', 'Order Details - Order Service')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Order #{{ $order->id }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
            @if($order->status === 'pending')
            <button id="completeOrderBtn" class="btn btn-sm btn-success">
                <i class="bi bi-check-circle"></i> Mark as Completed
            </button>
            @endif
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle me-1"></i>
                Order Information
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th style="width: 150px;">Order ID:</th>
                        <td>{{ $order->id }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge bg-{{ $order->status == 'completed' ? 'success' : 'warning' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Date:</th>
                        <td>{{ $order->created_at->format('F d, Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Total Amount:</th>
                        <td>${{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person me-1"></i>
                Customer Information
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th style="width: 150px;">User ID:</th>
                        <td>{{ $order->user_id }}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>{{ $user['name'] }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $user['email'] }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-cart me-1"></i>
        Order Items
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>{{ $item['product_id'] }}</td>
                        <td>{{ $item['product_name'] }}</td>
                        <td>${{ number_format($item['price'], 2) }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>${{ number_format($item['subtotal'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Total:</th>
                        <th>${{ number_format($order->total_amount, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-clock-history me-1"></i>
        Order Timeline
    </div>
    <div class="card-body">
        <ul class="timeline">
            <li class="timeline-item">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <h3 class="timeline-title">Order Created</h3>
                    <p>{{ $order->created_at->format('F d, Y H:i:s') }}</p>
                </div>
            </li>
            @if($order->status == 'completed')
            <li class="timeline-item">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <h3 class="timeline-title">Order Completed</h3>
                    <p>{{ $order->updated_at->format('F d, Y H:i:s') }}</p>
                </div>
            </li>
            @endif
        </ul>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 30px;
        list-style: none;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    
    .timeline-marker {
        position: absolute;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background-color: #007bff;
        left: -30px;
        top: 5px;
    }
    
    .timeline-item:not(:last-child):before {
        content: '';
        position: absolute;
        left: -23px;
        top: 20px;
        height: calc(100% - 15px);
        width: 2px;
        background-color: #e9ecef;
    }
    
    .timeline-title {
        margin-bottom: 5px;
        font-size: 1rem;
    }
</style>

@if($order->status === 'pending')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('completeOrderBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to mark this order as completed?')) {
                fetch('/api/orders/{{ $order->id }}/complete', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert('Order marked as completed successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.error || 'Unknown error occurred'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to complete the order. Please try again.');
                });
            }
        });
    });
</script>
@endif
@endsection
