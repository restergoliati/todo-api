<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate(): arrays
    {
        $user = User::factory()->create();

        $token = auth('api')->login($user);

        return [$user, $token];
    }

    public function test_authenticated_user_can_create_todo(): void
    {
        [$user, $token] = $this->authenticate();

        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer ' . $token
            )
            ->postJson('/api/v1/todos', [
                'title' => 'Test Todo',
                'description' => 'Testing creation',
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath(
                'success',
                true
            );

        $this->assertDatabaseHas(
            'todos',
            [
                'user_id' => $user->id,
                'title' => 'Test Todo',
            ]
        );
    }

    public function test_user_can_only_see_own_todos(): void
    {
        [$user, $token] = $this->authenticate();

        $otherUser = User::factory()->create();

        $todo = Todo::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer ' . $token
            )
            ->getJson(
                '/api/v1/todos/' . $todo->id
            );

        $response->assertStatus(404);
    }

    public function test_user_can_update_own_todo(): void
    {
        [$user, $token] = $this->authenticate();

        $todo = Todo::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer ' . $token
            )
            ->putJson(
                '/api/v1/todos/' . $todo->id,
                [
                    'title' => 'Updated Todo',
                ]
            );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.title',
                'Updated Todo'
            );
    }

    public function test_user_can_delete_own_todo(): void
    {
        [$user, $token] = $this->authenticate();

        $todo = Todo::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer ' . $token
            )
            ->deleteJson(
                '/api/v1/todos/' . $todo->id
            );

        $response->assertOk();

        $this->assertDatabaseMissing(
            'todos',
            [
                'id' => $todo->id,
            ]
        );
    }

    public function test_todo_requires_authentication(): void
    {
        $response = $this->getJson(
            '/api/v1/todos'
        );

        $response->assertStatus(401);
    }
}