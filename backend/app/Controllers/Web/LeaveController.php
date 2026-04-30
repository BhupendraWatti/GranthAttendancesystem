<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\LeaveRequestModel;
use App\Models\EmployeeModel;
use App\Services\LeaveService;
use App\Services\NotificationService;
use Config\Services;

class LeaveController extends BaseController
{
    private LeaveService $leaveService;
    private NotificationService $notificationService;
    private LeaveRequestModel $leaveRequestModel;
    private EmployeeModel $employeeModel;

    public function __construct()
    {
        $this->leaveService        = Services::leaveservice();
        $this->notificationService = Services::notificationservice();
        $this->leaveRequestModel   = new LeaveRequestModel();
        $this->employeeModel       = new EmployeeModel();
    }

    /**
     * Admin Leave Dashboard
     */
    public function index()
    {
        $data = [
            'pageTitle'  => 'Leave Requests',
            'activePage' => 'leave',
            'pending'    => $this->leaveRequestModel
                ->select('leave_requests.*, employees.name as employee_name')
                ->join('employees', 'employees.emp_code = leave_requests.emp_code')
                ->where('leave_requests.status', 'pending')
                ->orderBy('created_at', 'ASC')
                ->findAll(),
            'history'    => $this->leaveRequestModel
                ->select('leave_requests.*, employees.name as employee_name')
                ->join('employees', 'employees.emp_code = leave_requests.emp_code')
                ->where('leave_requests.status !=', 'pending')
                ->orderBy('created_at', 'DESC')
                ->findAll(50),
        ];

        return view('pages/leave_admin', $data);
    }

    /**
     * Approve Request
     */
    public function approve()
    {
        $id = $this->request->getPost('id');
        $comment = $this->request->getPost('admin_comment');

        $request = $this->leaveRequestModel->find($id);
        if (!$request) {
            return redirect()->back()->with('error', 'Request not found.');
        }

        if ($this->leaveService->approve($id, $comment)) {
            // Notify Employee
            $this->notificationService->notifyEmployee(
                $request['emp_code'],
                "Leave Approved",
                "Your leave request from {$request['from_date']} to {$request['to_date']} has been approved.",
                'success'
            );
            return redirect()->back()->with('success', 'Leave request approved successfully.');
        }

        return redirect()->back()->with('error', 'Failed to approve request.');
    }

    /**
     * Reject Request
     */
    public function reject()
    {
        $id = $this->request->getPost('id');
        $comment = $this->request->getPost('admin_comment');

        $request = $this->leaveRequestModel->find($id);
        if (!$request) {
            return redirect()->back()->with('error', 'Request not found.');
        }

        if ($this->leaveService->reject($id, $comment)) {
            // Notify Employee
            $this->notificationService->notifyEmployee(
                $request['emp_code'],
                "Leave Rejected",
                "Your leave request from {$request['from_date']} to {$request['to_date']} was rejected. Comment: {$comment}",
                'danger'
            );
            return redirect()->back()->with('success', 'Leave request rejected.');
        }

        return redirect()->back()->with('error', 'Failed to reject request.');
    }
}
