<?php

namespace App\Services;

use App\Models\NotificationModel;
use App\Models\EmployeeModel;
use Config\Services;

class NotificationService
{
    private NotificationModel $notificationModel;
    private EmployeeModel $employeeModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
        $this->employeeModel     = new EmployeeModel();
    }

    /**
     * Notify Admin
     */
    public function notifyAdmin(string $title, string $message, string $type = 'leave'): void
    {
        $this->notificationModel->insert([
            'emp_code' => null, // null for admin
            'title'    => $title,
            'message'  => $message,
            'type'     => $type,
            'is_read'  => false,
        ]);
    }

    /**
     * Notify Employee
     */
    public function notifyEmployee(string $empCode, string $title, string $message, string $type = 'info'): void
    {
        // 1. In-app notification
        $this->notificationModel->insert([
            'emp_code' => $empCode,
            'title'    => $title,
            'message'  => $message,
            'type'     => $type,
            'is_read'  => false,
        ]);

        // 2. Email notification
        $employee = $this->employeeModel->findByCode($empCode);
        if ($employee && !empty($employee['email'])) {
            $this->sendEmail($employee['email'], $title, $message);
        }
    }

    /**
     * Send email using CI4 Email service
     */
    private function sendEmail(string $to, string $subject, string $message): void
    {
        $email = Services::email();

        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($message);

        // Attempt to send
        if (!$email->send()) {
            log_message('error', "[NotificationService] Failed to send email to {$to}: " . $email->printDebugger(['headers']));
        }
    }
}
