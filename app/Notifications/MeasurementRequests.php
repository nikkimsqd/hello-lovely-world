<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MeasurementRequests extends Notification
{
    use Queueable;

    private $measurementrequest;
    private $boutique;
    private $transactionID;
    private $transactionType;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($transactionID, $boutique, $transactionType)
    {
        $this->transactionID = $transactionID;
        $this->boutique = $boutique;
        $this->transactionType = $transactionType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'text' => $this->boutique['boutiqueName'].' requests you to submit your measurements.',
            'transactionID' => $this->transactionID,
            'transactionType' => $this->transactionType
        ];
    }
}
