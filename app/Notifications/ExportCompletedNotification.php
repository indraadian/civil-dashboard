<?php

namespace App\Notifications;

use App\Models\CivilExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExportCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly CivilExport $export,
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
        $mail = (new MailMessage)
            ->subject('Export Data Warga Siap Diunduh')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('File export data warga sudah siap.')
            ->line("Total baris: **{$this->export->total_rows}**")
            ->line('Link download akan kadaluarsa dalam **24 jam**.');

        if ($this->export->isDownloadable()) {
            $mail->action('Unduh Sekarang', $this->export->download_url);
        }

        return $mail->line('Terima kasih telah menggunakan sistem kami.');
    }

    /**
     * Get the array representation of the notification (for database channel).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'export_completed',
            'export_id'    => $this->export->id,
            'filename'     => $this->export->filename,
            'total_rows'   => $this->export->total_rows,
            'download_url' => $this->export->download_url,
            'expires_at'   => $this->export->expires_at?->toIso8601String(),
            'message'      => "Export '{$this->export->filename}' siap diunduh.",
        ];
    }
}
