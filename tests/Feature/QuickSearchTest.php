<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuickSearchTest extends TestCase
{
    public function test_quick_search_returns_accent_insensitive_results(): void
    {
        $responseLau = $this->getJson('/tim-kiem-nhanh?q=lau');
        $responseLau->assertStatus(200);
        $responseLau->assertJsonStructure([
            'total',
            'items' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'category_name',
                    'price_formatted',
                    'old_price_formatted',
                    'has_discount',
                    'image',
                    'url',
                ]
            ]
        ]);

        $responseDau = $this->getJson('/tim-kiem-nhanh?q=lẩu');
        $responseDau->assertStatus(200);

        // Both unaccented and accented queries should return results
        $this->assertGreaterThan(0, $responseLau->json('total'));
        $this->assertGreaterThan(0, $responseDau->json('total'));
        $this->assertEquals($responseLau->json('total'), $responseDau->json('total'));
    }

    public function test_quick_search_with_empty_query_returns_empty_array(): void
    {
        $response = $this->getJson('/tim-kiem-nhanh?q=');
        $response->assertStatus(200);
        $response->assertJson([
            'total' => 0,
            'items' => [],
        ]);
    }

    public function test_menu_page_renders_with_live_search_component(): void
    {
        $response = $this->get('/thuc-don');
        $response->assertStatus(200);
        $response->assertSee('id="menuSearchInput"', false);
        $response->assertSee('id="menuSearchDropdown"', false);
        $response->assertSee('id="menuLiveSearchResultsPane"', false);
    }
}
