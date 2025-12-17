<?php

namespace App\Domains\Clinic\Services;

use App\Domains\Clinic\Contacts\ClinicRepositoryInterface;
use App\Models\Clinic;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ClinicService
{
    public function __construct(
        private readonly ClinicRepositoryInterface $clinicRepository
    ) {}

    public function getClinic(int $id): Clinic
    {
        $clinic = $this->clinicRepository->find($id);

        if (! $clinic) {
            throw new ModelNotFoundException('Clinic not found');
        }

        return $clinic;
    }

    public function getAllClinics(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->clinicRepository->getAll($filters, $perPage);
    }

    public function getActiveClinics(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->clinicRepository->getActive();
    }

    public function createClinic(array $data): Clinic
    {
        $this->validateClinicData($data);

        return $this->clinicRepository->create($data);
    }

    public function updateClinic(int $id, array $data): Clinic
    {
        $this->validateClinicData($data, $id);

        return $this->clinicRepository->update($id, $data);
    }

    public function deleteClinic(int $id): bool
    {
        return $this->clinicRepository->delete($id);
    }

    public function activateClinic(int $id): Clinic
    {
        $clinic = $this->getClinic($id);
        $clinic->activate();
        $clinic->save();

        return $clinic;
    }

    public function deactivateClinic(int $id): Clinic
    {
        $clinic = $this->getClinic($id);
        $clinic->deactivate();
        $clinic->save();

        return $clinic;
    }

    private function validateClinicData(array $data, ?int $excludeId = null): void
    {
        if (empty($data['name'])) {
            throw new ValidationException('Clinic name is required');
        }

        if (strlen($data['name']) < 2) {
            throw new ValidationException('Clinic name must be at least 2 characters');
        }

        if (isset($data['status']) && ! in_array($data['status'], [Clinic::STATUS_ACTIVE, Clinic::STATUS_INACTIVE])) {
            throw new ValidationException('Invalid status value');
        }
    }
}
