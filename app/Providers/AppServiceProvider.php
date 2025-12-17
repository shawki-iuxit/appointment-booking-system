<?php

namespace App\Providers;

use App\Contracts\ClinicRepositoryInterface;
use App\Contracts\DoctorRepositoryInterface;
use App\Domains\Clinic\Repositories\ClinicRepository;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
