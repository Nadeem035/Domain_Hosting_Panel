<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RenewalReminder extends Notification
{
    use Queueable;

    /**
     * @param  array{
     *     service_id: int,
     *     domain: string,
     *     client: string|null,
     *     tier: string,
     *     expiry_date: string,
     *     days_left: int,
     *     client_price: float,
     *     currency: string,
     *     url: string,
     *     title: string,
     *     message: string,
     * }  $data
     */
    public function __construct(public array $data)
    {
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        $prefs = $notifiable->notification_preferences ?? [];

        $emailEnabled = (bool) ($prefs['email_enabled'] ?? true);

        if ($emailEnabled && $notifiable->wantsEmailFor($this->data['tier'])) {
            return ['database', 'mail'];
        }

        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->data;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $days = $this->data['days_left'];

        $expiryLine = $days >= 0
            ? "It expires in {$days} day".($days === 1 ? '' : 's').' — on '.$this->data['expiry_date'].'.'
            : 'It expired '.abs($days).' day'.(abs($days) === 1 ? '' : 's').' ago ('.$this->data['expiry_date'].').';

        return (new MailMessage)
            ->subject('['.ucfirst($this->data['tier']).'] Renewal reminder — '.$this->data['domain'])
            ->greeting('Hi '.$notifiable->name.',')
            ->line('A renewal needs your attention:')
            ->line('**'.$this->data['domain'].'** — '.$this->data['tier'].' tier.')
            ->line($this->data['client'] ? 'Client: '.$this->data['client'] : null)
            ->line($expiryLine)
            ->line('Client price: '.number_format($this->data['client_price'], 2).' '.$this->data['currency'])
            ->action('View service', $this->data['url'])
            ->line('You can turn these emails off in Settings → Email reminders.');
    }
}