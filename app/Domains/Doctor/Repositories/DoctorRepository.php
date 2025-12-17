<?php

namespace App\Domains\Doctor\Repositories;

use App\Contracts\DoctorRepositoryInterface;
use App\Models\Doctor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DoctorRepository implements DoctorRepositoryInterface
{
    public function __construct(
        private readonly Doctor $doctor
    ) {}

    public function find(int $id): ?Doctor
    {
        return $this->doctor->with('clinic')->find($id);
    }

    public function create(array $data): Doctor
    {
        $doctor = $this->doctor->create($data);

        return $doctor->load('clinic');
    }

    public function update(int $id, array $data): Doctor
    {
        $doctor = $this->doctor->findOrFail($id);
        $doctor->update($data);

        return $doctor->refresh()->load('clinic');
    }

    public function delete(int $id): bool
    {
        $doctor = $this->doctor->findOrFail($id);

        return $doctor->delete();
    }

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->doctor->with('clinic');

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getByClinic(int $clinicId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->doctor->with('clinic')->where('clinic_id', $clinicId);

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getActiveByClinic(int $clinicId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->doctor->with('clinic')
            ->where('clinic_id', $clinicId)
            ->where('status', Doctor::STATUS_ACTIVE)
            ->get();
    }

    private function applyFilters($query, array $filters): void
    {
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['name'])) {
            $query->where('name', 'LIKE', '%'.$filters['name'].'%');
        }

        if (! empty($filters['specialization'])) {
            $query->where('specialization', 'LIKE', '%'.$filters['specialization'].'%');
        }
    }
}
