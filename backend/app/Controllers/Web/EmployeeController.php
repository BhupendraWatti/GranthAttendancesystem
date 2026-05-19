<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use App\Models\AttendanceDailyModel;
use App\Services\SalaryService;

/**
 * Web EmployeeController — Server-rendered Employee pages
 *
 * GET /employees           — List all employees
 * GET /employees/:empCode  — Show employee detail + attendance + salary
 */
class EmployeeController extends BaseController
{
    /**
     * GET /employees — List all employees with optional filters
     */
    public function index()
    {
        $status = $this->request->getGet('status');
        $type = $this->request->getGet('type');
        $employees = [];

        try {
            $model = new EmployeeModel();
            
            if (!empty($status)) {
                $model->where('employees.status', $status);
            }
            if (!empty($type)) {
                $model->where('employees.employee_type', $type);
            }

            if (!empty($status) || !empty($type)) {
                // If filters are applied, use getAllWithMaster so we don't hardcode 'active' and break the 'inactive' filter
                $employees = $model->getAllWithMaster();
            } else {
                // Default behavior when no filters are applied: show only active employees
                $employees = $model->getActiveWithMaster();
            }
        } catch (\Throwable $e) {
            log_message('error', '[Web\\EmployeeController] Error: ' . $e->getMessage());
            if (strpos($e->getMessage(), 'Unable to connect') !== false || strpos($e->getMessage(), 'Connection refused') !== false) {
                session()->setFlashdata('error', 'Database connection failed. Please ensure MySQL is running in XAMPP Control Panel.');
            } else {
                session()->setFlashdata('error', 'Failed to load employees: ' . $e->getMessage());
            }
        }

        return view('pages/employees', [
            'pageTitle' => 'Employees',
            'activePage' => 'employees',
            'employees' => $employees,
            'total' => count($employees),
            'filters' => [
                'status' => $status ?? '',
                'type' => $type ?? '',
            ],
        ]);
    }

