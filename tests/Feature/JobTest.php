<?php

namespace Tests\Feature;

use App\Jobs\BulkCompleteTodosJob;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class JobTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_complete_job_is_dispatched(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $token = auth('api')->login($user);

        $todos = Todo::factory()
            ->count(3)
            ->create([
                'user_id' => $user->id,
                'completed' => false,
            ]);

        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer ' . $token
            )
            ->postJson(
                '/api/v1/todos/bulk-complete',
                [
                    'todo_ids' => $todos
                        ->pluck('id')
                        ->toArray(),
                ]
            );

        $response->assertStatus(202);

        Queue::assertPushed(
            BulkCompleteTodosJob::class
        );
    }
}