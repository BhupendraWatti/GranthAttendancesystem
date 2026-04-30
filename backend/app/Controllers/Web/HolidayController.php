<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\HolidayModel;

class HolidayController extends BaseController
{
    private HolidayModel $holidayModel;

    public function __construct()
    {
        $this->holidayModel = new HolidayModel();
    }

    public function index()
    {
        $data = [
            'pageTitle'  => 'Holiday Management',
            'activePage' => 'holidays',
            'holidays'   => $this->holidayModel->orderBy('date', 'ASC')->findAll(),
        ];

        return view('pages/holidays_admin', $data);
    }

    public function add()
    {
        $rules = [
            'title' => 'required|min_length[3]',
            'date'  => 'required|valid_date|is_unique[holidays.date]',
            'type'  => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Invalid input or date already exists.');
        }

        $this->holidayModel->insert($this->request->getPost());
        return redirect()->back()->with('success', 'Holiday added successfully.');
    }

    public function delete()
    {
        $id = $this->request->getPost('id');
        $this->holidayModel->delete($id);
        return redirect()->back()->with('success', 'Holiday deleted.');
    }
}
