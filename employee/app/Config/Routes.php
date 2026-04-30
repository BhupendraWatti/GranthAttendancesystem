<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Dashboard');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// Employee Portal routes (OTP only)
$routes->get('auth/login', 'Auth::login');
$routes->post('auth/send-otp', 'Auth::sendOtp');
$routes->post('auth/verify-otp', 'Auth::verifyOtp');
$routes->get('auth/logout', 'Auth::logout');

// Legacy/accidental admin-login paths should never render in employee app
$routes->get('login', static fn () => redirect()->to(base_url('auth/login')));
$routes->post('login', static fn () => redirect()->to(base_url('auth/login')));

// Protected Employee routes
$routes->group('', ['filter' => 'employeeAuth'], function ($routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->get('attendance', 'Attendance::index');
    $routes->get('salary', 'Salary::index');
    $routes->get('salary/payslip', 'Salary::payslip');
    $routes->get('salary/payslip/print', 'Salary::payslipPrint');
    $routes->get('profile', 'Profile::index');

    // Leave Management
    $routes->get('leave', 'LeaveController::index');
    $routes->post('leave/apply', 'LeaveController::apply');

    // Notifications
    $routes->get('notifications', 'NotificationController::index');
    $routes->post('notifications/mark-read', 'NotificationController::markRead');
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
