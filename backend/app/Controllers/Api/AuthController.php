<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Services\ApiService;
// use App\Models\AdminModel; // Kept for rollback — replaced by live eTimeOffice auth

/**
 * AuthController — Authentication endpoints
 * 
 * POST /api/login  — Authenticate and receive JWT
 * POST /api/logout — Client-side token invalidation
 */
class AuthController extends ResourceController
{
    protected $format = 'json';

    /**
     * POST /api/login
     * 
     * Expects: { username: string, password: string }
     * Returns: { token: string, user: object, expires_in: int }
     */
    public function login()
    {
        $rules = [
            'username' => 'required|min_length[3]',
            'password' => 'required|min_length[4]',
        ];

        // Get JSON body
        $input = $this->request->getJSON(true);

        if (empty($input)) {
            return $this->failValidationErrors('Request body is required (JSON)');
        }

        // Manual validation
        $validation = \Config\Services::validation();
        $validation->setRules($rules);

        if (!$validation->run($input)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        $username = $input['username'];
        $password = $input['password'];

        // -------------------------------------------------------------------
        // Live eTimeOffice Authentication (replaces local admins table check)
        //
        // Basic Auth format: "COMPANY_CODE:username" as the username when ETIME_COMPANY_CODE is set.
        // Companycode header is also sent by ApiService.
        // -------------------------------------------------------------------
        $companyCode   = env('ETIME_COMPANY_CODE', '');
        $etimeUsername = $companyCode ? "{$companyCode}:{$username}" : $username;

        $apiService = new ApiService();
        $apiService->setCredentials($etimeUsername, $password);

        if (!$apiService->verifyCredentials()) {
            log_message('warning', "[AuthController] eTimeOffice login failed for user: {$etimeUsername}");
            return $this->failUnauthorized('Invalid eTimeOffice credentials');
        }

        log_message('info', "[AuthController] eTimeOffice login succeeded for user: {$username}");

        // Generate JWT — payload mirrors original shape so frontend needs no changes
        helper('jwt');
        $token = jwt_encode([
            'id'       => 1,          // Virtual admin ID (no local DB row needed)
            'username' => $username,
            'role'     => 'admin',    // All eTimeOffice users are treated as admins
        ]);

        $expiresIn = (int) env('JWT_EXPIRY', 86400);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Login successful',
            'data'    => [
                'token'      => $token,
                'expires_in' => $expiresIn,
                'user'       => [
                    'id'       => 1,
                    'username' => $username,
                    'role'     => 'admin',
                ],
            ],
        ]);
    }

    /**
     * POST /api/logout
     * 
     * Client-side logout — JWT is stateless, so we just acknowledge.
     * Client should delete the token from localStorage.
     */
    public function logout()
    {
        return $this->respond([
            'status'  => 'success',
            'message' => 'Logged out successfully. Please delete the token on client side.',
        ]);
    }
}
