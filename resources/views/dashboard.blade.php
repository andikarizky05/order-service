@extends('layouts.app')

@section('title', 'Dashboard - Order Service')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('orders.create') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus"></i> New Order
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Orders</h5>
                <p class="card-text display-4">{{ $totalOrders }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">Pending Orders</h5>
                <p class="card-text display-4">{{ $pendingOrders }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Completed Orders</h5>
                <p class="card-text display-4">{{ $completedOrders }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <p class="card-text display-4">${{ number_format($totalRevenue, 2) }}</p>
            </div>
        </div>
    </div>
</div>

<h2>Recent Orders</h2>
<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User ID</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentOrders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->user_id }}</td>
                <td>${{ number_format($order->total_amount, 2) }}</td>
                <td>
                    <span class="badge bg-{{ $order->status == 'completed' ? 'success' : 'warning' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td>{{ $order->created_at->format('M d, Y') }}</td>
                <td>
                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                Order Status Distribution
            </div>
            <div class="card-body">
                <canvas id="orderStatusChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                Recent Revenue
            </div>
            <div class="card-body">
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Order Status Chart
        var statusCtx = document.getElementById('orderStatusChart').getContext('2d');
        var statusChart = new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: ['Pending', 'Completed'],
                datasets: [{
                    data: [{{ $pendingOrders }}, {{ $completedOrders }}],
                    backgroundColor: [
                        '#ffc107',
                        '#28a745'
                    ]
                }]
            },
            options: {
                responsive: true
            }
        });
        
        // Revenue Chart (dummy data for demonstration)
        var revenueCtx = document.getElementById('revenueChart').getContext('2d');
        var revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue',
                    data: [1200, 1900, 3000, 5000, 2000, {{ $totalRevenue }}],
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(0, 123, 255, 1)'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@endsection
