<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'engineer') {
            return redirect()->route('requests.index');
        } elseif ($user->role === 'chief') {
            return redirect()->route('requests.approvals');
        } elseif ($user->role === 'manager') {
            return redirect()->route('requests.approvals');
        } elseif ($user->role === 'purchasing') {
            return redirect()->route('requests.approvals'); // Or a specific purchasing list
        } elseif ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return abort(403);
    }
}
