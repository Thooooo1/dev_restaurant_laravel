<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_addcart_ajax_returns_json_response(): void
    {
        $product = \App\Models\Product::first();
        if ($product) {
            $response = $this->postJson('/addcart/' . $product->id, [
                'ajax' => 1,
                'qty' => 2,
            ]);

            $response->assertStatus(200);
            $response->assertJson([
                'success' => true,
            ]);
            $this->assertGreaterThanOrEqual(2, $response->json('count'));
        }
    }
}
