<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyReportNotification extends Notification
{
    use Queueable;
    public $tasks;
    /**
     * Create a new notification instance.
     */
    public function __construct($tasks)
    {
        $this->tasks = $tasks;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $taskDetails = '';
        foreach ($this->tasks as $task) {
            $taskDetails .="Title: {$task->title},
                            Type: {$task->type},
                            Status: {$task->status},
                            Priority: {$task->priority}" ;
        }

        return (new MailMessage)
            ->subject('Your Daily Task Report')
            ->line('Here is your task report for today:')
            ->line($taskDetails)
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
