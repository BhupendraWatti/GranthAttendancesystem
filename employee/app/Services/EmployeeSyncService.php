<?php

namespace App\Services;

use App\Models\EmployeeModel;

class EmployeeSyncService
{
    private EmployeeModel $employeeModel;

    public function __construct(?EmployeeModel $employeeModel = null)
    {
        $this->employeeModel = $employeeModel ?? new EmployeeModel();
    }

    public function syncFromPunchRecords(array $records): array
    {
        $created = 0;
        $updated = 0;
        $deactivated = 0;
        $apiEmpcodes = [];
        $seen = [];

        foreach ($records as $record) {
            $empCode = trim((string) ($record['emp_code'] ?? ''));
            if ($empCode === '' || isset($seen[$empCode])) {
                continue;
            }
            $seen[$empCode] = true;
            $apiEmpcodes[] = $empCode;

            $raw = is_array($record['raw_data'] ?? null) ? $record['raw_data'] : [];
            $department = $this->extractDynamicValue($raw, ['department', 'dept']);
            $designation = $this->extractDynamicValue($raw, ['designation', 'desig', 'title']);
            $employeeType = $this->normalizeEmployeeType(
                $this->extractDynamicValue($raw, ['employee_type', 'type', 'emp_type', 'category'])
            );

            $existing = $this->employeeModel->findByCode($empCode);
            $isNew = $existing === null;

            $payload = [
                'emp_code'       => $empCode,
                'name'           => $record['name'] ?? $empCode,
                'department'     => $department,
                'designation'    => $designation,
                'employee_type'  => $employeeType,
                'status'         => 'active',
            ];

            $this->employeeModel->upsertByCode($payload);
            if ($isNew) {
                $created++;
                log_message('info', "[EmployeeSyncService] Created employee {$empCode}");
            } else {
                $updated++;
                log_message('info', "[EmployeeSyncService] Updated employee {$empCode}");
            }
        }

        if (!empty($apiEmpcodes)) {
            $dbEmpcodes = array_map(
                static fn(array $row): string => (string) $row['emp_code'],
                $this->employeeModel->select('emp_code')->findAll()
            );
            $missingInApi = array_diff($dbEmpcodes, $apiEmpcodes);
            foreach ($missingInApi as $empCode) {
                $this->employeeModel
                    ->where('emp_code', $empCode)
                    ->set(['status' => 'inactive', 'updated_at' => date('Y-m-d H:i:s')])
                    ->update();
                $deactivated++;
                log_message('info', "[EmployeeSyncService] Deactivated employee {$empCode} (missing in API)");
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'deactivated' => $deactivated,
            'api_empcodes' => count($apiEmpcodes),
        ];
    }

    private function extractDynamicValue(array $record, array $needles): ?string
    {
        foreach ($record as $key => $value) {
            if (is_array($value)) {
                $nested = $this->extractDynamicValue($value, $needles);
                if ($nested !== null && $nested !== '') {
                    return $nested;
                }
                continue;
            }

            $normalizedKey = strtolower((string) $key);
            foreach ($needles as $needle) {
                if (strpos($normalizedKey, strtolower($needle)) !== false) {
                    $out = trim((string) $value);
                    return $out === '' ? null : $out;
                }
            }
        }

        return null;
    }

    private function normalizeEmployeeType(?string $rawType): string
    {
        $value = strtolower(trim((string) $rawType));
        return in_array($value, ['intern', 'trainee'], true) ? 'intern' : 'full_time';
    }
}
