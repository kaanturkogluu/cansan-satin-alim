<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Bildirime tıklanınca okundu işaretleyip talep detay sayfasına yönlendirir.
 */
class NotificationController extends Controller
{
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
