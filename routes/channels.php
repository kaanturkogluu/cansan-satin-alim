<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Mühendisin kendi talep listesi (Taleplerim) anlık güncelleme
Broadcast::channel('user.{id}.requests', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Şef: sadece kendi departmanındaki bekleyen talepler
Broadcast::channel('approvals.chief.{departmentId}', function ($user, $departmentId) {
    return $user->role === 'chief' && (int) $user->department_id === (int) $departmentId;
});

// Müdür: tüm pending_manager talepler
Broadcast::channel('approvals.manager', function ($user) {
    return $user->role === 'manager';
});

// Satın alma: tüm pending_purchasing talepler
Broadcast::channel('approvals.purchasing', function ($user) {
    return $user->role === 'purchasing';
});
