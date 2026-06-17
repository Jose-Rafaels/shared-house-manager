<?php

namespace Tests\Feature;

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_be_created_and_deleted(): void
    {
        $this->post(route('members.store'), [
            'name' => 'Alice',
            'phone' => '08123456789',
        ])->assertRedirect();

        $this->assertDatabaseHas('members', ['name' => 'Alice', 'phone' => '08123456789']);

        $member = Member::query()->first();
        $this->delete(route('members.destroy', $member))->assertRedirect();

        $this->assertDatabaseMissing('members', ['id' => $member->id]);
    }
}
