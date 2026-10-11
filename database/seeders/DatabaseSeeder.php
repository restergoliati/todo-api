<?php

namespace Database\Seeders;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->count(3)
            ->create()
            ->each(function (User $user) {
                Todo::factory()
                    ->count(5)
                    ->create([
                        'user_id' => $user->id,
                    ]);
            });
    }
}