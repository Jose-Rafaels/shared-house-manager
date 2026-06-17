<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\ShoppingItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoppingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_shopping_item_can_be_created(): void
    {
        $this->post(route('shopping.store'), [
            'name' => 'Detergent',
            'priority' => 'High',
        ])->assertRedirect();

        $this->assertDatabaseHas('shopping_items', ['name' => 'Detergent']);
    }

    public function test_shopping_item_can_be_updated(): void
    {
        $item = ShoppingItem::query()->create([
            'name' => 'Detergent',
            'priority' => 'High',
        ]);

        $this->put(route('shopping.update', $item), [
            'name' => 'Soap',
            'priority' => 'Medium',
        ])->assertRedirect();

        $this->assertDatabaseHas('shopping_items', [
            'id' => $item->id,
            'name' => 'Soap',
            'priority' => 'Medium',
        ]);
    }

    public function test_shopping_item_can_be_deleted(): void
    {
        $item = ShoppingItem::query()->create([
            'name' => 'Detergent',
            'priority' => 'High',
        ]);

        $this->delete(route('shopping.destroy', $item))->assertRedirect();

        $this->assertDatabaseMissing('shopping_items', ['id' => $item->id]);
    }
}