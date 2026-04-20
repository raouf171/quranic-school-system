<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

// Repositories
use App\Repositories\Interfaces\StudentRepositoryInterface;
use App\Repositories\Interfaces\HalaqaRepositoryInterface;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Repositories\StudentRepository;
use App\Repositories\HalaqaRepository;
use App\Repositories\PaymentRepository;

use App\Models\Revision;
use App\Observers\RevisionObserver;
// Models
use App\Models\Attendance;
use App\Models\Memorization;
use App\Models\Payment;

// Observers
use App\Observers\AttendanceObserver;
use App\Observers\MemorizationObserver;
use App\Observers\PaymentObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository bindings (Jour 2)
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(HalaqaRepositoryInterface::class, HalaqaRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
    }

    public function boot(): void
    {
        // Enregistrer les Observers
        Attendance::observe(AttendanceObserver::class);
        Memorization::observe(MemorizationObserver::class);
        Payment::observe(PaymentObserver::class);
        Revision::observe(RevisionObserver::class);  

    }
}