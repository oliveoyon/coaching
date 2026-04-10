<?php

namespace App\Providers;

use App\Models\Batch;
use App\Models\FeeHead;
use App\Models\FeeStructure;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentFeeOverride;
use App\Models\Teacher;
use App\Policies\BatchPolicy;
use App\Policies\FeeHeadPolicy;
use App\Policies\FeeStructurePolicy;
use App\Policies\StudentPolicy;
use App\Policies\StudentEnrollmentPolicy;
use App\Policies\StudentFeeOverridePolicy;
use App\Policies\TeacherPolicy;
use Illuminate\Pagination\Paginator;
use App\Support\CurrentTenant;
use App\Services\BillingPolicyResolver;
use App\Support\DataScope;
use App\Services\FeeStructureResolver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(CurrentTenant::class, fn () => new CurrentTenant());
        $this->app->scoped(DataScope::class, fn () => new DataScope());
        $this->app->scoped(BillingPolicyResolver::class, fn () => new BillingPolicyResolver());
        $this->app->scoped(FeeStructureResolver::class, fn ($app) => new FeeStructureResolver($app->make(BillingPolicyResolver::class)));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();
        Gate::policy(Batch::class, BatchPolicy::class);
        Gate::policy(FeeHead::class, FeeHeadPolicy::class);
        Gate::policy(FeeStructure::class, FeeStructurePolicy::class);
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(StudentEnrollment::class, StudentEnrollmentPolicy::class);
        Gate::policy(StudentFeeOverride::class, StudentFeeOverridePolicy::class);
        Gate::policy(Teacher::class, TeacherPolicy::class);

        Gate::before(function ($user, string $ability): ?bool {
            if ($ability === 'super-admin') {
                return $user->hasRole(Role::SUPER_ADMIN);
            }

            return $user->hasRole(Role::SUPER_ADMIN) ? true : null;
        });
    }
}
