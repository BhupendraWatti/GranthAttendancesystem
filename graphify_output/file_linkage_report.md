# File Linkage Report

This report details the specific code-level links discovered in the Granth Attendance System.

## 1. Entry Points (Routes)

### Backend Portal
| Path | Controller Action | Purpose |
| :--- | :--- | :--- |
| `/` | `AuthController::loginForm` | Admin Login |
| `/dashboard` | `DashboardController::index` | Primary Overview |
| `/sync` | `SyncController::index` | Attendance Sync Management |
| `/employees` | `EmployeeController::index` | Employee Directory |
| `/api/dashboard/*`| `Api\DashboardController` | Real-time Data Polling |

### Employee Portal
| Path | Controller Action | Purpose |
| :--- | :--- | :--- |
| `/auth/login` | `Auth::login` | Staff Login |
| `/` | `Dashboard::index` | Personal Dashboard |
| `/attendance` | `Attendance::index` | Monthly Logs |
| `/leave` | `LeaveController::index`| Leave Requests |

## 2. Controller -> Model Linkages

### Backend
- **EmployeeController**: Links to `EmployeeModel`, `AttendanceDailyModel`, and `EmployeeDocumentModel`.
- **DashboardController**: Links to `EmployeeModel`, `AttendanceDailyModel`, and `PunchLogModel`.
- **SyncController**: Links to `SyncLogModel`, `PunchLogModel`, and `AttendanceDailyModel`.
- **SalaryController**: Links to `EmployeeModel`, `AttendanceDailyModel`, and `SalaryService`.

### Employee
- **Dashboard**: Links to `EmployeeModel` and `AttendanceDailyModel`.
- **Attendance**: Links to `AttendanceDailyModel`.
- **LeaveController**: Links to `LeaveRequestModel` and `LeaveBalanceModel`.

## 3. View Hierarchy

### Layout System
- All views extend `layout/main.php` (Backend) or `layout/main.php` (Employee).
- Common partials:
  - `partials/sidebar.php`: Navigation links.
  - `partials/topbar.php`: User profile and logout.

### Primary Templates
- `pages/employees.php`: Renders the employee table.
- `pages/employee_detail.php`: Links to attendance tables and salary cards.
- `dashboard.php`: Contains JS logic for fetching `/api` data.

## 4. External Linkages
- **eTimeOffice API**: Linked via `SyncController` and custom logic in `backend/app/Models/SyncLogModel.php`.
- **MySQL Database**: All models link to the shared database defined in `.env`.

## 5. Core Workflows (How files work together)

### A. The Attendance Sync Process
1.  **Trigger**: User clicks "Sync" in `SyncController` or a Cron job runs.
2.  **API Bridge**: `SyncController` calls the eTimeOffice API.
3.  **Raw Logging**: Data is first stored in `PunchLogModel` (raw records).
4.  **Processing**: The controller uses `AttendanceDailyModel` to aggregate these punches into daily "In/Out" times.
5.  **Status Update**: `SyncLogModel` records the success/failure of the batch.

### B. Dashboard Data Flow
1.  **Page Load**: `DashboardController` loads the base `dashboard.php` view.
2.  **Initial Stats**: The controller fetches basic counts from `EmployeeModel`.
3.  **Real-time Updates**: The view uses JavaScript to poll `Api\DashboardController`.
4.  **Data Refresh**: The API controller queries `AttendanceDailyModel` for live punch updates without reloading the page.

### C. Salary & Payslip Generation
1.  **Selection**: User selects an employee in `SalaryController`.
2.  **Calculation Logic**: The controller passes the request to `SalaryService`.
3.  **Data Gathering**: `SalaryService` queries `AttendanceDailyModel` for total hours and `EmployeeModel` for base pay.
4.  **Rendering**: The final calculated object is passed to `payslip_print.php` for formatting.

### D. Leave Management
1.  **Request**: Employee submits a form handled by `LeaveController`.
2.  **Validation**: `LeaveController` checks `LeaveBalanceModel` to ensure they have enough days.
3.  **Persistence**: Data is saved to `LeaveRequestModel` with a 'pending' status.
4.  **Admin Action**: Admin views the request via backend `LeaveController` and updates the status, which then triggers a sync back to the dashboard.
