<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifikasis = Notifikasi::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')->paginate(20);
        return view('notifikasi.index', compact('notifikasis'));
    }

    public function markAsRead(Notifikasi $notifikasi)
    {
        if ($notifikasi->user_id !== auth()->id()) { abort(403); }
        $notifikasi->markAsRead();

        if ($notifikasi->link) {
            return redirect($notifikasi->link);
        }
        return redirect()->back();
    }

    public function markAllAsRead()
    {
        Notifikasi::where('user_id', auth()->id())->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
        return redirect()->back()->with('success', 'Semua notifikasi telah dibaca.');
    }
}
