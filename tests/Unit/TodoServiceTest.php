<?php

namespace Tests\Unit;

use App\Models\Todo;
use App\Repositories\Contracts\TodoRepositoryInterface;
use App\Services\TodoService;
use Mockery;
use Tests\TestCase;

class TodoServiceTest extends TestCase
{
    public function test_service_creates_todo_through_repository(): void
    {
        $repository = Mockery::mock(
            TodoRepositoryInterface::class
        );

        $todo = new Todo([
            'title' => 'Test',
            'description' => 'Description',
        ]);

        $repository
            ->shouldReceive('create')
            ->once()
            ->with(
                1,
                [
                    'title' => 'Test',
                    'description' => 'Description',
                ]
            )
            ->andReturn($todo);

        $service = new TodoService($repository);

        $result = $service->create(
            1,
            [
                'title' => 'Test',
                'description' => 'Description',
            ]
        );

        $this->assertSame(
            'Test',
            $result->title
        );
    }
}