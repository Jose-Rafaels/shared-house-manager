<?php

namespace Tests\Feature;

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

        $this->assertDatabaseHas('shopping_items', [
            'name' => 'Detergent',
            'priority' => 'High',
            'is_purchased' => false,
        ]);
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

    public function test_shopping_item_can_be_toggled_purchased(): void
    {
        $item = ShoppingItem::query()->create([
            'name' => 'Detergent',
            'priority' => 'High',
        ]);

        $this->assertFalse($item->fresh()->is_purchased);

        $this->patch(route('shopping.togglePurchased', $item))->assertRedirect();
        $this->assertTrue($item->fresh()->is_purchased);

        $this->patch(route('shopping.togglePurchased', $item->fresh()))->assertRedirect();
        $this->assertFalse($item->fresh()->is_purchased);
    }
}
