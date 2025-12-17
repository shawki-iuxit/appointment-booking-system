<?php

namespace Tests\Unit\Domains\Booking\Services;

use App\Domains\Booking\Contracts\BookingRepositoryInterface;
use App\Domains\Booking\Pipelines\BookingValidationPipeline;
use App\Domains\Booking\Services\BookingService;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

class BookingServiceTest extends TestCase
{
    private BookingRepositoryInterface $mockRepository;

    private BookingValidationPipeline $mockPipeline;

    private BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(BookingRepositoryInterface::class);
        $this->mockPipeline = Mockery::mock(BookingValidationPipeline::class);
        $this->bookingService = new BookingService($this->mockRepository, $this->mockPipeline);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function get_patient_appointments_should_return_patient_appointments()
    {
        // Arrange
        $patientId = 3;
        $expectedAppointments = new Collection([
            new Appointment(['id' => 1, 'patient_id' => $patientId]),
            new Appointment(['id' => 2, 'patient_id' => $patientId]),
        ]);

        $this->mockRepository
            ->shouldReceive('getPatientAppointments')
            ->once()
            ->with($patientId)
            ->andReturn($expectedAppointments);

        // Act
        $result = $this->bookingService->getPatientAppointments($patientId);

        // Assert
        $this->assertSame($expectedAppointments, $result);
        $this->assertCount(2, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function is_time_slot_available_should_return_true_when_slot_not_booked()
    {
        $timeSlotId = 1;

        $this->mockRepository
            ->shouldReceive('isTimeSlotBooked')
            ->once()
            ->with($timeSlotId)
            ->andReturn(false);

        $result = $this->bookingService->isTimeSlotAvailable($timeSlotId);

        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function is_time_slot_available_should_return_false_when_slot_is_booked()
    {
        $timeSlotId = 1;

        $this->mockRepository
            ->shouldReceive('isTimeSlotBooked')
            ->once()
            ->with($timeSlotId)
            ->andReturn(true);

        $result = $this->bookingService->isTimeSlotAvailable($timeSlotId);

        $this->assertFalse($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function book_appointment_should_pass_through_validation_pipeline_exceptions()
    {
        // Arrange
        $timeSlotId = 1;
        $patientId = 2;
        $expectedException = new \Exception('Time slot validation failed');

        $this->mockPipeline
            ->shouldReceive('process')
            ->once()
            ->andThrow($expectedException);

        $this->mockRepository
            ->shouldNotReceive('createAppointmentWithTimeSlotUpdate');

        // Assert & Act
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Time slot validation failed');

        $this->bookingService->bookAppointment($timeSlotId, $patientId);
    }
}
