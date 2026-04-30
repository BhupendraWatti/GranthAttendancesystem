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
        $empCode = session()->get('emp_code');
        if (!$empCode) return redirect()->to('/login');

        $data = [
            'pageTitle'    => 'Notifications',
            'activePage'   => 'notifications',
            'notifications' => $this->notificationModel->getByEmployee($empCode),
        ];

        return view('pages/notifications', $data);
    }

    public function markRead()
    {
        $empCode = session()->get('emp_code');
        if ($empCode) {
            $this->notificationModel->markAllAsRead($empCode);
        }
        return $this->response->setJSON(['success' => true]);
    }
}
