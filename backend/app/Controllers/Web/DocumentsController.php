<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use App\Models\EmployeeDocumentModel;
use App\Models\CompanyDocumentModel;
use App\Services\DocumentUploadService;
use CodeIgniter\HTTP\ResponseInterface;

class DocumentsController extends BaseController
{
    protected DocumentUploadService $uploadService;
    protected EmployeeDocumentModel $employeeDocModel;
    protected CompanyDocumentModel $companyDocModel;
    protected EmployeeModel $employeeModel;

    public function __construct()
    {
        $this->uploadService = \Config\Services::documentuploadservice();
        $this->employeeDocModel = new EmployeeDocumentModel();
        $this->companyDocModel = new CompanyDocumentModel();
        $this->employeeModel = new EmployeeModel();
    }

    /**
     * Employee Documents Management Page
     */
    public function employee()
    {
        $data = [
            'pageTitle'  => 'Employee Documents',
            'activePage' => 'documents',
            'employees'  => $this->employeeModel->getActive(),
            'documents'  => $this->employeeDocModel
                ->select('employee_documents.*, employees.name as employee_name')
                ->join('employees', 'employees.emp_code = employee_documents.emp_code')
                ->orderBy('created_at', 'DESC')
                ->findAll(),
        ];

        return view('pages/documents_employee', $data);
    }

    /**
     * Company Documents Management Page
     */
    public function company()
    {
        $data = [
            'pageTitle'  => 'Company Documents',
            'activePage' => 'documents',
            'documents'  => $this->companyDocModel->orderBy('created_at', 'DESC')->findAll(),
        ];

        return view('pages/documents_company', $data);
    }

    /**
     * POST /documents/upload/employee
     */
    public function uploadEmployee()
    {
        $rules = [
            'emp_codes'     => 'required',
            'title'         => 'required|min_length[3]|max_length[255]',
            'document_type' => 'required',
            'document'      => 'uploaded[document]|max_size[document,5120]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $empCodes = $this->request->getPost('emp_codes'); 
        $title    = $this->request->getPost('title');
        $docType  = $this->request->getPost('document_type');
        $file     = $this->request->getFile('document');

        if (!is_array($empCodes)) {
            $empCodes = [$empCodes];
        }

        try {
            $this->uploadService->uploadEmployeeDocuments($file, $empCodes, $title, $docType, session()->get('user_id'));
            return redirect()->back()->with('success', 'Document(s) uploaded successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * POST /documents/upload/company
     */
    public function uploadCompany()
    {
        $rules = [
            'title'    => 'required|min_length[3]|max_length[255]',
            'category' => 'required',
            'document' => 'uploaded[document]|max_size[document,10240]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $title    = $this->request->getPost('title');
        $category = $this->request->getPost('category');
        $file     = $this->request->getFile('document');

        try {
            $this->uploadService->uploadCompanyDocument($file, $title, $category, session()->get('user_id'));
            return redirect()->back()->with('success', 'Company document uploaded successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Secure Download
     */
    public function download(string $type, int $id)
    {
        $doc = null;
        if ($type === 'employee') {
            $doc = $this->employeeDocModel->find($id);
        } else {
            $doc = $this->companyDocModel->find($id);
        }

        if (!$doc) {
            return redirect()->back()->with('error', 'Document not found.');
        }

        $path = $this->uploadService->getFullPath($doc['file_path']);

        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'File not found on server.');
        }

        return $this->response->download($path, null)->setFileName($doc['title'] . '.' . pathinfo($path, PATHINFO_EXTENSION));
    }

    /**
     * POST /documents/delete
     */
    public function delete()
    {
        $id   = $this->request->getPost('id');
        $type = $this->request->getPost('type');

        $model = ($type === 'employee') ? $this->employeeDocModel : $this->companyDocModel;
        $doc   = $model->find($id);

        if ($doc) {
            $path = $this->uploadService->getFullPath($doc['file_path']);
            if (file_exists($path)) {
                unlink($path);
            }
            $model->delete($id);
            log_message('info', "[Documents] Document deleted: ID {$id} (Type: {$type}) by User ID " . session()->get('user_id'));
            return redirect()->back()->with('success', 'Document deleted successfully.');
        }

        return redirect()->back()->with('error', 'Document not found.');
    }
}
