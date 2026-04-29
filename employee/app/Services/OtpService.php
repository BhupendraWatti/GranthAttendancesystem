<?php

namespace App\Services;

use App\Models\EmployeeModel;
use Config\Services;

class OtpService
{
    private int $ttlSeconds;
    private int $maxAttempts;

    public function __construct()
    {
        $this->ttlSeconds = (int) (env('EMP_OTP_TTL_SECONDS') ?: 300);
        $this->maxAttempts = (int) (env('EMP_OTP_MAX_ATTEMPTS') ?: 3);
    }

    public function issueOtp(string $empCode, string $email): ?string
    {
        // #region agent log
        $dbg = static function (string $hypothesisId, string $message, array $data = []): void {
            try {
                $payload = [
                    'sessionId' => '6fc754',
                    'runId' => 'pre-fix',
                    'hypothesisId' => $hypothesisId,
                    'location' => 'employee/app/Services/OtpService.php:issueOtp',
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

        // #region agent log
        $emailDomain = '';
        if (strpos($email, '@') !== false) {
            $emailDomain = substr($email, (int) strrpos($email, '@') + 1);
        }
        $dbg('H1', 'OTP issue start', [
            'empCode' => $empCode,
            'emailDomain' => $emailDomain,
            'env' => ENVIRONMENT,
            'smtpHost' => (string) (env('email.SMTPHost') ?: ''),
            'smtpPort' => (int) (env('email.SMTPPort') ?: 0),
            'smtpCrypto' => (string) (env('email.SMTPCrypto') ?: ''),
        ]);
        // #endregion agent log

        $otp = (string) random_int(100000, 999999);
        $hash = password_hash($otp, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', time() + $this->ttlSeconds);

        (new EmployeeModel())
            ->where('emp_code', $empCode)
            ->set([
                'otp_hash'       => $hash,
                'otp_expires_at' => $expiresAt,
                'otp_attempts'   => 0,
                'otp_last_sent_at' => date('Y-m-d H:i:s'),
            ])
            ->update();

        // Send OTP email via SMTP (configured in .env / Config\Email.php)
        try {
            $emailSvc = Services::email();
            $emailSvc->setTo($email);
            $emailSvc->setSubject('Your OTP for Employee Portal Login');
            $emailSvc->setMessage(
                '<p>Your OTP is:</p>' .
                '<p style="font-size:28px; font-weight:700; letter-spacing:2px;">' . esc($otp) . '</p>' .
                '<p>This OTP expires in 5 minutes.</p>'
            );

            // #region agent log
            $dbg('H2', 'Attempting SMTP send', [
                'toDomain' => $emailDomain,
                'fromDomain' => (string) (env('email.fromEmail') ? substr((string) env('email.fromEmail'), (int) strrpos((string) env('email.fromEmail'), '@') + 1) : ''),
            ]);
            // #endregion agent log

            if (!$emailSvc->send(false)) {
                log_message('error', 'OTP email send failed emp_code={emp_code} email={email} debug={debug}', [
                    'emp_code' => $empCode,
                    'email'    => $email,
                    'debug'    => $emailSvc->printDebugger(['headers', 'subject']),
                ]);

                // #region agent log
                $dbg('H3', 'SMTP send returned false', [
                    'debugHas554' => strpos($emailSvc->printDebugger(['headers', 'subject']), '554') !== false,
                ]);
                // #endregion agent log
            } else {
                // #region agent log
                $dbg('H2', 'SMTP send succeeded', ['toDomain' => $emailDomain]);
                // #endregion agent log
            }
        } catch (\Throwable $e) {
            log_message('error', 'OTP email exception emp_code={emp_code} email={email} err={err}', [
                'emp_code' => $empCode,
                'email'    => $email,
                'err'      => $e->getMessage(),
            ]);

            // #region agent log
            $dbg('H4', 'SMTP send exception', ['err' => $e->getMessage()]);
            // #endregion agent log
        }

        // In non-production, return OTP for localhost testing
        return ENVIRONMENT === 'production' ? null : $otp;
    }

    /**
     * @return array{ok:bool, reason?:string, message:string}
     */
    public function verifyOtp(string $empCode, string $otp): array
    {
        $employee = (new EmployeeModel())->where('emp_code', $empCode)->first();
        if (!$employee) {
            return ['ok' => false, 'reason' => 'not_found', 'message' => 'Employee not found'];
        }

        $attempts = (int) ($employee['otp_attempts'] ?? 0);
        if ($attempts >= $this->maxAttempts) {
            return ['ok' => false, 'reason' => 'locked', 'message' => 'OTP attempts exceeded'];
        }

        $expiresAt = $employee['otp_expires_at'] ?? null;
        if (!$expiresAt || strtotime((string) $expiresAt) < time()) {
            return ['ok' => false, 'reason' => 'expired', 'message' => 'OTP expired'];
        }

        $hash = (string) ($employee['otp_hash'] ?? '');
        if ($hash === '' || !password_verify($otp, $hash)) {
            (new EmployeeModel())
                ->where('emp_code', $empCode)
                ->set(['otp_attempts' => $attempts + 1])
                ->update();

            return ['ok' => false, 'reason' => 'invalid', 'message' => 'Invalid OTP'];
        }

        // success: clear otp
        (new EmployeeModel())
            ->where('emp_code', $empCode)
            ->set([
                'otp_hash' => null,
                'otp_expires_at' => null,
                'otp_attempts' => 0,
            ])
            ->update();

        return ['ok' => true, 'message' => 'OTP verified'];
    }
}

