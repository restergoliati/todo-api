<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Services\TodoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\BulkCompleteTodoRequest;
use App\Models\JobStatus;
use App\Jobs\BulkCompleteTodosJob;
use Illuminate\Support\Str;

class TodoController extends BaseApiController
{
    public function __construct(
        private TodoService $todoService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $todos = $this->todoService->list(
            auth('api')->id(),
            (int) $request->get('per_page', 15)
        );

        return $this->success(
            $todos->items(),
            'Todos retrieved successfully.',
            200,
            [
                'current_page' => $todos->currentPage(),
                'last_page' => $todos->lastPage(),
                'per_page' => $todos->perPage(),
                'total' => $todos->total(),
            ]
        );
    }

    public function store(
        StoreTodoRequest $request
    ): JsonResponse {
        $todo = $this->todoService->create(
            auth('api')->id(),
            $request->validated()
        );

        return $this->success(
            $todo,
            'Todo created successfully.',
            201
        );
    }

    public function show(int $todo): JsonResponse
    {
        $result = $this->todoService->find(
            auth('api')->id(),
            $todo
        );

        return $this->success(
            $result,
            'Todo retrieved successfully.'
        );
    }

    public function update(
        UpdateTodoRequest $request,
        int $todo
    ): JsonResponse {
        $result = $this->todoService->update(
            auth('api')->id(),
            $todo,
            $request->validated()
        );

        return $this->success(
            $result,
            'Todo updated successfully.'
        );
    }

    public function destroy(int $todo): JsonResponse
    {
        $this->todoService->delete(
            auth('api')->id(),
            $todo
        );

        return $this->success(
            null,
            'Todo deleted successfully.'
        );
    }

    public function bulkComplete(
        BulkCompleteTodoRequest $request
    ): JsonResponse {
        $jobStatus = JobStatus::create([
            'id' => (string) Str::uuid(),
            'user_id' => auth('api')->id(),
            'type' => 'bulk_complete_todos',
            'status' => 'queued',
            'progress' => 0,
            'message' => 'Job queued.',
        ]);

        BulkCompleteTodosJob::dispatch(
            $jobStatus->id,
            auth('api')->id(),
            $request->validated('todo_ids')
        );

        return $this->success(
            [
                'job_id' => $jobStatus->id,
                'status' => $jobStatus->status,
            ],
            'Bulk completion job queued.',
            202
        );
    }
}