@extends('layouts.app')

@section('title', 'Orders - Order Service')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Orders</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('orders.create') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus"></i> New Order
            </a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-table me-1"></i>
        All Orders
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User ID</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->user_id }}</td>
                        <td>{{ $order->items->count() }}</td>
                        <td>${{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $order->status == 'completed' ? 'success' : 'warning' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                        <td>
                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
