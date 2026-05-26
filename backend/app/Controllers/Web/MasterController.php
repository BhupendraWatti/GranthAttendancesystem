<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\DepartmentModel;
use App\Models\DesignationModel;
use App\Models\ShiftModel;

class MasterController extends BaseController
{
    /**
     * GET /master/shifts — Manage Shifts
     */
    public function shifts()
    {
        $model = new ShiftModel();
        return view('pages/master/shifts', [
            'pageTitle'  => 'Shift Management',
            'activePage' => 'master',
            'shifts'     => $model->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    /**
     * POST /master/shifts — Add/Update Shift
     */
    public function saveShift()
    {
        $id = $this->request->getPost('id');
        $data = [
            'name'            => $this->request->getPost('name'),
            'start_time'      => $this->request->getPost('start_time'),
            'end_time'        => $this->request->getPost('end_time'),
            'expected_hours'  => $this->request->getPost('expected_hours') ?: 8.5,
            'grace_minutes'   => $this->request->getPost('grace_minutes') ?: 30,
            'is_intern_shift' => $this->request->getPost('is_intern_shift') ? 1 : 0,
            'status'          => $this->request->getPost('status') ?: 'active',
        ];

        $model = new ShiftModel();
        if ($id) {
            $data['id'] = $id; // Pass ID for is_unique validation
            if (!$model->update($id, $data)) {
                return redirect()->back()->with('error', implode(', ', $model->errors()));
            }
            $msg = 'Shift updated successfully.';
        } else {
            if (!$model->insert($data)) {
                return redirect()->back()->with('error', implode(', ', $model->errors()));
            }
            $msg = 'New shift created successfully.';
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * POST /master/shifts/delete — Delete Shift
     */
    public function deleteShift()
    {
        $id = $this->request->getPost('id');
        if ($id) {
            $model = new ShiftModel();
            $model->delete($id);
            return redirect()->back()->with('success', 'Shift deleted successfully.');
        }
        return redirect()->back()->with('error', 'Invalid shift ID.');
    }

    /**
     * GET /master/departments — Manage Departments
     */
    public function departments()
    {
        $deptModel = new DepartmentModel();
        $desigModel = new DesignationModel();

        return view('pages/master/departments', [
            'pageTitle'   => 'Org Structure',
            'activePage'  => 'master',
            'departments' => $deptModel->orderBy('name', 'ASC')->findAll(),
            'designations' => $desigModel->select('designations.*, departments.name as dept_name')
                                         ->join('departments', 'departments.id = designations.department_id', 'left')
                                         ->orderBy('dept_name', 'ASC')
                                         ->orderBy('designations.name', 'ASC')
                                         ->findAll(),
        ]);
    }

    /**
     * POST /master/departments — Save Department
     */
    public function saveDepartment()
    {
        $id = $this->request->getPost('id');
        $data = [
            'name'   => $this->request->getPost('name'),
            'status' => $this->request->getPost('status') ?: 'active',
        ];

        $model = new DepartmentModel();
        if ($id) {
            $model->update($id, $data);
        } else {
            $model->insert($data);
        }

        return redirect()->back()->with('success', 'Department saved successfully.');
    }

    /**
     * POST /master/designations — Save Designation
     */
    public function saveDesignation()
    {
        $id = $this->request->getPost('id');
        $data = [
            'name'          => $this->request->getPost('name'),
            'department_id' => $this->request->getPost('department_id'),
            'status'        => $this->request->getPost('status') ?: 'active',
        ];

        $model = new DesignationModel();
        if ($id) {
            $model->update($id, $data);
        } else {
            $model->insert($data);
        }

        return redirect()->back()->with('success', 'Designation saved successfully.');
    }

    /**
     * POST /master/departments/delete — Delete Department
     */
    public function deleteDepartment()
    {
        $id = $this->request->getPost('id');
        if ($id) {
            $model = new DepartmentModel();
            $model->delete($id);
            return redirect()->back()->with('success', 'Department deleted successfully.');
        }
        return redirect()->back()->with('error', 'Invalid department ID.');
    }

    /**
     * POST /master/designations/delete — Delete Designation
     */
    public function deleteDesignation()
    {
        $id = $this->request->getPost('id');
        if ($id) {
            $model = new DesignationModel();
            $model->delete($id);
            return redirect()->back()->with('success', 'Designation deleted successfully.');
        }
        return redirect()->back()->with('error', 'Invalid designation ID.');
    }
}
