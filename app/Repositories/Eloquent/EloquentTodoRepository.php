<?php

namespace App\Repositories\Eloquent;

use App\Models\Todo;
use App\Repositories\Contracts\TodoRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentTodoRepository implements TodoRepositoryInterface
{
    public function paginateForUser(
        int $userId,
        int $perPage = 15
    ): LengthAwarePaginator {
        return Todo::query()
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function findForUser(
        int $userId,
        int $todoId
    ): ?Todo {
        return Todo::query()
            ->where('user_id', $userId)
            ->whereKey($todoId)
            ->first();
    }

    public function create(
        int $userId,
        array $data
    ): Todo {
        return Todo::create([
            'user_id' => $userId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'completed' => $data['completed'] ?? false,
        ]);
    }

    public function update(
        Todo $todo,
        array $data
    ): Todo {
        $todo->update($data);

        return $todo->refresh();
    }

    public function delete(Todo $todo): void
    {
        $todo->delete();
    }

    public function completeMany(
        int $userId,
        array $ids
    ): int {
        return Todo::query()
            ->where('user_id', $userId)
            ->whereIn('id', $ids)
            ->update([
                'completed' => true,
            ]);
    }
}