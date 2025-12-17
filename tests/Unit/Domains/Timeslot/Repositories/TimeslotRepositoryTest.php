<?php

namespace Tests\Unit\Domains\Timeslot\Repositories;

use App\Domains\Timeslot\Repositories\TimeslotRepository;
use App\Models\Timeslot;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class TimeslotRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TimeslotRepository $repository;

    private Timeslot $mockTimeslot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockTimeslot = Mockery::mock(Timeslot::class);
        $this->repository = new TimeslotRepository($this->mockTimeslot);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function find_should_return_timeslot_with_doctor_when_exists()
    {
        $timeslotId = 1;
        $expectedTimeslot = new Timeslot(['id' => $timeslotId]);

        $this->mockTimeslot
            ->shouldReceive('with')
            ->once()
            ->with('doctor')
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('find')
            ->once()
            ->with($timeslotId)
            ->andReturn($expectedTimeslot);

        $result = $this->repository->find($timeslotId);

        $this->assertSame($expectedTimeslot, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function find_should_return_null_when_not_exists()
    {
        $timeslotId = 999;

        $this->mockTimeslot
            ->shouldReceive('with')
            ->once()
            ->with('doctor')
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('find')
            ->once()
            ->with($timeslotId)
            ->andReturn(null);

        $result = $this->repository->find($timeslotId);

        $this->assertNull($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function create_should_create_and_return_timeslot_with_doctor()
    {
        $data = [
            'doctor_id' => 1,
            'date' => '2025-12-18',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'is_available' => true,
        ];

        $createdTimeslot = Mockery::mock(Timeslot::class);
        $loadedTimeslot = new Timeslot($data);

        $this->mockTimeslot
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($createdTimeslot);

        $createdTimeslot
            ->shouldReceive('load')
            ->once()
            ->with('doctor')
            ->andReturn($loadedTimeslot);

        $result = $this->repository->create($data);

        $this->assertSame($loadedTimeslot, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function delete_should_delete_timeslot_and_return_true()
    {
        $timeslotId = 1;
        $mockTimeslot = Mockery::mock(Timeslot::class);

        $this->mockTimeslot
            ->shouldReceive('findOrFail')
            ->once()
            ->with($timeslotId)
            ->andReturn($mockTimeslot);

        $mockTimeslot
            ->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $result = $this->repository->delete($timeslotId);

        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function delete_should_throw_exception_when_timeslot_not_found()
    {
        $timeslotId = 999;

        $this->mockTimeslot
            ->shouldReceive('findOrFail')
            ->once()
            ->with($timeslotId)
            ->andThrow(new ModelNotFoundException);

        $this->expectException(ModelNotFoundException::class);

        $this->repository->delete($timeslotId);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function get_all_should_return_paginated_timeslots_with_doctor()
    {
        $filters = [];
        $perPage = 15;
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mockTimeslot
            ->shouldReceive('with')
            ->once()
            ->with('doctor')
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('orderBy')
            ->once()
            ->with('date', 'asc')
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('orderBy')
            ->once()
            ->with('start_time', 'asc')
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('paginate')
            ->once()
            ->with($perPage)
            ->andReturn($mockPaginator);

        $result = $this->repository->getAll($filters, $perPage);

        $this->assertSame($mockPaginator, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function get_all_should_apply_date_filter()
    {
        $filters = ['date' => '2025-12-18'];
        $perPage = 15;
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mockTimeslot
            ->shouldReceive('with')
            ->once()
            ->with('doctor')
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('whereDate')
            ->once()
            ->with('date', '2025-12-18')
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('orderBy')
            ->twice()
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('paginate')
            ->once()
            ->with($perPage)
            ->andReturn($mockPaginator);

        $result = $this->repository->getAll($filters, $perPage);

        $this->assertSame($mockPaginator, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function get_all_should_apply_date_range_filters()
    {
        $filters = [
            'date_from' => '2025-12-18',
            'date_to' => '2025-12-25',
        ];
        $perPage = 15;
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mockTimeslot
            ->shouldReceive('with')
            ->once()
            ->with('doctor')
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('whereDate')
            ->once()
            ->with('date', '>=', '2025-12-18')
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('whereDate')
            ->once()
            ->with('date', '<=', '2025-12-25')
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('orderBy')
            ->twice()
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('paginate')
            ->once()
            ->with($perPage)
            ->andReturn($mockPaginator);

        $result = $this->repository->getAll($filters, $perPage);

        $this->assertSame($mockPaginator, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function get_all_should_apply_time_filters()
    {
        $filters = [
            'start_time' => '09:00',
            'end_time' => '17:00',
        ];
        $perPage = 15;
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mockTimeslot
            ->shouldReceive('with')
            ->once()
            ->with('doctor')
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('whereTime')
            ->once()
            ->with('start_time', '>=', '09:00')
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('whereTime')
            ->once()
            ->with('end_time', '<=', '17:00')
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('orderBy')
            ->twice()
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('paginate')
            ->once()
            ->with($perPage)
            ->andReturn($mockPaginator);

        $result = $this->repository->getAll($filters, $perPage);

        $this->assertSame($mockPaginator, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function get_by_doctor_should_return_paginated_timeslots_for_doctor()
    {
        $doctorId = 1;
        $filters = [];
        $perPage = 15;
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mockTimeslot
            ->shouldReceive('with')
            ->once()
            ->with('doctor')
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('where')
            ->once()
            ->with('doctor_id', $doctorId)
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('orderBy')
            ->twice()
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('paginate')
            ->once()
            ->with($perPage)
            ->andReturn($mockPaginator);

        $result = $this->repository->getByDoctor($doctorId, $filters, $perPage);

        $this->assertSame($mockPaginator, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function get_available_by_doctor_should_return_available_future_timeslots()
    {
        $doctorId = 1;
        $today = Carbon::today();
        $now = Carbon::now();
        $expectedTimeslots = new Collection([new Timeslot(['id' => 1])]);

        $this->mockTimeslot
            ->shouldReceive('with')
            ->once()
            ->with('doctor')
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('where')
            ->once()
            ->with('doctor_id', $doctorId)
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('where')
            ->once()
            ->with('is_available', 1)
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('where')
            ->once()
            ->with(Mockery::type('Closure'))
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('orderBy')
            ->twice()
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('get')
            ->once()
            ->andReturn($expectedTimeslots);

        Carbon::setTestNow($now);

        $result = $this->repository->getAvailableByDoctor($doctorId);

        $this->assertSame($expectedTimeslots, $result);

        Carbon::setTestNow();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function get_available_by_doctor_should_filter_by_date_when_provided()
    {
        $doctorId = 1;
        $date = '2025-12-18';
        $expectedTimeslots = new Collection([new Timeslot(['id' => 1])]);

        $this->mockTimeslot
            ->shouldReceive('with')
            ->once()
            ->with('doctor')
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('where')
            ->once()
            ->with('doctor_id', $doctorId)
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('where')
            ->once()
            ->with('is_available', 1)
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('whereDate')
            ->once()
            ->with('date', $date)
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('where')
            ->once()
            ->with(Mockery::type('Closure'))
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('orderBy')
            ->twice()
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('get')
            ->once()
            ->andReturn($expectedTimeslots);

        $result = $this->repository->getAvailableByDoctor($doctorId, $date);

        $this->assertSame($expectedTimeslots, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function has_overlapping_slot_should_return_true_when_overlap_exists()
    {
        $doctorId = 1;
        $date = '2025-12-18';
        $startTime = '09:00';
        $endTime = '10:00';

        $this->mockTimeslot
            ->shouldReceive('where')
            ->once()
            ->with('doctor_id', $doctorId)
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('whereDate')
            ->once()
            ->with('date', $date)
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('where')
            ->once()
            ->with(Mockery::type('Closure'))
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('exists')
            ->once()
            ->andReturn(true);

        $result = $this->repository->hasOverlappingSlot($doctorId, $date, $startTime, $endTime);

        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function has_overlapping_slot_should_return_false_when_no_overlap_exists()
    {
        $doctorId = 1;
        $date = '2025-12-18';
        $startTime = '09:00';
        $endTime = '10:00';

        $this->mockTimeslot
            ->shouldReceive('where')
            ->once()
            ->with('doctor_id', $doctorId)
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('whereDate')
            ->once()
            ->with('date', $date)
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('where')
            ->once()
            ->with(Mockery::type('Closure'))
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('exists')
            ->once()
            ->andReturn(false);

        $result = $this->repository->hasOverlappingSlot($doctorId, $date, $startTime, $endTime);

        $this->assertFalse($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function has_overlapping_slot_should_exclude_specified_timeslot()
    {
        $doctorId = 1;
        $date = '2025-12-18';
        $startTime = '09:00';
        $endTime = '10:00';
        $excludeId = 5;

        $this->mockTimeslot
            ->shouldReceive('where')
            ->once()
            ->with('doctor_id', $doctorId)
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('whereDate')
            ->once()
            ->with('date', $date)
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('where')
            ->once()
            ->with(Mockery::type('Closure'))
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('where')
            ->once()
            ->with('id', '!=', $excludeId)
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('exists')
            ->once()
            ->andReturn(false);

        $result = $this->repository->hasOverlappingSlot($doctorId, $date, $startTime, $endTime, $excludeId);

        $this->assertFalse($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function create_multiple_timeslots_should_create_and_return_collection_of_timeslots()
    {
        $timeslotsData = [
            ['doctor_id' => 1, 'start_time' => '09:00', 'end_time' => '10:00'],
            ['doctor_id' => 1, 'start_time' => '10:00', 'end_time' => '11:00'],
        ];

        $mockTimeslot1 = Mockery::mock(Timeslot::class);
        $mockTimeslot2 = Mockery::mock(Timeslot::class);

        $loadedTimeslot1 = new Timeslot(['id' => 1]);
        $loadedTimeslot2 = new Timeslot(['id' => 2]);

        $this->mockTimeslot
            ->shouldReceive('create')
            ->once()
            ->with($timeslotsData[0])
            ->andReturn($mockTimeslot1);

        $this->mockTimeslot
            ->shouldReceive('create')
            ->once()
            ->with($timeslotsData[1])
            ->andReturn($mockTimeslot2);

        $mockTimeslot1
            ->shouldReceive('load')
            ->once()
            ->with('doctor')
            ->andReturn($loadedTimeslot1);

        $mockTimeslot2
            ->shouldReceive('load')
            ->once()
            ->with('doctor')
            ->andReturn($loadedTimeslot2);

        $result = $this->repository->createMultipleTimeslots($timeslotsData);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertSame($loadedTimeslot1, $result->first());
        $this->assertSame($loadedTimeslot2, $result->last());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function get_overlapping_slots_should_return_overlapping_timeslots()
    {
        $doctorId = 1;
        $date = '2025-12-18';
        $startTime = '09:00';
        $endTime = '10:00';
        $expectedTimeslots = new Collection([
            new Timeslot(['id' => 1]),
            new Timeslot(['id' => 2]),
        ]);

        $this->mockTimeslot
            ->shouldReceive('where')
            ->once()
            ->with('doctor_id', $doctorId)
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('whereDate')
            ->once()
            ->with('date', $date)
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('where')
            ->once()
            ->with(Mockery::type('Closure'))
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('get')
            ->once()
            ->andReturn($expectedTimeslots);

        $result = $this->repository->getOverlappingSlots($doctorId, $date, $startTime, $endTime);

        $this->assertSame($expectedTimeslots, $result);
    }
}
