<?php

namespace App\Domains\Timeslot\Services;

use App\Contracts\DoctorRepositoryInterface;
use App\Domains\Timeslot\Contracts\TimeslotRepositoryInterface;
use App\Models\Timeslot;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class TimeslotService
{
    public function __construct(
        private readonly TimeslotRepositoryInterface $timeslotRepository,
        private readonly DoctorRepositoryInterface $doctorRepository
    ) {}

    public function getTimeslot(int $id): Timeslot
    {
        $timeslot = $this->timeslotRepository->find($id);

        if (! $timeslot) {
            throw new ModelNotFoundException('Timeslot not found');
        }

        return $timeslot;
    }

    public function getAllTimeslots(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->timeslotRepository->getAll($filters, $perPage);
    }

    public function getTimeslotsByDoctor(int $doctorId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $this->validateDoctorExists($doctorId);

        return $this->timeslotRepository->getByDoctor($doctorId, $filters, $perPage);
    }

    public function getAvailableTimeslotsByDoctor(int $doctorId, ?string $date = null): \Illuminate\Database\Eloquent\Collection
    {
        $this->validateDoctorExists($doctorId);

        return $this->timeslotRepository->getAvailableByDoctor($doctorId, $date);
    }

    public function createTimeslot(array $data): Timeslot
    {
        $this->validateTimeslotData($data);
        $this->validateDoctorExists($data['doctor_id']);
        $this->validateNoOverlap($data['doctor_id'], $data['date'], $data['start_time'], $data['end_time']);

        return $this->timeslotRepository->create($data);
    }

    public function createMultipleTimeslots(array $data): Collection
    {
        $this->validateDoctorExists($data['doctor_id']);

        // Check if the entire time range overlaps with existing slots
        $overlappingSlots = $this->timeslotRepository->getOverlappingSlots(
            $data['doctor_id'],
            $data['date'],
            $data['start_time'],
            $data['end_time']
        );

        if ($overlappingSlots->isNotEmpty()) {
            $overlappingTimes = $overlappingSlots->map(function ($slot) {
                return $slot->start_time->format('H:i').'-'.$slot->end_time->format('H:i');
            })->join(', ');

            throw ValidationException::withMessages([
                'time_range' => "The specified time range overlaps with existing timeslots: {$overlappingTimes}",
            ]);
        }

        $timeslots = $this->generateTimeslots($data);

        return $this->timeslotRepository->createMultipleTimeslots($timeslots);
    }

    public function deleteTimeslot(int $id): bool
    {
        return $this->timeslotRepository->delete($id);
    }

    private function validateTimeslotData(array $data): void
    {
        if (isset($data['date'])) {
            $date = Carbon::parse($data['date']);
            if ($date->isPast()) {
                throw ValidationException::withMessages([
                    'date' => 'Cannot create timeslot for past dates',
                ]);
            }
        }

        if (isset($data['start_time']) && isset($data['end_time'])) {
            $startTime = Carbon::createFromFormat('H:i', $data['start_time']);
            $endTime = Carbon::createFromFormat('H:i', $data['end_time']);

            if ($endTime->lte($startTime)) {
                throw ValidationException::withMessages([
                    'end_time' => 'End time must be after start time',
                ]);
            }

            $durationMinutes = $startTime->diffInMinutes($endTime);
            if ($durationMinutes < 15) {
                throw ValidationException::withMessages([
                    'end_time' => 'Timeslot duration must be at least 15 minutes',
                ]);
            }

            if ($durationMinutes > 480) { // 8 hours
                throw ValidationException::withMessages([
                    'end_time' => 'Timeslot duration cannot exceed 8 hours',
                ]);
            }
        }

        if (empty($data['doctor_id'])) {
            throw ValidationException::withMessages([
                'doctor_id' => 'Doctor ID is required',
            ]);
        }
    }

    private function validateDoctorExists(int $doctorId): void
    {
        $doctor = $this->doctorRepository->find($doctorId);

        if (! $doctor) {
            throw new ModelNotFoundException('Doctor not found');
        }

        if (! $doctor->isActive()) {
            throw ValidationException::withMessages([
                'doctor_id' => 'Cannot create timeslot for inactive doctor',
            ]);
        }
    }

    private function validateNoOverlap(int $doctorId, string $date, string $startTime, string $endTime, ?int $excludeId = null): void
    {
        if ($this->timeslotRepository->hasOverlappingSlot($doctorId, $date, $startTime, $endTime, $excludeId)) {
            throw ValidationException::withMessages([
                'time_range' => 'This timeslot overlaps with an existing timeslot',
            ]);
        }
    }

    private function generateTimeslots(array $data): array
    {
        $doctorId = $data['doctor_id'];
        $date = $data['date'];
        $startTime = $data['start_time'];
        $endTime = $data['end_time'];
        $durationMinutes = (int) $data['duration_minutes'];

        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);

        $timeslots = [];
        $current = $start->copy();

        while ($current->lt($end)) {
            $slotEnd = $current->copy()->addMinutes($durationMinutes);

            // Ensure we don't exceed the end time
            if ($slotEnd->gt($end)) {
                break;
            }

            $timeslots[] = [
                'doctor_id' => $doctorId,
                'date' => $date,
                'start_time' => $current->format('H:i'),
                'end_time' => $slotEnd->format('H:i'),
            ];

            $current = $slotEnd;
        }

        return $timeslots;
    }
}
