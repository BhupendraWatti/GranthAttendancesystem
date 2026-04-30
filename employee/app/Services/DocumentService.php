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
        
        // In production, this path might be shared between admin and employee apps.
        // We default to the local writable folder as per instructions.
        $this->uploadPath = WRITEPATH . 'uploads/documents/';
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
