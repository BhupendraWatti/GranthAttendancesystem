<?php

namespace App\Controllers;

use App\Models\EmployeeModel;

class Profile extends BaseController
{
    public function index()
    {
        $empCode = (string) session()->get('empcode');
        $employee = (new EmployeeModel())->findByCode($empCode);

        return view('profile', [
            'employee' => $employee,
        ]);
    }
}

