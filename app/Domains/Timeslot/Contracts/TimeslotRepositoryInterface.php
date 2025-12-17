<?php

namespace App\Domains\Timeslot\Contracts;

use App\Models\Timeslot;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TimeslotRepositoryInterface
{
    public function find(int $id): ?Timeslot;

    public function create(array $data): Timeslot;

    public function update(int $id, array $data): Timeslot;

    public function delete(int $id): bool;

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function getByDoctor(int $doctorId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function getAvailableByDoctor(int $doctorId, ?string $date = null): \Illuminate\Database\Eloquent\Collection;

    public function hasOverlappingSlot(int $doctorId, string $date, string $startTime, string $endTime, ?int $excludeId = null): bool;

    public function createMultipleTimeslots(array $timeslots): \Illuminate\Database\Eloquent\Collection;

    public function getOverlappingSlots(int $doctorId, string $date, string $startTime, string $endTime): \Illuminate\Database\Eloquent\Collection;
}
