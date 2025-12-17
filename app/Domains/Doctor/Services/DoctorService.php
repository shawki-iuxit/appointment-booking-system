<?php

namespace App\Domains\Doctor\Services;

use App\Domains\Clinic\Contacts\ClinicRepositoryInterface;
use App\Domains\Doctor\Contacts\DoctorRepositoryInterface;
use App\Models\Doctor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DoctorService
{
    public function __construct(
        private readonly DoctorRepositoryInterface $doctorRepository,
        private readonly ClinicRepositoryInterface $clinicRepository
    ) {}

    public function getDoctor(int $id): Doctor
    {
        $doctor = $this->doctorRepository->find($id);

        if (! $doctor) {
            throw new ModelNotFoundException('Doctor not found');
        }

        return $doctor;
    }

    public function getAllDoctors(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->doctorRepository->getAll($filters, $perPage);
    }

    public function getDoctorsByClinic(int $clinicId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $this->validateClinicExists($clinicId);

        return $this->doctorRepository->getByClinic($clinicId, $filters, $perPage);
    }

    public function getActiveDoctorsByClinic(int $clinicId): \Illuminate\Database\Eloquent\Collection
    {
        $this->validateClinicExists($clinicId);

        return $this->doctorRepository->getActiveByClinic($clinicId);
    }

    public function createDoctor(array $data): Doctor
    {
        $this->validateClinicExists($data['clinic_id']);

        return $this->doctorRepository->create($data);
    }

    public function updateDoctor(int $id, array $data): Doctor
    {

        if (isset($data['clinic_id'])) {
            $this->validateClinicExists($data['clinic_id']);
        }

        return $this->doctorRepository->update($id, $data);
    }

    public function deleteDoctor(int $id): bool
    {
        return $this->doctorRepository->delete($id);
    }

    private function validateClinicExists(int $clinicId): void
    {
        $clinic = $this->clinicRepository->find($clinicId);

        if (! $clinic) {
            throw new ModelNotFoundException('Clinic not found');
        }
    }
}
