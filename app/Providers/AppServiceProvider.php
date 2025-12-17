<?php

namespace App\Providers;

use App\Domains\Booking\Contracts\BookingRepositoryInterface;
use App\Domains\Booking\Repositories\BookingRepository;
use App\Domains\Clinic\Contacts\ClinicRepositoryInterface;
use App\Domains\Clinic\Repositories\ClinicRepository;
use App\Domains\Doctor\Contacts\DoctorRepositoryInterface;
use App\Domains\Doctor\Repositories\DoctorRepository;
use App\Domains\Patient\Contracts\PatientRepositoryInterface;
use App\Domains\Patient\Repositories\PatientRepository;
use App\Domains\Timeslot\Contracts\TimeslotRepositoryInterface;
use App\Domains\Timeslot\Repositories\TimeslotRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            ClinicRepositoryInterface::class,
            ClinicRepository::class
        );

        $this->app->bind(
            DoctorRepositoryInterface::class,
            DoctorRepository::class
        );

        $this->app->bind(
            TimeslotRepositoryInterface::class,
            TimeslotRepository::class
        );

        $this->app->bind(
            PatientRepositoryInterface::class,
            PatientRepository::class
        );

        $this->app->bind(
            BookingRepositoryInterface::class,
            BookingRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
