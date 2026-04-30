<?php

namespace App\Controllers;

use App\Models\LeaveRequestModel;
use App\Models\LeaveBalanceModel;
use App\Models\HolidayModel;
use App\Services\LeaveService;
use App\Services\NotificationService;
use Config\Services;

class LeaveController extends BaseController
{
    private LeaveService $leaveService;
    private NotificationService $notificationService;
    private LeaveRequestModel $leaveRequestModel;
    private LeaveBalanceModel $leaveBalanceModel;
    private HolidayModel $holidayModel;

    public function __construct()
    {
        $this->leaveService        = Services::leaveservice();
        $this->notificationService = Services::notificationservice();
        $this->leaveRequestModel   = new LeaveRequestModel();
        $this->leaveBalanceModel   = new LeaveBalanceModel();
        $this->holidayModel        = new HolidayModel();
    }

    /**
     * Leave Dashboard
     */
    public function index()
    {
        $empCode = session()->get('emp_code');
        if (!$empCode) {
            return redirect()->to('/login');
        }

        $data = [
            'pageTitle'    => 'Leave Management',
            'activePage'   => 'leave',
            'balances'     => $this->leaveBalanceModel->getByEmployee($empCode),
            'history'      => $this->leaveRequestModel->getByEmployee($empCode),
            'holidays'     => $this->holidayModel->getUpcoming(),
        ];

        return view('pages/leave', $data);
    }

    /**
     * Submit Leave Request
     */
    public function apply()
    {
        $empCode = session()->get('emp_code');
        if (!$empCode) {
            return redirect()->to('/login');
        }

        $rules = [
            'leave_type'   => 'required',
            'from_date'    => 'required|valid_date',
            'to_date'      => 'required|valid_date',
            'reason'       => 'required|min_length[5]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please provide all required details correctly.');
        }

        try {
            $postData = $this->request->getPost();
            $postData['emp_code'] = $empCode;
            
            $result = $this->leaveService->apply($postData);

            // Notify Admin
            $employeeName = session()->get('name') ?? $empCode;
            $this->notificationService->notifyAdmin(
                "New Leave Request",
                "{$employeeName} has applied for {$result['duration']} days of leave ({$postData['leave_type']}) from {$postData['from_date']} to {$postData['to_date']}.",
                'leave'
            );

            return redirect()->to('/leave')->with('success', "Leave request submitted successfully ({$result['duration']} days).");
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
