<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderViewController extends Controller
{
    /**
     * Display the dashboard with order statistics.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $totalRevenue = Order::sum('total_amount');
        
        $recentOrders = Order::with('items')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        return view('dashboard', compact(
            'totalOrders', 
            'pendingOrders', 
            'completedOrders', 
            'totalRevenue', 
            'recentOrders'
        ));
    }

    /**
     * Display a listing of all orders.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $orders = Order::with('items')->orderBy('created_at', 'desc')->paginate(10);
        return view('orders.index', compact('orders'));
    }

    /**
     * Display the specified order.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $order = Order::with('items')->findOrFail($id);
        
        // Get user details from User Service
        try {
            $userResponse = Http::get(env('USER_SERVICE_URL') . "/api/users/{$order->user_id}");
            if ($userResponse->successful()) {
                $user = $userResponse->json();
            } else {
                $user = ['name' => 'Unknown User', 'email' => 'unknown@example.com'];
            }
        } catch (\Exception $e) {
            $user = ['name' => 'Unknown User', 'email' => 'unknown@example.com'];
        }
        
        // Get product details for each order item
        $items = [];
        foreach ($order->items as $item) {
            try {
                $productResponse = Http::get(env('PRODUCT_SERVICE_URL') . "/api/products/{$item->product_id}");
                if ($productResponse->successful()) {
                    $product = $productResponse->json();
                    $items[] = [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $product['name'],
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->quantity * $item->price
                    ];
                } else {
                    $items[] = [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => 'Unknown Product',
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->quantity * $item->price
                    ];
                }
            } catch (\Exception $e) {
                $items[] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => 'Unknown Product',
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->quantity * $item->price
                ];
            }
        }
        
        return view('orders.show', compact('order', 'user', 'items'));
    }

    /**
     * Show the form for creating a new order.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Get users from User Service
        try {
            $usersResponse = Http::get(env('USER_SERVICE_URL') . "/api/users");
            if ($usersResponse->successful()) {
                $users = $usersResponse->json();
            } else {
                $users = [];
            }
        } catch (\Exception $e) {
            $users = [];
        }
        
        // Get products from Product Service
        try {
            $productsResponse = Http::get(env('PRODUCT_SERVICE_URL') . "/api/products");
            if ($productsResponse->successful()) {
                $products = $productsResponse->json();
            } else {
                $products = [];
            }
        } catch (\Exception $e) {
            $products = [];
        }
        
        return view('orders.create', compact('users', 'products'));
    }

    /**
     * Display a listing of completed orders.
     *
     * @return \Illuminate\View\View
     */
    public function completedOrders()
    {
        $orders = Order::with('items')
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->paginate(10);
            
        return view('orders.completed', compact('orders'));
    }
}
