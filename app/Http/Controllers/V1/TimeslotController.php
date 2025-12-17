<?php

namespace App\Http\Controllers\V1;

use App\Domains\Timeslot\Services\TimeslotService;
use App\Domains\Timeslot\Transformers\TimeslotTransformer;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CreateTimeslotRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TimeslotController extends BaseController
{
    public function __construct(
        private readonly TimeslotService $timeslotService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['date', 'date_from', 'date_to', 'start_time', 'end_time']);
            $perPage = $request->get('per_page', 15);

            $timeslots = $this->timeslotService->getAllTimeslots($filters, $perPage);

            return $this->successResponse(
                TimeslotTransformer::transformPaginated($timeslots),
                'Timeslots retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve timeslots',
                $e->getMessage()
            );
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $timeslot = $this->timeslotService->getTimeslot($id);

            return $this->successResponse(
                TimeslotTransformer::transform($timeslot),
                'Timeslot retrieved successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Timeslot not found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve timeslot',
                $e->getMessage()
            );
        }
    }

    public function store(CreateTimeslotRequest $request): JsonResponse
    {
        try {
            $timeslots = $this->timeslotService->createMultipleTimeslots($request->validated());

            return $this->createdResponse(
                TimeslotTransformer::transformCollection($timeslots),
                'Timeslots created successfully'
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create timeslots',
                $e->getMessage()
            );
        }
    }

    public function getTimeslotByDoctor(Request $request, int $doctorId): JsonResponse
    {
        try {
            $filters = $request->only(['date', 'date_from', 'date_to', 'start_time', 'end_time']);
            $perPage = $request->get('per_page', 15);

            $timeslots = $this->timeslotService->getTimeslotsByDoctor($doctorId, $filters, $perPage);

            return $this->successResponse(
                TimeslotTransformer::transformPaginated($timeslots),
                'Doctor timeslots retrieved successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Doctor not found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve doctor timeslots',
                $e->getMessage()
            );
        }
    }

    public function getAvailableByDoctor(Request $request, int $doctorId): JsonResponse
    {
        try {
            $date = $request->get('date');

            $timeslots = $this->timeslotService->getAvailableTimeslotsByDoctor($doctorId, $date);

            return $this->successResponse(
                TimeslotTransformer::transformCollection($timeslots),
                'Available doctor timeslots retrieved successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Doctor not found');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve available doctor timeslots',
                $e->getMessage()
            );
        }
    }
}
