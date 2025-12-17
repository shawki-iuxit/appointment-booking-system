<?php

namespace App\Http\Controllers\V1;

use App\Domains\Doctor\Services\DoctorService;
use App\Domains\Doctor\Transformers\DoctorTransformer;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorController extends BaseController
{
    public function __construct(
        private readonly DoctorService $doctorService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'name', 'specialization']);
            $perPage = $request->get('per_page', 15);

            $doctors = $this->doctorService->getAllDoctors($filters, $perPage);

            return $this->successResponse(
                DoctorTransformer::transformPaginated($doctors),
                'Doctors retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve doctors',
                $e->getMessage()
            );
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $doctor = $this->doctorService->getDoctor($id);

            return $this->successResponse(
                DoctorTransformer::transform($doctor),
                'Doctor retrieved successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Doctor not found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve doctor',
                $e->getMessage()
            );
        }
    }

    public function store(CreateDoctorRequest $request): JsonResponse
    {
        try {
            $doctor = $this->doctorService->createDoctor($request->validated());

            return $this->createdResponse(
                DoctorTransformer::transform($doctor),
                'Doctor created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create doctor',
                $e->getMessage()
            );
        }
    }

    public function update(UpdateDoctorRequest $request, int $id): JsonResponse
    {
        try {
            $doctor = $this->doctorService->updateDoctor($id, $request->validated());

            return $this->successResponse(
                DoctorTransformer::transform($doctor),
                'Doctor updated successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Doctor not found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update doctor',
                $e->getMessage()
            );
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->doctorService->deleteDoctor($id);

            return $this->successResponse(
                null,
                'Doctor deleted successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Doctor not found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to delete doctor',
                $e->getMessage()
            );
        }
    }

    public function getByClinic(Request $request, int $clinicId): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'name', 'specialization']);
            $perPage = $request->get('per_page', 15);

            $doctors = $this->doctorService->getDoctorsByClinic($clinicId, $filters, $perPage);

            return $this->successResponse(
                DoctorTransformer::transformPaginated($doctors),
                'Clinic doctors retrieved successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Clinic not found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve clinic doctors',
                $e->getMessage()
            );
        }
    }
}
