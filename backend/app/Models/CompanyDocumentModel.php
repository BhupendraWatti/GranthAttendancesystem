<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyDocumentModel extends Model
{
    protected $table            = 'company_documents';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'title',
        'file_path',
        'category',
        'version',
        'uploaded_by',
    ];

    protected $validationRules = [
        'title'     => 'required|max_length[255]',
        'file_path' => 'required|max_length[512]',
        'category'  => 'required|max_length[100]',
    ];
}
