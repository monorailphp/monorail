<?php

declare(strict_types=1);

namespace Monorail\Imports\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Monorail\Imports\Importer;
use Monorail\Imports\Notifications\ImportCompletedNotification;
use Monorail\Models\Import;

/**
 * Marks the import completed.
 */
final class CompleteImportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  class-string<Importer>  $importer
     */
    public function __construct(
        public readonly int $importId,
        public readonly string $importer,
    ) {}

    public function handle(): void
    {
        /** @var Import $import */
        $import = Import::query()->findOrFail($this->importId);
        $import->completed_at = now();
        $import->save();

        $this->notifyUser($import);
    }

    private function notifyUser(Import $import): void
    {
        if ($import->user_id === null) {
            return;
        }

        $userModel = config('auth.providers.users.model');
        if (! is_string($userModel) || ! class_exists($userModel)) {
            return;
        }

        $user = $userModel::query()->find($import->user_id);
        if ($user === null || ! method_exists($user, 'notify')) {
            return;
        }

        $user->notify(new ImportCompletedNotification(
            $import->getKey(),
            $this->importer,
        ));
    }
}
