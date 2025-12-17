<?php

namespace App\Contracts;

use App\Models\Clinic;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ClinicRepositoryInterface
{
    public function find(int $id): ?Clinic;

    public function create(array $data): Clinic;

    public function update(int $id, array $data): Clinic;

    public function delete(int $id): bool;

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function getActive(): \Illuminate\Database\Eloquent\Collection;
}