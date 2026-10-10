<?php

namespace App\Jobs;

use App\Models\JobStatus;
use App\Repositories\Contracts\TodoRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BulkCompleteTodosJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $jobStatusId,
        public int $userId,
        public array $todoIds
    ) {
    }

    public function handle(
        TodoRepositoryInterface $todos
    ): void {
        $status = JobStatus::findOrFail(
            $this->jobStatusId
        );

        $status->update([
            'status' => 'processing',
            'progress' => 10,
            'message' => 'Processing todos.',
            'started_at' => now(),
        ]);

        try {
            $updated = $todos->completeMany(
                $this->userId,
                $this->todoIds
            );

            $status->update([
                'status' => 'completed',
                'progress' => 100,
                'message' => 'Todos completed successfully.',
                'result' => [
                    'updated' => $updated,
                ],
                'finished_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            $status->update([
                'status' => 'failed',
                'progress' => 100,
                'error' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            throw $exception;
        }
    }
}