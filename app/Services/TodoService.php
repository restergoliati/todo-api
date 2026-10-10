<?php

namespace App\Services;

use App\Models\Todo;
use App\Repositories\Contracts\TodoRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TodoService
{
    public function __construct(
        private TodoRepositoryInterface $todos
    ) {
    }

    public function list(
        int $userId,
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->todos->paginateForUser(
            $userId,
            $perPage
        );
    }

    public function find(
        int $userId,
        int $todoId
    ): Todo {
        $todo = $this->todos->findForUser(
            $userId,
            $todoId
        );

        abort_if(!$todo, 404, 'Todo not found.');

        return $todo;
    }

    public function create(
        int $userId,
        array $data
    ): Todo {
        return $this->todos->create(
            $userId,
            $data
        );
    }

    public function update(
        int $userId,
        int $todoId,
        array $data
    ): Todo {
        $todo = $this->find($userId, $todoId);

        return $this->todos->update(
            $todo,
            $data
        );
    }

    public function delete(
        int $userId,
        int $todoId
    ): void {
        $todo = $this->find($userId, $todoId);

        $this->todos->delete($todo);
    }
}