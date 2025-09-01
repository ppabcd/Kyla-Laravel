<?php

namespace App\Jobs;

use App\Telegram\Services\TelegramBotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTelegramUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $update;

    /**
     * Create a new job instance.
     */
    public function __construct(array $update)
    {
        $this->update = $update;
        $this->queue = config('telegram.queue.name', 'telegram');
        $this->tries = config('telegram.queue.tries', 3);
        $this->timeout = config('telegram.queue.timeout', 60);
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramBotService $telegramService): void
    {
        try {
            Log::info('Processing Telegram update job', [
                'update_id' => $this->update['update_id'] ?? null,
                'job_id' => $this->job->getJobId(),
                'attempt' => $this->attempts(),
            ]);

            $telegramService->handleUpdate($this->update);

            Log::info('Telegram update job completed successfully', [
                'update_id' => $this->update['update_id'] ?? null,
                'job_id' => $this->job->getJobId(),
            ]);

        } catch (\Exception $e) {
            Log::error('Telegram update job failed', [
                'update_id' => $this->update['update_id'] ?? null,
                'job_id' => $this->job->getJobId(),
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw the exception to trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Telegram update job failed permanently', [
            'update_id' => $this->update['update_id'] ?? null,
            'job_id' => $this->job->getJobId(),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'telegram',
            'update',
            'update_id:'.($this->update['update_id'] ?? 'unknown'),
        ];
    }
}
