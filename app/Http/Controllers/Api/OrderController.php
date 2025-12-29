<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
        ]);

        $order = Order::create([
            'user_id' => $request->user_id,
            'product_id' => $request->product_id,
            'status' => 'pending',
        ]);

        return response()->json($order, 201);
    }

    public function show($id)
    {
        return Order::with(['user', 'product'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return response()->json($order);
    }
}
