<?php

declare(strict_types=1);

namespace Monorail\Exports\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Monorail\Exports\Exporter;
use Monorail\Models\Export;

final class ExportReadyNotification extends Notification
{
    use Queueable;

    /**
     * @param  class-string<Exporter>  $exporter
     */
    public function __construct(
        public readonly int $exportId,
        public readonly string $exporter,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(mixed $notifiable): array
    {
        /** @var Export|null $export */
        $export = Export::query()->find($this->exportId);

        /** @var class-string<Exporter> $exporter */
        $exporter = $this->exporter;

        return [
            'type' => 'monorail.export.ready',
            'export_id' => $this->exportId,
            'file_name' => $export?->file_name,
            'body' => $export !== null ? $exporter::getCompletedNotificationBody($export) : 'Export ready.',
            'download_url' => $export !== null
                ? url(config('app.url').'/', '').'/admin/exports/'.$export->getKey().'/download'
                : null,
        ];
    }
}
