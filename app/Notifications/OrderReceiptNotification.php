<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class OrderReceiptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Order $order,
    ) {}

    public static function sendFor(Order $order): void
    {
        NotificationFacade::route('mail', $order->customer_email)
            ->notify(new self($order));
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        $amount = Money::format($order->total_minor, $order->currency);
        $paidAt = ($order->paid_at ?? $order->created_at)?->format('F j, Y');
        $orderUrl = route('orders.show', $order->order_number);
        $supportEmail = (string) config('brightside.support_email', 'support@brightside.test');

        return (new MailMessage)
            ->subject('Your Brightside receipt — '.$order->order_number)
            ->view('mail.order-receipt', [
                'order' => $order,
                'amount' => $amount,
                'paidAt' => $paidAt,
                'orderUrl' => $orderUrl,
                'supportEmail' => $supportEmail,
            ])
            ->text('mail.order-receipt-text', [
                'order' => $order,
                'amount' => $amount,
                'paidAt' => $paidAt,
                'orderUrl' => $orderUrl,
                'supportEmail' => $supportEmail,
            ]);
    }
}
