<?php

namespace App\Domains\Patient\Repositories;

use App\Domains\Patient\Contracts\PatientRepositoryInterface;
use App\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PatientRepository implements PatientRepositoryInterface
{
    public function __construct(
        private readonly Patient $patient
    ) {}

    public function find(int $id): ?Patient
    {
        return $this->patient->find($id);
    }

    public function create(array $data): Patient
    {
        return $this->patient->create($data);
    }

    public function update(int $id, array $data): Patient
    {
        $patient = $this->patient->findOrFail($id);
        $patient->update($data);

        return $patient->refresh();
    }

    public function delete(int $id): bool
    {
        $patient = $this->patient->findOrFail($id);

        return $patient->delete();
    }

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->patient->newQuery();

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    private function applyFilters($query, array $filters): void
    {
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['name'])) {
            $query->where('name', 'LIKE', '%'.$filters['name'].'%');
        }

        if (! empty($filters['email'])) {
            $query->where('email', 'LIKE', '%'.$filters['email'].'%');
        }
    }

    public function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->patient->where('status', Patient::STATUS_ACTIVE)->get();
    }

    public function findByEmail(string $email): ?Patient
    {
        return $this->patient->where('email', $email)->first();
    }
}
