# Project Architecture & Linking Guide

This folder contains a visual and textual representation of the **Granth Attendance System**'s internal linking and architecture.

## Visual Graph
Open [project_linking.html](project_linking.html) in your browser to see an interactive map of how files connect.

## Core Architecture
The project is split into two main CodeIgniter 4 applications:

1.  **Backend (`/backend`)**: The administrative portal for managing employees, sync processes, and global settings.
2.  **Employee (`/employee`)**: The self-service portal for staff to check attendance, salary, and apply for leave.

### Backend Linking Structure
- **Routes**: Defined in `backend/app/Config/Routes.php`. Maps URLs to `Web` and `Api` controllers.
- **Controllers**: Located in `backend/app/Controllers/`. They orchestrate logic by calling Models and rendering Views.
- **Models**: Located in `backend/app/Models/`. They handle database interactions (MySQL).
- **Views**: Located in `backend/app/Views/`. Uses a layout system (`layout/main.php`).

### Employee Linking Structure
- **Routes**: Defined in `employee/app/Config/Routes.php`. Focuses on OTP authentication and personal data.
- **Controllers**: Located in `employee/app/Controllers/`. Includes `Api` sub-controllers for real-time dashboard updates.
- **Models**: Located in `employee/app/Models/`. Shared logic with backend models for data consistency.
- **Views**: Located in `employee/app/Views/`. Responsive design optimized for mobile/tablet.

## Key Linkages
- **Authentication**: Backend uses standard session-based login. Employee portal uses OTP-based login via `Auth` controller.
- **Data Sync**: The `SyncController` in backend links to `SyncLogModel` and `PunchLogModel` to bridge external attendance APIs with the local database.
- **Salary/Attendance**: Both portals link to `AttendanceDailyModel` and `EmployeeModel` to calculate monthly goals and payouts.
