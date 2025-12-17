<?php

namespace App\Domains\Timeslot\Repositories;

use App\Domains\Timeslot\Contracts\TimeslotRepositoryInterface;
use App\Models\Timeslot;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TimeslotRepository implements TimeslotRepositoryInterface
{
    public function __construct(
        private readonly Timeslot $timeslot
    ) {}

    public function find(int $id): ?Timeslot
    {
        return $this->timeslot->with('doctor')->find($id);
    }

    public function create(array $data): Timeslot
    {
        $timeslot = $this->timeslot->create($data);

        return $timeslot->load('doctor');
    }

    public function update(int $id, array $data): Timeslot
    {
        $timeslot = $this->timeslot->findOrFail($id);
        $timeslot->update($data);

        return $timeslot->refresh()->load('doctor');
    }

    public function delete(int $id): bool
    {
        $timeslot = $this->timeslot->findOrFail($id);

        return $timeslot->delete();
    }

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->timeslot->with('doctor');

        $this->applyFilters($query, $filters);

        return $query->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->paginate($perPage);
    }

    public function getByDoctor(int $doctorId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->timeslot->with('doctor')->where('doctor_id', $doctorId);

        $this->applyFilters($query, $filters);

        return $query->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->paginate($perPage);
    }

    public function getAvailableByDoctor(int $doctorId, ?string $date = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->timeslot->with('doctor')
            ->where('doctor_id', $doctorId)
            ->where('is_available', 1);

        if ($date) {
            $query->whereDate('date', $date);
        }

        return $query->where(function ($q) {
            $q->whereDate('date', '>', Carbon::today())
                ->orWhere(function ($subQ) {
                    $subQ->whereDate('date', Carbon::today())
                        ->whereTime('start_time', '>', Carbon::now()->format('H:i:s'));
                });
        })
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    public function hasOverlappingSlot(int $doctorId, string $date, string $startTime, string $endTime, ?int $excludeId = null): bool
    {
        $query = $this->timeslot->where('doctor_id', $doctorId)
            ->whereDate('date', $date)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($subQ) use ($startTime, $endTime) {
                    $subQ->whereTime('start_time', '<', $endTime)
                        ->whereTime('end_time', '>', $startTime);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    private function applyFilters($query, array $filters): void
    {
        if (! empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        if (! empty($filters['start_time'])) {
            $query->whereTime('start_time', '>=', $filters['start_time']);
        }

        if (! empty($filters['end_time'])) {
            $query->whereTime('end_time', '<=', $filters['end_time']);
        }
    }

    public function createMultipleTimeslots(array $timeslots): \Illuminate\Database\Eloquent\Collection
    {
        $createdTimeslots = new \Illuminate\Database\Eloquent\Collection;

        foreach ($timeslots as $timeslotData) {
            $timeslot = $this->timeslot->create($timeslotData);
            $createdTimeslots->push($timeslot->load('doctor'));
        }

        return $createdTimeslots;
    }

    public function getOverlappingSlots(int $doctorId, string $date, string $startTime, string $endTime): \Illuminate\Database\Eloquent\Collection
    {
        return $this->timeslot
            ->where('doctor_id', $doctorId)
            ->whereDate('date', $date)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereTime('start_time', '<', $endTime)
                    ->whereTime('end_time', '>', $startTime);
            })
            ->get();
    }
}
