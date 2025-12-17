<?php

namespace App\Domains\Doctor\Contacts;

use App\Models\Doctor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DoctorRepositoryInterface
{
    public function find(int $id): ?Doctor;

    public function create(array $data): Doctor;

    public function update(int $id, array $data): Doctor;

    public function delete(int $id): bool;

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function getByClinic(int $clinicId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function getActiveByClinic(int $clinicId): \Illuminate\Database\Eloquent\Collection;
}
