<?php

namespace App\Services;

use App\Models\EmployeeDocumentModel;
use App\Models\CompanyDocumentModel;

class DocumentService
{
    private EmployeeDocumentModel $employeeDocModel;
    private CompanyDocumentModel $companyDocModel;
    private string $uploadPath;

    public function __construct()
    {
        $this->employeeDocModel = new EmployeeDocumentModel();
        $this->companyDocModel = new CompanyDocumentModel();
        
        // Dynamic path resolution for server compatibility.
        // Priority: 1. Environment Variable, 2. Auto-discovered sibling path, 3. Local fallback.
        $configuredPath = env('DOCUMENTS_STORAGE_PATH');
        
        if ($configuredPath) {
            $this->uploadPath = rtrim($configuredPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        } else {
            // Discovery: Look for the sibling 'backend' folder dynamically
            $siblingBackend = realpath(ROOTPATH . '../backend/writable/uploads/documents');
            if ($siblingBackend && is_dir($siblingBackend)) {
                $this->uploadPath = $siblingBackend . DIRECTORY_SEPARATOR;
            } else {
                // Fallback to local app's writable folder
                $this->uploadPath = WRITEPATH . 'uploads/documents' . DIRECTORY_SEPARATOR;
            }
        }
    }

    /**
     * Get documents for a specific employee
     */
    public function getEmployeeDocuments(string $empCode): array
    {
        return $this->employeeDocModel
            ->where('emp_code', $empCode)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get all company documents (policies, etc.)
     */
    public function getCompanyDocuments(): array
    {
        return $this->companyDocModel
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get a specific document by ID and type
     */
    public function getDocumentById(int $id, string $type): ?array
    {
        if ($type === 'employee') {
            return $this->employeeDocModel->find($id);
        }
        
        return $this->companyDocModel->find($id);
    }

    /**
     * Get full filesystem path for a document
     */
    public function getFullFilePath(string $relativePath): string
    {
        return $this->uploadPath . $relativePath;
    }
}
