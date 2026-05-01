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
        
        // 1. Check Environment Variable first (Highest priority for server flexibility)
        $configuredPath = env('DOCUMENTS_STORAGE_PATH');
        if ($configuredPath) {
            $this->uploadPath = rtrim($configuredPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            return;
        }

        // 2. Verified Server Path (Based on deep diagnostics)
        // Your server structure: public_html/office/superadmin/backend/writable/...
        $serverPath = '/home/u415869585/domains/granthtech.com/public_html/office/superadmin/backend/writable/uploads/documents';
        
        if (is_dir($serverPath)) {
            $this->uploadPath = $serverPath . DIRECTORY_SEPARATOR;
        } else {
            // Fallback for local development or if server path changes
            $this->uploadPath = realpath(ROOTPATH . '../superadmin/backend/writable/uploads/documents') ?: WRITEPATH . 'uploads/documents';
            $this->uploadPath = rtrim($this->uploadPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
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
