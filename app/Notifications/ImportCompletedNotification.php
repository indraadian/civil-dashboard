<?php

namespace App\Notifications;

use App\Models\CivilImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly CivilImport $import,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Import Data Warga Selesai')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Proses import data warga telah selesai.')
            ->line("Total baris diproses: **{$this->import->processed_rows}** dari **{$this->import->total_rows}**")
            ->line("Baris gagal: **{$this->import->failed_rows}**")
            ->action('Lihat Dashboard', url('/'))
            ->line('Terima kasih telah menggunakan sistem kami.');
    }

    /**
     * Get the array representation of the notification (for database channel).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'import_completed',
            'import_id'      => $this->import->id,
            'filename'       => $this->import->filename,
            'total_rows'     => $this->import->total_rows,
            'processed_rows' => $this->import->processed_rows,
            'failed_rows'    => $this->import->failed_rows,
            'message'        => "Import '{$this->import->filename}' selesai: {$this->import->processed_rows} baris diproses.",
        ];
    }
}