    /**
     * GET /employees/:empCode — Employee detail with attendance + salary
     */
    public function show($empCode = null)
    {
        if (empty($empCode)) {
            return redirect()->to(site_url('employees'))->with('error', 'Employee code is required.');
        }

        try {
            $employeeModel = new EmployeeModel();
            $attendanceModel = new AttendanceDailyModel();
            $salaryService = new SalaryService();

            $employeeDocModel = new \App\Models\EmployeeDocumentModel();

            $employee = $employeeModel->findByCodeWithMaster($empCode);

            if (!$employee) {
                return redirect()->to(site_url('employees'))->with('error', "Employee {$empCode} not found.");
            }

            // Fetch Master Data for Dropdowns
            $deptModel = new \App\Models\DepartmentModel();
            $desigModel = new \App\Models\DesignationModel();
            $shiftModel = new \App\Models\ShiftModel();

            $departments = $deptModel->where('status', 'active')->orderBy('name', 'ASC')->findAll();
            $designations = $desigModel->where('status', 'active')->orderBy('name', 'ASC')->findAll();
            $shifts = $shiftModel->where('status', 'active')->orderBy('name', 'ASC')->findAll();

            // Get month/year from query params (default = current month)
            $month = (int) ($this->request->getGet('month') ?? date('n'));
            $year = (int) ($this->request->getGet('year') ?? date('Y'));

            // Fetch attendance records for selected month
            $attendanceRecords = $attendanceModel->getMonthly($empCode, $year, $month);

            // Ensure all dates of the month are visible (fix for missing weekend/no-punch rows)
            helper('attendance');
            $holidayModel = new \App\Models\HolidayModel();
            $indexedRecords = [];
            foreach ($attendanceRecords as $r) {
                $indexedRecords[$r['date']] = $r;
            }

            $fullMonthRecords = [];
            $daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                if (isset($indexedRecords[$dateStr])) {
                    $fullMonthRecords[] = $indexedRecords[$dateStr];
                } else {
                    // Create default virtual record for missing dates
                    $dayType = 'working_day';
                    if (isWeekendOff($dateStr)) {
                        $dayType = 'weekend';
                    } elseif ($holidayModel->isHoliday($dateStr)) {
                        $dayType = 'holiday';
                    }

                    $fullMonthRecords[] = [
                        'emp_code' => $empCode,
                        'date' => $dateStr,
                        'first_in' => null,
                        'last_out' => null,
                        'work_minutes' => 0,
                        'late_minutes' => 0,
                        'status' => 'absent',
                        'attendance_status' => 'absent',
                        'day_type' => $dayType,
                        'work_mode' => null,
                        'punch_count' => 0,
                        'is_manual_entry' => 0,
                        'is_locked' => 0,
                    ];
                }
            }
            $attendanceRecords = $fullMonthRecords;

            // Calculate salary for this employee
            $salarySummary = $salaryService->calculateEmployeeSalary($empCode, $year, $month);

            // Fetch documents
            $documents = $employeeDocModel->where('emp_code', $empCode)->orderBy('created_at', 'DESC')->findAll();

            // Fetch Leave Balances
            $balanceModel = new \App\Models\LeaveBalanceModel();
            $leaveBalances = $balanceModel->getByEmployee($empCode);

            return view('pages/employee_detail', [
                'pageTitle' => $employee['name'],
                'activePage' => 'employees',
                'employee' => $employee,
                'attendanceRecords' => $attendanceRecords,
                'salarySummary' => $salarySummary,
                'documents' => $documents,
                'leaveBalances' => $leaveBalances,
                'month' => $month,
                'year' => $year,
                'masters' => [
                    'departments' => $departments,
                    'designations' => $designations,
                    'shifts' => $shifts,
                ]
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[Web\\EmployeeController] show error: ' . $e->getMessage());
            return redirect()->to(site_url('employees'))->with('error', 'Failed to load employee details. Please ensure the database is connected.');
        }
    }

    /**
     * POST /employees/profile — Update employee HR profile metadata
     */
    public function updateProfile()
    {
        $empCode = $this->request->getPost('emp_code');
        if (empty($empCode)) {
            return redirect()->back()->with('error', 'Employee code is required.');
        }

        try {
            $model = new EmployeeModel();
            $employee = $model->findByCode($empCode);
            if (!$employee) {
                return redirect()->back()->with('error', 'Employee not found.');
            }

            $data = [
                'name'              => $this->request->getPost('name'),
                'employee_type'     => $this->request->getPost('employee_type'),
                'date_of_joining'   => $this->request->getPost('date_of_joining') ?: null,
                'department_id'     => $this->request->getPost('department_id') ?: null,
                'designation_id'    => $this->request->getPost('designation_id') ?: null,
                'shift_id'          => $this->request->getPost('shift_id') ?: null,
                'employment_status' => $this->request->getPost('employment_status') ?: 'active',
                'is_profile_locked' => 1, // Lock profile on manual update
            ];

            $model->update($employee['id'], $data);

            return redirect()->back()->with('success', 'Employee profile updated and locked successfully.');
        } catch (\Throwable $e) {
            log_message('error', '[Web\\EmployeeController] updateProfile error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update employee profile.');
        }
    }

    /**
     * POST /employees/salary — Update employee base salary
     */
    public function updateSalary()
    {
        $empCode = $this->request->getPost('emp_code');
        $salary = $this->request->getPost('salary');

        if (empty($empCode) || !is_numeric($salary) || $salary <= 0) {
            return redirect()->back()->with('error', 'Invalid salary value provided.');
        }

        try {
            $employeeModel = new EmployeeModel();
            $employee = $employeeModel->findByCode($empCode);

            if (!$employee) {
                return redirect()->back()->with('error', 'Employee not found.');
            }

            $employeeModel->update($employee['id'], ['salary' => $salary]);

            return redirect()->back()->with('success', 'Salary updated successfully.');
        } catch (\Throwable $e) {
            log_message('error', '[Web\\EmployeeController] updateSalary error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update salary.');
        }
    }

    /**
     * POST /employees/email — Update employee email (manual mapping)
     *
     * IMPORTANT: Email is NOT provided by eTimeOffice API and must never be
     * overwritten by sync. This endpoint is the only supported way to set it.
     */
    public function updateEmail()
    {
        $empCode = (string) $this->request->getPost('emp_code');
        $email = trim((string) $this->request->getPost('email'));

        if ($empCode === '') {
            return redirect()->back()->with('error', 'Employee code is required.');
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Invalid email format.');
        }

        try {
            $employeeModel = new EmployeeModel();
            $employee = $employeeModel->findByCode($empCode);
            if (!$employee) {
                return redirect()->back()->with('error', 'Employee not found.');
            }

            // Enforce uniqueness manually (DB unique index exists but this gives a nicer message)
            if ($email !== '') {
                $existing = $employeeModel->where('email', $email)->first();
                if ($existing && (int) $existing['id'] !== (int) $employee['id']) {
                    return redirect()->back()->with('error', 'Email already assigned to another employee.');
                }
            }

            $employeeModel->update($employee['id'], [
                'email' => $email === '' ? null : $email,
            ]);

            return redirect()->back()->with('success', 'Email updated successfully.');
        } catch (\Throwable $e) {
            log_message('error', '[Web\\EmployeeController] updateEmail error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update email.');
        }
    }

    /**
     * POST /employees/attendance — Update employee attendance manually
     */
    public function updateAttendance()
    {
        $empCode = $this->request->getPost('emp_code');
        $date = $this->request->getPost('date');
        $status = $this->request->getPost('status');
        $firstIn = $this->request->getPost('first_in') ?: null;
        $lastOut = $this->request->getPost('last_out') ?: null;
        $workMode = $this->request->getPost('work_mode') ?: null; // New field

        if (empty($empCode) || empty($date) || empty($status)) {
            return redirect()->back()->with('error', 'Employee code, date, and status are required.');
        }

        try {
            $attendanceModel = new AttendanceDailyModel();
            $overrideModel = new \App\Models\AttendanceOverrideModel();
            
            // Handle Work Mode Override Table
            if ($workMode) {
                $overrideModel->replace([
                    'emp_code' => $empCode,
                    'attendance_date' => $date,
                    'override_type' => $workMode,
                    'remarks' => 'Admin manual edit',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $overrideModel->where('emp_code', $empCode)->where('attendance_date', $date)->delete();
            }

            // Calculate work minutes if times are provided
            $workMinutes = 0;
            if ($firstIn && $lastOut) {
                $inTime = strtotime($date . ' ' . $firstIn);
                $outTime = strtotime($date . ' ' . $lastOut);
                if ($outTime > $inTime) {
                    $workMinutes = round(($outTime - $inTime) / 60);
                }
            } else if ($status === 'work_from_home' || $workMode === 'wfh') {
                $workMinutes = 510; // 8.5 hours default for WFH
            } else if ($status === 'paid_leave' || $status === 'leave') {
                $workMinutes = 510; // Full credit for paid leave
            } else if ($status === 'half_day') {
                $workMinutes = 240; // 4 hours default
            } else if ($status === 'present') {
                $workMinutes = 480;
            } else if ($status === 'unpaid_leave' || $status === 'absent') {
                $workMinutes = 0;
            }

            // Format times for DB if provided
            $formattedFirstIn = $firstIn ? $date . ' ' . $firstIn . ':00' : null;
            $formattedLastOut = $lastOut ? $date . ' ' . $lastOut . ':00' : null;

            $data = [
                'emp_code' => $empCode,
                'date' => $date,
                'first_in' => $formattedFirstIn,
                'last_out' => $formattedLastOut,
                'status' => ($status === 'work_from_home') ? 'present' : $status, // map wfh to present
                'attendance_status' => ($status === 'work_from_home') ? 'present' : $status,
                'work_mode' => $workMode,
                'work_minutes' => $workMinutes,
                'punch_count' => ($firstIn || $lastOut) ? 2 : 0, 
                'is_manual_entry' => 1,
                'is_locked' => 1,
            ];

            $attendanceModel->upsertAttendance($data);

            return redirect()->back()->with('success', 'Attendance record for ' . $date . ' updated successfully.');
        } catch (\Throwable $e) {
            log_message('error', '[Web\\EmployeeController] updateAttendance error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update attendance.');
        }
    }

    /**
     * POST /employees/leave-balances — Update employee leave balances manually
     */
    public function updateLeaveBalances()
    {
        $empCode = $this->request->getPost('emp_code');
        $paid = $this->request->getPost('paid_leave');
        $unpaid = $this->request->getPost('unpaid_leave');
        $compoff = $this->request->getPost('comp_off');

        if (empty($empCode)) {
            return redirect()->back()->with('error', 'Employee code is required.');
        }

        try {
            $balanceModel = new \App\Models\LeaveBalanceModel();
            
            $types = [
                'paid_leave' => $paid,
                'unpaid_leave' => $unpaid,
                'comp_off' => $compoff
            ];

            foreach ($types as $type => $value) {
                if ($value === null || $value === '') continue;
                
                $existing = $balanceModel->where('emp_code', $empCode)->where('leave_type', $type)->first();
                $data = [
                    'emp_code' => $empCode,
                    'leave_type' => $type,
                    'total' => (float)$value,
                    'remaining' => (float)$value, // resetting remaining to match new total for simplicity
                    'used' => 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($existing) {
                    $balanceModel->update($existing['id'], $data);
                } else {
                    $balanceModel->insert($data);
                }
            }

            return redirect()->back()->with('success', 'Leave balances updated successfully.');
        } catch (\Throwable $e) {
            log_message('error', '[Web\\EmployeeController] updateLeaveBalances error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update leave balances.');
        }
    }
}
