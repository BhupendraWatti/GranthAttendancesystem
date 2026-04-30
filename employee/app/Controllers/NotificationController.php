<?php

namespace App\Controllers;

use App\Models\NotificationModel;
use Config\Services;

class NotificationController extends BaseController
{
    private NotificationModel $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
    }

    public function index()
    {
        $empCode = session()->get('empcode');
        if (!$empCode) return redirect()->to('/login');

        $data = [
            'pageTitle'    => 'Notifications',
            'activePage'   => 'notifications',
            'notifications' => $this->notificationModel->getByEmployee($empCode),
        ];

        return view('notifications', $data);
    }

    public function markRead()
    {
        $empCode = session()->get('empcode');
        if ($empCode) {
            $this->notificationModel->markAllAsRead($empCode);
        }
        return $this->response->setJSON(['success' => true]);
    }
}
