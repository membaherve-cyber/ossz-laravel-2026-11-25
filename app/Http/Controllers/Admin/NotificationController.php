<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $this->guard();

        return view('admin.notifications', [
            'notifications' => Notification::with('order')->orderByDesc('created_at')->limit(300)->get(),
        ]);
    }

    public function destroy(Notification $notification)
    {
        $this->guardAdmin();
        $id = $notification->id;
        $notification->delete();

        $user = request()->attributes->get('ossz_user');
        AuditLog::create([
            'actor_id' => $user->id ?? null,
            'actor_email' => $user->email ?? 'admin',
            'action' => 'notifications.delete',
            'detail' => "Notification #{$id} deleted from archive",
        ]);

        return redirect()->back()->with('success', is_fr() ? 'Message supprimé de l\'archive.' : 'Message deleted from archive.');
    }

    private function guard(): void
    {
        $user = request()->attributes->get('ossz_user');
        if (! \App\Support\Auth::canFulfilOrders($user->role)) {
            abort(redirect()->route('admin.dashboard')->with('error', 'Insufficient permissions.'));
        }
    }

    private function guardAdmin(): void
    {
        $user = request()->attributes->get('ossz_user');
        if (! \App\Support\Auth::isAdmin($user->role)) {
            abort(redirect()->route('admin.dashboard')->with('error', 'Admin role required.'));
        }
    }
}
