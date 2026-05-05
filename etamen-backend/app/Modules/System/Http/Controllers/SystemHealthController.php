<?php

namespace App\Modules\System\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Notifications\Infrastructure\Models\SchedulerRun;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SystemHealthController extends ApiController
{
    public function health()
    {
        return $this->success([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', 'local'),
            'environment' => app()->environment(['production', 'staging']) ? app()->environment() : 'non-production',
        ], 'System health.');
    }

    public function readiness()
    {
        $checks = [
            'database' => $this->databaseCheck(),
            'cache' => $this->cacheCheck(),
            'private_storage' => $this->privateStorageCheck(),
            'scheduler' => $this->schedulerCheck(),
        ];

        $ready = collect($checks)->every(fn (array $check): bool => $check['status'] === 'ok' || $check['status'] === 'warning');

        return $this->success([
            'ready' => $ready,
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
        ], $ready ? 'System readiness.' : 'System readiness has failed checks.', $ready ? 200 : 503);
    }

    private function databaseCheck(): array
    {
        try {
            DB::select('select 1');

            return ['status' => 'ok'];
        } catch (Throwable) {
            return ['status' => 'failed', 'message' => 'Database check failed.'];
        }
    }

    private function cacheCheck(): array
    {
        try {
            $key = 'system-readiness-'.bin2hex(random_bytes(4));
            Cache::put($key, 'ok', 30);
            $ok = Cache::get($key) === 'ok';
            Cache::forget($key);

            return ['status' => $ok ? 'ok' : 'failed'];
        } catch (Throwable) {
            return ['status' => 'failed', 'message' => 'Cache check failed.'];
        }
    }

    private function privateStorageCheck(): array
    {
        try {
            $disk = Storage::disk('medical_private');
            $path = 'readiness/.probe-'.bin2hex(random_bytes(4));
            $disk->put($path, 'ok');
            $ok = $disk->exists($path);
            $disk->delete($path);

            return ['status' => $ok ? 'ok' : 'failed'];
        } catch (Throwable) {
            return ['status' => 'failed', 'message' => 'Private storage check failed.'];
        }
    }

    private function schedulerCheck(): array
    {
        $lastRun = SchedulerRun::query()->latest('started_at')->first();

        if (! $lastRun) {
            return [
                'status' => 'warning',
                'message' => 'No scheduler run has been recorded yet.',
            ];
        }

        return [
            'status' => $lastRun->status->value === 'failed' ? 'warning' : 'ok',
            'last_job_name' => $lastRun->job_name,
            'last_status' => $lastRun->status->value,
            'last_started_at' => $lastRun->started_at?->toISOString(),
        ];
    }
}
