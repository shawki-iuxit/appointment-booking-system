<?php

namespace App\Domains\Patient\Contracts;

use App\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PatientRepositoryInterface
{
    public function find(int $id): ?Patient;

    public function create(array $data): Patient;

    public function update(int $id, array $data): Patient;

    public function delete(int $id): bool;

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
