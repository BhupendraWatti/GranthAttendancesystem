<?php

namespace App\Controllers;

use App\Services\DocumentService;
use CodeIgniter\HTTP\ResponseInterface;

class Documents extends BaseController
{
    protected DocumentService $documentService;

    public function __construct()
    {
        $this->documentService = \Config\Services::documentservice();
    }

    /**
     * List all available documents for the logged-in employee
     */
    public function index(): string
    {
        $empCode = session()->get('empcode');

        $data = [
            'pageTitle'         => 'My Documents',
            'activePage'        => 'documents',
            'employeeDocuments' => $this->documentService->getEmployeeDocuments($empCode),
            'companyDocuments'  => $this->documentService->getCompanyDocuments(),
        ];

        return view('documents', $data);
    }

    /**
     * Securely download a document
     */
    public function download(string $type, int $id): ResponseInterface
    {
        $empCode = session()->get('empcode');
        
        // 1. Fetch document record
        $doc = $this->documentService->getDocumentById($id, $type);

        if (!$doc) {
            return redirect()->back()->with('error', 'Document not found.');
        }

        // 2. Security Check: Employee can only download their own or company documents
        if ($type === 'employee' && $doc['emp_code'] !== $empCode) {
            log_message('warning', "[Security] Unauthorized document access attempt by Emp: {$empCode} for Doc ID: {$id}");
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        // 3. Verify file exists on disk
        $filePath = $this->documentService->getFullFilePath($doc['file_path']);

        if (!file_exists($filePath)) {
            log_message('error', "[Storage] File missing on disk: {$filePath} for Doc ID: {$id}");
            return redirect()->back()->with('error', 'File not found on server.');
        }

        // 4. Serve file securely
        return $this->response->download($filePath, null)
            ->setFileName($doc['title'] . '.' . pathinfo($filePath, PATHINFO_EXTENSION));
    }
}
