<?php

namespace App\Services;

use App\Models\EmployeeDocumentModel;
use App\Models\CompanyDocumentModel;
use CodeIgniter\Files\File;
use CodeIgniter\HTTP\Files\UploadedFile;

class DocumentUploadService
{
    private string $uploadPath;
    private EmployeeDocumentModel $employeeDocModel;
    private CompanyDocumentModel $companyDocModel;

    public function __construct()
    {
        $this->uploadPath = WRITEPATH . 'uploads/documents/';
        $this->employeeDocModel = new EmployeeDocumentModel();
        $this->companyDocModel = new CompanyDocumentModel();

        // Ensure directories exist
        if (!is_dir($this->uploadPath . 'employee')) {
            mkdir($this->uploadPath . 'employee', 0755, true);
        }
        if (!is_dir($this->uploadPath . 'company')) {
            mkdir($this->uploadPath . 'company', 0755, true);
        }
    }

    /**
     * Upload Employee Document (Single or Bulk)
     * 
     * @param UploadedFile $file
     * @param array $empCodes
     * @param string $title
     * @param string $docType
     * @param int|null $uploadedBy
     * @return array
     */
    public function uploadEmployeeDocuments(UploadedFile $file, array $empCodes, string $title, string $docType, ?int $uploadedBy = null): array
    {
        if (!$file->isValid()) {
            throw new \RuntimeException($file->getErrorString() . ' (' . $file->getError() . ')');
        }

        if (empty($empCodes)) {
            return [];
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $results = [];
            $firstEmpCode = $empCodes[0];
            
            // Handle first employee (moves the file)
            $latest = $this->employeeDocModel
                ->where('emp_code', $firstEmpCode)
                ->where('document_type', $docType)
                ->orderBy('version', 'DESC')
                ->first();
            $version = $latest ? ((int)$latest['version'] + 1) : 1;
            
            $randomName = $file->getRandomName();
            $safeDocType = $this->sanitize_filename($docType);
            $newName = "emp_{$firstEmpCode}_{$safeDocType}_v{$version}_{$randomName}";
            
            if (!$file->hasMoved()) {
                $file->move($this->uploadPath . 'employee', $newName);
            }
            $firstPath = $this->uploadPath . 'employee/' . $newName;

            $data = [
                'emp_code'      => $firstEmpCode,
                'title'         => $title,
                'file_path'     => 'employee/' . $newName,
                'document_type' => $docType,
                'version'       => $version,
                'uploaded_by'   => $uploadedBy,
            ];
            $id = $this->employeeDocModel->insert($data);
            $results[] = array_merge($data, ['id' => $id]);

            // Handle others (copy the file)
            for ($i = 1; $i < count($empCodes); $i++) {
                $empCode = $empCodes[$i];
                $latest = $this->employeeDocModel
                    ->where('emp_code', $empCode)
                    ->where('document_type', $docType)
                    ->orderBy('version', 'DESC')
                    ->first();
                $version = $latest ? ((int)$latest['version'] + 1) : 1;
                
                $nextName = "emp_{$empCode}_{$safeDocType}_v{$version}_{$randomName}";
                $nextPath = $this->uploadPath . 'employee/' . $nextName;
                
                if (!copy($firstPath, $nextPath)) {
                    throw new \RuntimeException("Failed to copy file for employee: {$empCode}");
                }

                $data = [
                    'emp_code'      => $empCode,
                    'title'         => $title,
                    'file_path'     => 'employee/' . $nextName,
                    'document_type' => $docType,
                    'version'       => $version,
                    'uploaded_by'   => $uploadedBy,
                ];
                $id = $this->employeeDocModel->insert($data);
                $results[] = array_merge($data, ['id' => $id]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                // If DB fails, we should ideally clean up the files, but move/copy already happened.
                // In production, we might want more complex rollback for files.
                throw new \RuntimeException("Database transaction failed for document upload.");
            }

            return $results;
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Upload Company Document
     * 
     * @param UploadedFile $file
     * @param string $title
     * @param string $category
     * @param int|null $uploadedBy
     * @return array
     */
    public function uploadCompanyDocument(UploadedFile $file, string $title, string $category, ?int $uploadedBy = null): array
    {
        if (!$file->isValid()) {
            throw new \RuntimeException($file->getErrorString() . ' (' . $file->getError() . ')');
        }

        // Get latest version for this category and title
        $latest = $this->companyDocModel
            ->where('title', $title)
            ->where('category', $category)
            ->orderBy('version', 'DESC')
            ->first();

        $version = $latest ? ((int)$latest['version'] + 1) : 1;

        $safeCategory = $this->sanitize_filename($category);
        $newName = "comp_{$safeCategory}_v{$version}_" . $file->getRandomName();
        $file->move($this->uploadPath . 'company', $newName);

        $data = [
            'title'       => $title,
            'file_path'   => 'company/' . $newName,
            'category'    => $category,
            'version'     => $version,
            'uploaded_by' => $uploadedBy,
        ];

        $id = $this->companyDocModel->insert($data);

        return array_merge($data, ['id' => $id]);
    }

    /**
     * Get document full path
     */
    public function getFullPath(string $relativePath): string
    {
        return $this->uploadPath . $relativePath;
    }

    /**
     * Helper to sanitize filename component
     */
    private function sanitize_filename(string $filename): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $filename);
    }
}
