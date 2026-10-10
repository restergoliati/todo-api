<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\TodoRepositoryInterface;
use App\Repositories\Eloquent\EloquentTodoRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TodoRepositoryInterface::class,
            EloquentTodoRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}