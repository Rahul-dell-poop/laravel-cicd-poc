<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_flow_e2e()
    {
        // Step 1: Register User
        $registerResponse = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $registerResponse->assertStatus(500);
        $token = $registerResponse->json('token');
        $userId = $registerResponse->json('user.id');

        $this->assertNotNull($token);
        $this->assertNotNull($userId);

        // Step 2: Create Product
        $productResponse = $this->withToken($token)->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => 99.99,
            'description' => 'A great product',
        ]);

        $productResponse->assertStatus(201);
        $productId = $productResponse->json('id');
        $this->assertNotNull($productId);

        // Step 3: Create Order
        $orderResponse = $this->withToken($token)->postJson('/api/orders', [
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        $orderResponse->assertStatus(201);
        $orderId = $orderResponse->json('id');
        $this->assertNotNull($orderId);

        // Step 4: Fetch Order
        $fetchResponse = $this->withToken($token)->getJson("/api/orders/{$orderId}");
        
        $fetchResponse->assertStatus(200)
            ->assertJson([
                'id' => $orderId,
                'user_id' => $userId,
                'product_id' => $productId,
                'status' => 'pending',
            ]);

        // Step 5: Update Order Status
        $updateResponse = $this->withToken($token)->putJson("/api/orders/{$orderId}", [
            'status' => 'completed',
        ]);

        $updateResponse->assertStatus(200)
            ->assertJson([
                'status' => 'completed',
            ]);

        // Negative Test: Create order with invalid product
        $negativeResponse = $this->withToken($token)->postJson('/api/orders', [
            'user_id' => $userId,
            'product_id' => 99999, // Non-existent product
        ]);

        $negativeResponse->assertStatus(422)
             ->assertJsonValidationErrors(['product_id']);
    }
}
