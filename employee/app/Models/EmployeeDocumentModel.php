<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeDocumentModel extends Model
{
    protected $table            = 'employee_documents';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'emp_code',
        'title',
        'file_path',
        'document_type',
        'version',
        'uploaded_by',
    ];

    protected $validationRules = [
        'emp_code'      => 'required',
        'title'         => 'required|max_length[255]',
        'file_path'     => 'required|max_length[512]',
        'document_type' => 'required|max_length[100]',
    ];
}
