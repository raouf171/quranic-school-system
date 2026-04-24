<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema; // ✅ ADD THIS

// Repositories
use App\Repositories\Interfaces\StudentRepositoryInterface;
use App\Repositories\Interfaces\HalaqaRepositoryInterface;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Repositories\StudentRepository;
use App\Repositories\HalaqaRepository;
use App\Repositories\PaymentRepository;

// Models
use App\Models\Revision;
use App\Models\Attendance;
use App\Models\Memorization;
use App\Models\Payment;

// Observers
use App\Observers\RevisionObserver;
use App\Observers\AttendanceObserver;
use App\Observers\MemorizationObserver;
use App\Observers\PaymentObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(HalaqaRepositoryInterface::class, HalaqaRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
    }

    public function boot(): void
    {
        // ✅ FIX for MySQL key length issue
        Schema::defaultStringLength(191);

        // Observers
        Attendance::observe(AttendanceObserver::class);
        Memorization::observe(MemorizationObserver::class);
        Payment::observe(PaymentObserver::class);
        Revision::observe(RevisionObserver::class);
    }
}