<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $orders = Order::with('items')->get();
        return response()->json($orders);
    }

    /**
     * Store a newly created order.
     * 
     * This method implements the consumer pattern described in docs/service-consumer.md
     * It consumes both the User Service and Product Service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'products' => 'required|array',
            'products.*.product_id' => 'required|integer',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            // Validate user exists by calling UserService
            $userResponse = Http::timeout(5)
                ->retry(3, 100)
                ->get(env('USER_SERVICE_URL') . "/api/users/{$request->user_id}");
            
            if (!$userResponse->successful()) {
                Log::warning("User Service returned error: " . $userResponse->status());
                return response()->json([
                    'error' => 'Invalid user',
                    'service' => 'user-service',
                    'status' => $userResponse->status()
                ], 400);
            }

            // Calculate total and validate products
            $totalAmount = 0;
            $products = [];

            foreach ($request->products as $item) {
                // Get product details from ProductService
                $productResponse = Http::timeout(5)
                    ->retry(3, 100)
                    ->get(env('PRODUCT_SERVICE_URL') . "/api/products/{$item['product_id']}");
                
                if (!$productResponse->successful()) {
                    Log::warning("Product Service returned error: " . $productResponse->status());
                    return response()->json([
                        'error' => "Invalid product with ID {$item['product_id']}",
                        'service' => 'product-service',
                        'status' => $productResponse->status()
                    ], 400);
                }
                
                $product = $productResponse->json();
                
                if ($product['stock'] < $item['quantity']) {
                    return response()->json(['error' => "Insufficient stock for product {$product['name']}"], 400);
                }
                
                $totalAmount += $product['price'] * $item['quantity'];
                $products[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product['price']
                ];
            }

            // Create order with transaction
            DB::beginTransaction();
            
            $order = Order::create([
                'user_id' => $request->user_id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);
            
            foreach ($products as $product) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'price' => $product['price'],
                ]);
                
                // Update product stock
                $stockResponse = Http::put(env('PRODUCT_SERVICE_URL') . "/api/products/{$product['product_id']}/stock", [
                    'quantity' => $product['quantity'],
                ]);
                
                if (!$stockResponse->successful()) {
                    throw new \Exception("Failed to update stock for product {$product['product_id']}");
                }
            }
            
            DB::commit();
            
            // Return order with items
            $order->load('items');
            return response()->json($order, 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create order: " . $e->getMessage());
            return response()->json(['error' => 'Failed to create order: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $order = Order::with('items')->find($id);
        
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }
        
        return response()->json($order);
    }

    /**
     * Get orders for a specific user.
     * 
     * This method implements the provider pattern described in docs/service-provider.md
     * It provides order data to the User Service.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrdersByUser($userId)
    {
        $orders = Order::with('items')->where('user_id', $userId)->get();
        return response()->json($orders);
    }

    /**
     * Get orders containing a specific product.
     * 
     * This method implements the provider pattern described in docs/service-provider.md
     * It provides order data to the Product Service.
     *
     * @param  int  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrdersByProduct($productId)
    {
        $orders = Order::with('items')
            ->whereHas('items', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->get();
            
        return response()->json($orders);
    }

    /**
     * Update order status to completed.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeOrder($id)
    {
        $order = Order::find($id);
        
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }
        
        if ($order->status === 'completed') {
            return response()->json(['message' => 'Order is already completed', 'order' => $order]);
        }
        
        try {
            DB::beginTransaction();
            
            $order->status = 'completed';
            $order->save();
            
            DB::commit();
            
            Log::info("Order ID {$id} marked as completed");
            
            return response()->json([
                'message' => 'Order marked as completed successfully',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to complete order: " . $e->getMessage());
            return response()->json(['error' => 'Failed to complete order'], 500);
        }
    }

    /**
     * Get all completed orders.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompletedOrders()
    {
        $completedOrders = Order::with('items')
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->get();
            
        return response()->json($completedOrders);
    }
}
