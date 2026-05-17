# Granth Attendance & Payroll System

A comprehensive workforce management application built with **CodeIgniter 4**, designed to bridge biometric attendance hardware with professional HR and payroll workflows.

## 🚀 Project Overview

The Granth Attendance System provides a dual-portal solution for modern companies:
- **Backend (Admin Portal):** Centralized management of employees, attendance synchronization, leave approvals, and precise payroll generation.
- **Employee Portal:** A mobile-optimized self-service dashboard where staff can view their attendance logs, apply for leaves, track their "Time-Bank" (Comp-offs), and download payslips.

## 🏗️ Architecture

- **Framework:** CodeIgniter 4 (PHP 8.x)
- **Database:** MySQL
- **Integration:** eTimeOffice API (Biometric hardware synchronization)
- **UI:** Server-side rendered views with progressive JavaScript enhancement and responsive CSS.

---

## ✨ Key Features (Recently Updated - May 2026)

### 1. Professional Weekend Policy
- **Logic:** Automatically recognizes **Sundays** and the **1st & 3rd Saturdays** of every month as off-days.
- **Visibility:** Differentiates between "Absent" (missed work) and "Weekend" (scheduled off) on all dashboards.
- **Gap Filling:** Ensures every calendar date is visible in the Service Logs, even if no punches exist, allowing admins to edit any date manually.

### 2. Advanced Work Modes (WFH/WFO)
- **Separation of Concerns:** Introduced a three-tier tracking system:
    1.  `attendance_status` (Present, Half-day, Absent, etc.)
    2.  `work_mode` (WFO - Office, WFH - Home)
    3.  `day_type` (Working Day, Weekend, Holiday)
- **Manual Overrides:** Admins can manually mark any day (including weekends) as WFH or WFO to manage special work arrangements.

### 3. "Time-Bank" Comp-off System
- **Automated Earnings:** Employees automatically earn **+1.0 day** of Comp-off credit whenever they work on a weekend or a Public Holiday.
- **Atomic Protection:** Uses database-level locking to prevent race conditions (no double or triple crediting for a single day).
- **Payroll Integration:** Comp-off usage provides full day credit (510 minutes), ensuring no salary deductions.

### 4. Professional Leave Management
- **Paid Leave Carry-Forward:** Unused paid leaves (1.0 per month) are now carried forward to the next month instead of being cleared.
- **Balance Controls:** Admins can manually adjust leave pools (Paid, Unpaid, Comp-off) directly from the employee profile.
- **Validation:** Real-time balance checking prevents employees from over-applying for paid leaves.

### 5. Bank-Standard Salary Engine
- **Month-Length Accuracy:** Calculates daily rates dynamically based on the actual days in the month (28, 30, or 31) using `date('t')`.
- **Fair Deductions:** Prorates "Net Payable" amounts mid-month and ensures perfect accuracy for full-month payouts.
- **Overwrite Protection:** Manual corrections (punches/status) are "Locked" to prevent them from being overwritten by automated hardware syncs.

---

## 🛠️ Server Sync & Maintenance

### Mandatory Files for Update
To keep the server in sync with these features, the following core services and models must be uploaded:
- `backend/app/Services/AttendanceService.php`
- `backend/app/Services/SalaryService.php`
- `backend/app/Services/LeaveService.php`
- `backend/app/Models/AttendanceDailyModel.php`
- `employee/app/Services/...` (Mirror files)

### Database Migrations
Always run migrations after updating backend files to add new protection flags and status columns:
```bash
php spark migrate
```

### Manual Data Cleanup
If testing data causes messy balances, use the **Admin > Employee Detail > Leave Balances > Edit** feature to reset pools to 1.0 (Paid) or 0.0 (Comp-off) as required.

---
*Developed for Granth - Professional Workforce Intelligence.*
