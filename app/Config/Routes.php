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
$routes->setDefaultNamespace('App\Controllers\Web');
$routes->setDefaultController('DashboardController');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// AUTHENTICATION
$routes->get('/', 'AuthController::loginForm');
$routes->get('login', 'AuthController::loginForm');
$routes->post('login', 'AuthController::login');
$routes->get('logout', 'AuthController::logout');

// PROTECTED ROUTES
$routes->group('', ['filter' => 'session'], function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');

    // Sync
    $routes->get('sync', 'SyncController::index');
    $routes->post('sync/run', 'SyncController::run');

    // Employees
    $routes->get('employees', 'EmployeeController::index');
    $routes->post('employees/salary', 'EmployeeController::updateSalary');
    $routes->get('employees/(:segment)', 'EmployeeController::show/$1');

    // Salary
    $routes->get('salary', 'SalaryController::index');

    // Payslip
    $routes->get('payslip/(:segment)', 'SalaryController::payslip/$1');
    $routes->get('payslip/(:segment)/print', 'SalaryController::payslipPrint/$1');
});

// API ROUTES (explicit mappings for dashboard polling + sync controls)
$routes->group('api', ['namespace' => 'App\Controllers\Api', 'filter' => 'session'], function ($routes) {
    $routes->get('dashboard/summary', 'DashboardController::summary');
    $routes->get('dashboard/attendance', 'DashboardController::attendance');
    $routes->get('dashboard/live-punches', 'DashboardController::livePunches');

    $routes->post('sync/run', 'SyncController::run');
    $routes->get('sync/status', 'SyncController::status');
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
