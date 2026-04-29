<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Services\OtpService;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    public function login(): string
    {
        return view('auth/login');
    }

    public function sendOtp(): ResponseInterface
    {
        // #region agent log
        $dbg = static function (string $hypothesisId, string $message, array $data = []): void {
            try {
                $payload = [
                    'sessionId' => '6fc754',
                    'runId' => 'pre-fix',
                    'hypothesisId' => $hypothesisId,
                    'location' => 'employee/app/Controllers/Auth.php:sendOtp',
                    'message' => $message,
                    'data' => $data,
                    'timestamp' => (int) floor(microtime(true) * 1000),
                ];
                file_put_contents('debug-6fc754.log', json_encode($payload, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);
            } catch (\Throwable $e) {
                // ignore
            }
        };
        // #endregion agent log

        $email = trim((string) $this->request->getPost('email'));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // #region agent log
            $dbg('H1', 'Rejected invalid email input', ['hasAt' => strpos($email, '@') !== false]);
            // #endregion agent log
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Valid email is required']);
        }

        $employeeModel = new EmployeeModel();
        $employee = $employeeModel
            ->where('status', 'active')
            ->where('email', $email)
            ->first();

        if (!$employee) {
            // #region agent log
            $dbg('H1', 'Email not registered in DB', ['emailDomain' => substr($email, (int) strrpos($email, '@') + 1)]);
            // #endregion agent log
            log_message('warning', 'OTP send denied (email not registered) email={email}', ['email' => $email]);
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'message' => 'Email not registered']);
        }

        if (empty($employee['email'])) {
            // #region agent log
            $dbg('H1', 'Employee email is NULL/empty', ['empCode' => $employee['emp_code'] ?? null]);
            // #endregion agent log
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Contact admin to set email']);
        }

        $otpService = new OtpService();
        $devOtp = $otpService->issueOtp($employee['emp_code'], (string) $employee['email']);

        log_message('info', 'OTP issued emp_code={emp_code}', ['emp_code' => $employee['emp_code']]);
        $payload = ['ok' => true, 'message' => 'OTP sent'];
        if ($devOtp !== null) {
            $payload['devOtp'] = $devOtp;
        }

        return $this->response->setJSON($payload);
    }

    public function verifyOtp(): ResponseInterface
    {
        $identifier = trim((string) $this->request->getPost('email'));
        $otp = trim((string) $this->request->getPost('otp'));

        if ($identifier === '' || !filter_var($identifier, FILTER_VALIDATE_EMAIL) || $otp === '') {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Valid email and OTP are required']);
        }

        $employeeModel = new EmployeeModel();
        $employee = $employeeModel->where('status', 'active')
            ->where('email', $identifier)
            ->first();

        if (!$employee) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'message' => 'Email not registered']);
        }

        if (empty($employee['email'])) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Contact admin to set email']);
        }

        $otpService = new OtpService();
        $result = $otpService->verifyOtp($employee['emp_code'], $otp);

        if (!$result['ok']) {
            log_message('warning', 'OTP verify failed emp_code={emp_code} reason={reason}', [
                'emp_code' => $employee['emp_code'],
                'reason'   => $result['reason'],
            ]);
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'message' => $result['message']]);
        }

        session()->regenerate(true);
        session()->set([
            'employee_logged_in' => true,
            'empcode'            => $employee['emp_code'],
            'login_time'         => time(),
        ]);

        log_message('info', 'Employee login success emp_code={emp_code}', ['emp_code' => $employee['emp_code']]);
        return $this->response->setJSON(['ok' => true, 'redirect' => base_url('/')]);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('auth/login'));
    }
}

