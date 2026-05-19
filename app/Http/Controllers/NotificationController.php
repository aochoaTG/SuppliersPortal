<?php

namespace App\Http\Controllers;

use App\Services\NotificationCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationCenterService $notificationCenter
    ) {}

    public function index(Request $request): View
    {
        $notifications = $this->notificationCenter
            ->queryForUser($request->user())
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $notificationModel = $this->notificationCenter->findForUser($request->user(), $notification);

        abort_unless($notificationModel, 404);

        if ($notificationModel->read_at === null) {
            $notificationModel->markAsRead();
        }

        $targetUrl = $notificationModel->data['url'] ?? route('notifications.index');

        return redirect()->to($targetUrl);
    }

    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        $notificationModel = $this->notificationCenter->findForUser($request->user(), $notification);

        abort_unless($notificationModel, 404);

        if ($notificationModel->read_at === null) {
            $notificationModel->markAsRead();
        }

        return back()->with('status', 'Notificación marcada como leída.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $this->notificationCenter->markAllAsReadForUser($request->user());

        return back()->with('status', 'Todas las notificaciones fueron marcadas como leídas.');
    }
}
