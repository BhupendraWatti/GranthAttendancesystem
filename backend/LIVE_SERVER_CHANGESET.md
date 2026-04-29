# Live Server Changeset (Manual Apply)

Use this file to replicate the exact backend changes on live via FileZilla.

## 1) `app/Commands/SyncFull.php`

- `line 36`: add execution lock path
  - `\$lockPath = WRITEPATH . 'sync_full.lock';`
- `lines 40-44`: skip overlapping run if lock exists
- `lines 47-51`: log step start/end around `runFull()`
- `lines 74-78`: remove lock in `finally`

## 2) `app/Models/EmployeeModel.php`

- `lines 62-65`: extend UPSERT update set
  - `department = VALUES(department)`
  - `designation = VALUES(designation)`
  - `employee_type = VALUES(employee_type)`
  - `status = VALUES(status)`

This ensures employee updates from API are reflected, not just name.

## 3) `app/Models/PunchLogModel.php`

- `lines 169-176`: new helper method
  - `getDuplicateCleanupQuery(): string`
  - returns duplicate cleanup SQL for `punch_logs` based on `(emp_code, punch_time)`.

## 4) `app/Services/AttendanceService.php`

- `lines 61-64`: new env-based policy thresholds:
  - `FULL_TIME_PRESENT_MINUTES` (default `420`)
  - `INTERN_PRESENT_MINUTES` (default `330`)
  - `FULL_TIME_HALF_DAY_MINUTES` (default `264`)
  - `INTERN_HALF_DAY_MINUTES` (default `150`)
- `line 183`: status calculation continues via `determineStatus()`
- `lines 207-221`: `determineStatus()` logic updated:
  - `present` if `workMinutes >= presentThreshold`
  - `half_day` if `workMinutes >= halfDayThreshold`
  - otherwise `absent`

## 5) `app/Services/SyncService.php`

- `lines 8-9`: new imports:
  - `EmployeeSyncService`
  - `AttendancePolicyService`
- `lines 45-46`: new service properties
- `lines 69-70`: instantiate new services in constructor
- `line 226`: in incremental flow, replace old employee discovery with:
  - `syncFromPunchRecords($records)`
- `line 233`: run attendance summary generation:
  - `generateForDate($date)`
- `line 317` and `line 322`: same integration for full single-day flow
- `line 390` and `line 396`: same integration for full-range flow

## 6) `app/Services/EmployeeSyncService.php` (new file)

- `line 7`: class declaration `EmployeeSyncService`
- `line 16`: main method `syncFromPunchRecords(array $records): array`
  - unique empcode extraction
  - upsert create/update with logs
  - department/designation dynamic extraction
  - employee type normalization
  - deactivate employees missing from API (`status = inactive`)
- `line 85`: `extractDynamicValue(...)` recursive dynamic key mapper
- `line 108`: `normalizeEmployeeType(...)` (`intern`/`trainee` => `intern`)

## 7) `app/Services/AttendancePolicyService.php` (new file)

- `line 7`: class declaration `AttendancePolicyService`
- `lines 18-21`: env-based hours thresholds loaded
- `line 24`: `generateForDate(string $date): array`
  - aggregates first_in/last_out from `punch_logs`
  - computes `total_hours`
  - upserts `attendance_summary`
- `line 75`: `classify()` rule engine
  - full-time: present >= configured present hours, half-day >= configured half-day hours
  - intern: separate configured thresholds

## 8) `app/Database/Migrations/2026-04-27-201600_CreateAttendanceSummaryTable.php` (new file)

- `line 7`: migration class added
- `line 52`: unique key on `['emp_code', 'date']`
- `line 54`: creates `attendance_summary` table

## 9) `app/Database/Migrations/2026-04-27-201700_AddPunchLogsCompositeIndex.php` (new file)

- `line 7`: migration class added
- `line 12`: check index existence
- `line 14`: create index `idx_punch_emp_time` on `(emp_code, punch_time)`
- `line 20`: down migration existence check
- `line 22`: drop index if present

---

## Optional env keys to set on live (`backend/.env`)

If you want the exact attendance behavior used in fixes, add/update:

- `FULL_TIME_PRESENT_MINUTES=420`
- `FULL_TIME_HALF_DAY_MINUTES=264`
- `INTERN_PRESENT_MINUTES=330`
- `INTERN_HALF_DAY_MINUTES=150`

If these are missing, code defaults are already applied as listed above.
