<?php

namespace App\Services;

use App\Models\HolidayModel;

class HolidayService
{
    private HolidayModel $holidayModel;

    public function __construct()
    {
        $this->holidayModel = new HolidayModel();
    }

    public function getAll(): array
    {
        return $this->holidayModel->orderBy('date', 'ASC')->findAll();
    }

    public function add(array $data): bool
    {
        return $this->holidayModel->insert($data) !== false;
    }

    public function delete(int $id): bool
    {
        return $this->holidayModel->delete($id);
    }

    public function isHoliday(string $date): bool
    {
        return $this->holidayModel->isHoliday($date);
    }
}
