<?php

namespace App\Domains\Patient\Services;

use App\Domains\Patient\Contracts\PatientRepositoryInterface;
use App\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class PatientService
{
    public function __construct(
        private readonly PatientRepositoryInterface $patientRepository
    ) {}

    public function getPatient(int $id): Patient
    {
        $patient = $this->patientRepository->find($id);

        if (! $patient) {
            throw new ModelNotFoundException('Patient not found');
        }

        return $patient;
    }

    public function getAllPatients(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->patientRepository->getAll($filters, $perPage);
    }

    public function getActivePatients(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->patientRepository->getActive();
    }

    public function createPatient(array $data): Patient
    {
        $this->validatePatientData($data);
        $this->validateEmailUnique($data['email']);

        return $this->patientRepository->create($data);
    }

    public function updatePatient(int $id, array $data): Patient
    {
        $patient = $this->getPatient($id);

        $this->validatePatientData($data);

        if (isset($data['email']) && $data['email'] !== $patient->email) {
            $this->validateEmailUnique($data['email']);
        }

        return $this->patientRepository->update($id, $data);
    }

    public function deletePatient(int $id): bool
    {
        return $this->patientRepository->delete($id);
    }

    public function activatePatient(int $id): Patient
    {
        $patient = $this->getPatient($id);
        $patient->activate();
        $patient->save();

        return $patient;
    }

    public function deactivatePatient(int $id): Patient
    {
        $patient = $this->getPatient($id);
        $patient->deactivate();
        $patient->save();

        return $patient;
    }

    public function findByEmail(string $email): ?Patient
    {
        return $this->patientRepository->findByEmail($email);
    }

    private function validatePatientData(array $data): void
    {
        if (isset($data['name']) && (empty($data['name']) || strlen($data['name']) < 2)) {
            throw ValidationException::withMessages([
                'name' => 'Patient name must be at least 2 characters',
            ]);
        }

        if (isset($data['email']) && (! filter_var($data['email'], FILTER_VALIDATE_EMAIL))) {
            throw ValidationException::withMessages([
                'email' => 'Please provide a valid email address',
            ]);
        }

        if (isset($data['status']) && ! in_array($data['status'], [Patient::STATUS_ACTIVE, Patient::STATUS_INACTIVE])) {
            throw ValidationException::withMessages([
                'status' => 'Invalid status value',
            ]);
        }
    }

    private function validateEmailUnique(string $email): void
    {
        $existingPatient = $this->patientRepository->findByEmail($email);

        if ($existingPatient) {
            throw ValidationException::withMessages([
                'email' => 'Email address is already registered',
            ]);
        }
    }
}
