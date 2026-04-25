<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Services\ApiService;

/**
 * Web AuthController — Session-based Authentication
 * 
 * GET  /login  — Show login form
 * POST /login  — Authenticate via eTimeOffice and create session
 * GET  /logout — Destroy session and redirect
 */
class AuthController extends BaseController
{
    /**
     * GET /login — Show login form
     */
    public function loginForm()
    {
        // If already logged in, redirect to dashboard
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    /**
     * POST /login — Authenticate and create session
     */
    public function login()
    {
        $rules = [
            'username' => 'required|min_length[3]',
            'password' => 'required|min_length[4]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Please enter valid credentials.');
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // ---------------------------------------------------------------
        // Live eTimeOffice Authentication (same logic as API AuthController)
        // ---------------------------------------------------------------
        $companyCode   = env('ETIME_COMPANY_CODE', '');
        $etimeUsername = $companyCode ? "{$companyCode}:{$username}" : $username;

        $apiService = new ApiService();
        $apiService->setCredentials($etimeUsername, $password);

        if (!$apiService->verifyCredentials()) {
            log_message('warning', "[Web\\AuthController] eTimeOffice login failed for user: {$etimeUsername}");

            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid eTimeOffice credentials. Please try again.');
        }

        log_message('info', "[Web\\AuthController] eTimeOffice login succeeded for user: {$username}");

        // Create session
        session()->set([
            'logged_in' => true,
            'username'  => $username,
            'role'      => 'admin',
            'login_time' => date('Y-m-d H:i:s'),
        ]);

        // Redirect to intended URL or dashboard
        $redirectUrl = session()->get('redirect_url') ?? '/dashboard';
        session()->remove('redirect_url');

        return redirect()->to($redirectUrl)->with('success', "Welcome back, {$username}!");
    }

    /**
     * GET /logout — Destroy session
     */
    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login')->with('info', 'You have been signed out.');
    }
}
