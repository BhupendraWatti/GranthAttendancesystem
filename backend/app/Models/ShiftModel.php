<?php

namespace App\Models;

use CodeIgniter\Model;

class ShiftModel extends Model
{
    protected $table            = 'shifts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name', 
        'start_time', 
        'end_time', 
        'expected_hours', 
        'grace_minutes', 
        'is_intern_shift', 
        'status'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'id'         => 'permit_empty|is_natural_no_zero',
        'name'       => 'required|max_length[100]|is_unique[shifts.name,id,{id}]',
        'start_time' => 'required',
        'end_time'   => 'required',
    ];
}
