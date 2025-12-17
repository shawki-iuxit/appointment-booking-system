<?php

namespace Tests\Unit\Domains\Booking\Repositories;

use App\Domains\Booking\Repositories\BookingRepository;
use App\Models\Appointment;
use App\Models\Timeslot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class BookingRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private BookingRepository $repository;

    private Appointment $mockAppointment;

    private Timeslot $mockTimeslot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockAppointment = Mockery::mock(Appointment::class);
        $this->mockTimeslot = Mockery::mock(Timeslot::class);
        $this->repository = new BookingRepository($this->mockAppointment, $this->mockTimeslot);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function find_appointment_by_time_slot_should_return_appointment_when_exists()
    {
        // Arrange
        $timeSlotId = 1;
        $expectedAppointment = new Appointment(['id' => 1, 'time_slot_id' => $timeSlotId]);

        $this->mockAppointment
            ->shouldReceive('where')
            ->once()
            ->with('time_slot_id', $timeSlotId)
            ->andReturnSelf();

        $this->mockAppointment
            ->shouldReceive('first')
            ->once()
            ->andReturn($expectedAppointment);

        $result = $this->repository->findAppointmentByTimeSlot($timeSlotId);

        $this->assertSame($expectedAppointment, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function find_appointment_by_time_slot_should_return_null_when_not_exists()
    {
        // Arrange
        $timeSlotId = 999;

        $this->mockAppointment
            ->shouldReceive('where')
            ->once()
            ->with('time_slot_id', $timeSlotId)
            ->andReturnSelf();

        $this->mockAppointment
            ->shouldReceive('first')
            ->once()
            ->andReturn(null);

        $result = $this->repository->findAppointmentByTimeSlot($timeSlotId);

        $this->assertNull($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function create_appointment_with_time_slot_update_should_create_appointment_in_transaction()
    {
        // Arrange
        $timeSlotId = 1;
        $patientId = 2;
        $mockTimeslot = Mockery::mock(Timeslot::class);
        $expectedAppointment = new Appointment([
            'id' => 1,
            'time_slot_id' => $timeSlotId,
            'patient_id' => $patientId,
        ]);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $this->mockTimeslot
            ->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('findOrFail')
            ->once()
            ->with($timeSlotId)
            ->andReturn($mockTimeslot);

        $mockTimeslot
            ->shouldReceive('markAsUnavailable')
            ->once();

        $mockTimeslot
            ->shouldReceive('save')
            ->once();

        $this->mockAppointment
            ->shouldReceive('create')
            ->once()
            ->andReturn($expectedAppointment);

        $result = $this->repository->createAppointmentWithTimeSlotUpdate($timeSlotId, $patientId);

        $this->assertSame($expectedAppointment, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function create_appointment_with_time_slot_update_should_throw_exception_when_timeslot_not_found()
    {
        // Arrange
        $timeSlotId = 999;
        $patientId = 2;

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $this->mockTimeslot
            ->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $this->mockTimeslot
            ->shouldReceive('findOrFail')
            ->once()
            ->with($timeSlotId)
            ->andThrow(new ModelNotFoundException);

        $this->mockAppointment
            ->shouldNotReceive('create');

        // Assert & Act
        $this->expectException(ModelNotFoundException::class);

        $this->repository->createAppointmentWithTimeSlotUpdate($timeSlotId, $patientId);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function get_patient_appointments_should_return_patient_appointments_with_relations()
    {
        // Arrange
        $patientId = 3;
        $expectedAppointments = new Collection([
            new Appointment(['id' => 1, 'patient_id' => $patientId]),
            new Appointment(['id' => 2, 'patient_id' => $patientId]),
        ]);

        $this->mockAppointment
            ->shouldReceive('with')
            ->once()
            ->with(['timeslot.doctor'])
            ->andReturnSelf();

        $this->mockAppointment
            ->shouldReceive('where')
            ->once()
            ->with('patient_id', $patientId)
            ->andReturnSelf();

        $this->mockAppointment
            ->shouldReceive('orderBy')
            ->once()
            ->with('created_at', 'desc')
            ->andReturnSelf();

        $this->mockAppointment
            ->shouldReceive('get')
            ->once()
            ->andReturn($expectedAppointments);

        $result = $this->repository->getPatientAppointments($patientId);

        $this->assertSame($expectedAppointments, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function is_time_slot_booked_should_return_true_when_appointment_exists()
    {
        // Arrange
        $timeSlotId = 1;

        $this->mockAppointment
            ->shouldReceive('where')
            ->once()
            ->with('time_slot_id', $timeSlotId)
            ->andReturnSelf();

        $this->mockAppointment
            ->shouldReceive('exists')
            ->once()
            ->andReturn(true);

        $result = $this->repository->isTimeSlotBooked($timeSlotId);

        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function is_time_slot_booked_should_return_false_when_no_appointment_exists()
    {
        $timeSlotId = 1;

        $this->mockAppointment
            ->shouldReceive('where')
            ->once()
            ->with('time_slot_id', $timeSlotId)
            ->andReturnSelf();

        $this->mockAppointment
            ->shouldReceive('exists')
            ->once()
            ->andReturn(false);

        $result = $this->repository->isTimeSlotBooked($timeSlotId);

        $this->assertFalse($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function create_appointment_with_time_slot_update_should_set_booked_at_timestamp()
    {
        // Arrange
        $timeSlotId = 1;
        $patientId = 2;
        $mockTimeslot = Mockery::mock(Timeslot::class);
        $fixedTime = Carbon::parse('2025-12-17 10:00:00');
        Carbon::setTestNow($fixedTime);

        $expectedAppointment = new Appointment;

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $this->mockTimeslot
            ->shouldReceive('lockForUpdate->findOrFail')
            ->once()
            ->with($timeSlotId)
            ->andReturn($mockTimeslot);

        $mockTimeslot
            ->shouldReceive('markAsUnavailable')
            ->once();

        $mockTimeslot
            ->shouldReceive('save')
            ->once();

        $this->mockAppointment
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($fixedTime) {
                return $data['booked_at']->equalTo($fixedTime);
            }))
            ->andReturn($expectedAppointment);

        $result = $this->repository->createAppointmentWithTimeSlotUpdate($timeSlotId, $patientId);

        $this->assertSame($expectedAppointment, $result);

        Carbon::setTestNow();
    }
}
