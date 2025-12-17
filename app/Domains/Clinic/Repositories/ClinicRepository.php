<?php

namespace App\Domains\Clinic\Repositories;

use App\Contracts\ClinicRepositoryInterface;
use App\Models\Clinic;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClinicRepository implements ClinicRepositoryInterface
{
    public function __construct(
        private readonly Clinic $clinic
    ) {}

    public function find(int $id): ?Clinic
    {
        return $this->clinic->find($id);
    }

    public function create(array $data): Clinic
    {
        return $this->clinic->create($data);
    }

    public function update(int $id, array $data): Clinic
    {
        $clinic = $this->clinic->findOrFail($id);
        $clinic->update($data);
        return $clinic->refresh();
    }

    public function delete(int $id): bool
    {
        $clinic = $this->clinic->findOrFail($id);
        return $clinic->delete();
    }

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->clinic->newQuery();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'LIKE', '%' . $filters['name'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->clinic->where('status', Clinic::STATUS_ACTIVE)->get();
    }
}