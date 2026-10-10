<?php

namespace App\Repositories\Contracts;

use App\Models\Todo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TodoRepositoryInterface
{
    public function paginateForUser(
        int $userId,
        int $perPage = 15
    ): LengthAwarePaginator;

    public function findForUser(
        int $userId,
        int $todoId
    ): ?Todo;

    public function create(
        int $userId,
        array $data
    ): Todo;

    public function update(
        Todo $todo,
        array $data
    ): Todo;

    public function delete(Todo $todo): void;

    public function completeMany(
        int $userId,
        array $ids
    ): int;
}