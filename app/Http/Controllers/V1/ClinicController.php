<?php

namespace App\Http\Controllers\V1;

use App\Domains\Clinic\Services\ClinicService;
use App\Domains\Clinic\Transformers\ClinicTransformer;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateClinicRequest;
use App\Http\Requests\UpdateClinicRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClinicController extends BaseController
{
    public function __construct(
        private readonly ClinicService $clinicService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'name']);
            $perPage = $request->get('per_page', 15);

            $clinics = $this->clinicService->getAllClinics($filters, $perPage);

            return $this->successResponse(
                ClinicTransformer::transformPaginated($clinics),
                'Clinics retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve clinics',
                $e->getMessage()
            );
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $clinic = $this->clinicService->getClinic($id);

            return $this->successResponse(
                ClinicTransformer::transform($clinic),
                'Clinic retrieved successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Clinic not found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve clinic',
                $e->getMessage()
            );
        }
    }

    public function store(CreateClinicRequest $request): JsonResponse
    {
        try {
            $clinic = $this->clinicService->createClinic($request->validated());

            return $this->createdResponse(
                ClinicTransformer::transform($clinic),
                'Clinic created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create clinic',
                $e->getMessage()
            );
        }
    }

    public function update(UpdateClinicRequest $request, int $id): JsonResponse
    {
        try {
            $clinic = $this->clinicService->updateClinic($id, $request->validated());

            return $this->successResponse(
                ClinicTransformer::transform($clinic),
                'Clinic updated successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Clinic not found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update clinic',
                $e->getMessage()
            );
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->clinicService->deleteClinic($id);

            return $this->successResponse(
                null,
                'Clinic deleted successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Clinic not found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to delete clinic',
                $e->getMessage()
            );
        }
    }
}
