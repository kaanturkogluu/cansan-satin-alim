<?php

namespace App\Events;

use App\Models\RequestForm;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Talep listesi güncellendiğinde (ekleme/çıkarma/güncelleme) ilgili kanallara broadcast edilir.
 * ShouldBroadcastNow: Kuyruk beklemeden anında gönderilir (queue worker gerekmez).
 * action: 'added' | 'removed' | 'updated'
 */
class RequestListUpdate implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        /** Kanal adları (örn. 'approvals.chief.1', 'user.5.requests') */
        public array $channelNames,
        public string $action,
        public RequestForm $requestForm
    ) {}

    public function broadcastOn(): array
    {
        return array_map(fn (string $name) => new PrivateChannel($name), $this->channelNames);
    }

    public function broadcastAs(): string
    {
        return 'RequestListUpdate';
    }

    public function broadcastWith(): array
    {
        $this->requestForm->load('user');
        $r = $this->requestForm;

        return [
            'action' => $this->action,
            'request' => [
                'id' => $r->id,
                'request_no' => $r->request_no,
                'title' => $r->title,
                'status' => $r->status,
                'created_at' => $r->created_at->toIso8601String(),
                'user' => $r->user ? ['id' => $r->user->id, 'name' => $r->user->name] : null,
            ],
        ];
    }
}
