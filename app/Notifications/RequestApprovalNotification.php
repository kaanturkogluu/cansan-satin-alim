<?php

namespace App\Notifications;

use App\Models\RequestForm;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Talep onay sürecinde ilgili kullanıcılara bildirim (okundu/okunmadı, tıklanınca talebe yönlendirme).
 * database + broadcast ile sayfa yenilenmeden anında düşer (ShouldBroadcastNow = kuyruk yok).
 */
class RequestApprovalNotification extends Notification implements ShouldBroadcastNow
{

    public function __construct(
        public RequestForm $requestForm,
        /** 'new_request' | 'moved_to_manager' | 'moved_to_purchasing' */
        public string $type = 'new_request'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * WebSocket ile anında gönderilecek payload (sayfa yenilenmeden navbar'da görünsün).
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    /**
     * Veritabanına yazılacak payload (liste ve tıklanınca yönlendirme için).
     */
    public function toArray(object $notifiable): array
    {
        $this->requestForm->load('user');
        $r = $this->requestForm;
        $creatorName = $r->user ? $r->user->name : __('Deleted User');

        $message = match ($this->type) {
            'new_request' => __('New request :no from :name', ['no' => $r->request_no, 'name' => $creatorName]),
            'moved_to_manager' => __('Request :no approved by chief, pending your review', ['no' => $r->request_no]),
            'moved_to_purchasing' => __('Request :no approved by manager, pending your review', ['no' => $r->request_no]),
            default => $r->request_no . ' - ' . $r->title,
        };

        return [
            'type' => $this->type,
            'request_form_id' => $r->id,
            'request_no' => $r->request_no,
            'title' => $r->title,
            'message' => $message,
            'created_at_iso' => $r->created_at->toIso8601String(),
        ];
    }
}
