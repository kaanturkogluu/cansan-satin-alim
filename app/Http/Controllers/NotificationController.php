<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Bildirime tıklanınca okundu işaretleyip talep detay sayfasına yönlendirir.
 * Polling için okunmamış bildirim listesi JSON.
 */
class NotificationController extends Controller
{
    /**
     * Okunmamış bildirimleri JSON döndür (polling / WebSocket yedeği).
     */
    public function unreadJson(Request $request)
    {
        $user = Auth::user();
        $notifications = $user->unreadNotifications()->take(20)->get();

        return response()->json([
            'notifications' => $notifications->map(fn ($n) => [
                'id' => $n->id,
                'data' => $n->data,
                'created_at' => $n->created_at?->toIso8601String(),
            ])->values()->all(),
        ]);
    }

    /**
     * Bildirimi okundu işaretle ve ilgili talebin detay sayfasına yönlendir.
     */
    public function readAndRedirect(Request $request, string $id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        $requestFormId = $notification->data['request_form_id'] ?? null;
        if (! $requestFormId) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('requests.show', $requestFormId);
    }
}
