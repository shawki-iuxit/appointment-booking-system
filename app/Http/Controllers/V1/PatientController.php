<?php

namespace App\Http\Controllers\V1;

use App\Domains\Patient\Services\PatientService;
use App\Domains\Patient\Transformers\PatientTransformer;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CreatePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PatientController extends BaseController
{
    public function __construct(
        private readonly PatientService $patientService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'name', 'email']);
            $perPage = $request->get('per_page', 15);

            $patients = $this->patientService->getAllPatients($filters, $perPage);

            return $this->successResponse(
                PatientTransformer::transformPaginated($patients),
                'Patients retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve patients',
                $e->getMessage()
            );
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $patient = $this->patientService->getPatient($id);

            return $this->successResponse(
                PatientTransformer::transform($patient),
                'Patient retrieved successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Patient not found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve patient',
                $e->getMessage()
            );
        }
    }

    public function store(CreatePatientRequest $request): JsonResponse
    {
        try {
            $patient = $this->patientService->createPatient($request->validated());

            return $this->createdResponse(
                PatientTransformer::transform($patient),
                'Patient created successfully'
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create patient',
                $e->getMessage()
            );
        }
    }

    public function update(UpdatePatientRequest $request, int $id): JsonResponse
    {
        try {
            $patient = $this->patientService->updatePatient($id, $request->validated());

            return $this->successResponse(
                PatientTransformer::transform($patient),
                'Patient updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Patient not found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update patient',
                $e->getMessage()
            );
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->patientService->deletePatient($id);

            return $this->successResponse(
                null,
                'Patient deleted successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Patient not found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to delete patient',
                $e->getMessage()
            );
        }
    }
}
