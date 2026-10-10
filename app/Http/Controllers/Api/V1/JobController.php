<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\JobStatus;
use Illuminate\Http\JsonResponse;

class JobController extends BaseApiController
{
    public function show(string $id): JsonResponse
    {
        $job = JobStatus::query()
            ->where('id', $id)
            ->where('user_id', auth('api')->id())
            ->first();

        if (!$job) {
            return $this->error(
                'Job not found.',
                404
            );
        }

        return $this->success(
            $job,
            'Job status retrieved successfully.'
        );
    }
}