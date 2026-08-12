<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index()
    {
        return Inertia::render('Notification/Notification');
    }

    public function getNotifications(Request $request)
    {
        $data = DB::table('cms_multiple_notification as N')
            ->leftJoin('cms_multiple_recon as R', 'R.MultipleID', '=', 'N.MultipleID')
            ->where('N.user_id', auth()->id())
            ->orderBy('N.CreatedAt', 'desc')
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ]);
    }

    public function markAsRead(Request $request)
    {
        $notificationId = $request->input('notification_id');

        DB::table('cms_multiple_notification')
            ->where('RecID', $notificationId)
            ->update(['is_read' => 1]);

        return response()->json(['status' => 200]);
    }

    public function getUnreadCount()
    {
        $count = DB::table('cms_multiple_notification')
            ->where('user_id', auth()->id())
            ->where('is_read', 0)
            ->count();

        return response()->json(['count' => $count]);
    }
}
