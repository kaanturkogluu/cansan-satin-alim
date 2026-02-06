<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * WebSocket (Reverb) kapalıyken broadcast hatalarının uygulamayı çökertmemesi için
 * güvenli broadcast yardımcısı. Hata durumunda log yazar, exception fırlatmaz.
 */
class SafeBroadcast
{
    /**
     * Event'i broadcast etmeye çalışır; Reverb erişilemezse sadece loglar, exception fırlatmaz.
     *
     * @param  object  $event  ShouldBroadcast implement eden event
     * @return bool Broadcast gönderildiyse true, hata yutulduysa false
     */
    public static function send(object $event): bool
    {
        try {
            broadcast($event);

            return true;
        } catch (\Throwable $e) {
            Log::channel('stack')->warning('Broadcast failed (Reverb/socket may be down)', [
                'event' => get_class($event),
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
