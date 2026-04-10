<?php

namespace App\Providers;

use App\Contracts\EmailGateway;
use App\Contracts\SmsGateway;
use App\Contracts\WhatsAppGateway;
use App\Events\PaymentReceived;
use App\Listeners\DispatchPostPaymentActions;
use App\Models\Batch;
use App\Models\AttendanceSession;
use App\Models\BatchSchedule;
use App\Models\FeeHead;
use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentDue;
use App\Models\StudentEnrollment;
use App\Models\StudentFeeOverride;
use App\Models\Teacher;
use App\Policies\BatchPolicy;
use App\Policies\AttendanceSessionPolicy;
use App\Policies\BatchSchedulePolicy;
use App\Policies\FeeHeadPolicy;
use App\Policies\FeeStructurePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\StudentPolicy;
use App\Policies\StudentDuePolicy;
use App\Policies\StudentEnrollmentPolicy;
use App\Policies\StudentFeeOverridePolicy;
use App\Policies\TeacherPolicy;
use Illuminate\Pagination\Paginator;
use App\Support\CurrentTenant;
use App\Services\BillingPolicyResolver;
use App\Services\AttendanceService;
use App\Services\FeeCollectionService;
use App\Services\DueLedgerService;
use App\Services\FeeStructureResolver;
use App\Services\FeeDueCalculator;
use App\Services\Gateways\LogEmailGateway;
use App\Services\Gateways\LogSmsGateway;
use App\Services\Gateways\LogWhatsAppGateway;
use App\Services\PaymentPostActionService;
use App\Services\PostPaymentSettingsResolver;
use App\Services\ReceiptNumberService;
use App\Services\ReceiptTemplateService;
use App\Services\TenantSettingsService;
use App\Support\DataScope;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Event;
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
        $this->app->scoped(AttendanceService::class, fn () => new AttendanceService());
        $this->app->singleton(SmsGateway::class, fn () => new LogSmsGateway());
        $this->app->singleton(WhatsAppGateway::class, fn () => new LogWhatsAppGateway());
        $this->app->singleton(EmailGateway::class, fn () => new LogEmailGateway());
        $this->app->scoped(BillingPolicyResolver::class, fn () => new BillingPolicyResolver());
        $this->app->scoped(TenantSettingsService::class, fn () => new TenantSettingsService());
        $this->app->scoped(PostPaymentSettingsResolver::class, fn ($app) => new PostPaymentSettingsResolver(
            $app->make(TenantSettingsService::class),
        ));
        $this->app->scoped(FeeStructureResolver::class, fn ($app) => new FeeStructureResolver($app->make(BillingPolicyResolver::class)));
        $this->app->scoped(ReceiptNumberService::class, fn () => new ReceiptNumberService());
        $this->app->scoped(ReceiptTemplateService::class, fn () => new ReceiptTemplateService());
        $this->app->scoped(FeeDueCalculator::class, fn () => new FeeDueCalculator());
        $this->app->scoped(DueLedgerService::class, fn ($app) => new DueLedgerService(
            $app->make(FeeDueCalculator::class),
            $app->make(FeeStructureResolver::class),
        ));
        $this->app->scoped(PaymentPostActionService::class, fn ($app) => new PaymentPostActionService(
            $app->make(PostPaymentSettingsResolver::class),
            $app->make(ReceiptTemplateService::class),
            $app->make(SmsGateway::class),
            $app->make(WhatsAppGateway::class),
            $app->make(EmailGateway::class),
        ));
        $this->app->scoped(FeeCollectionService::class, fn ($app) => new FeeCollectionService(
            $app->make(FeeDueCalculator::class),
            $app->make(ReceiptNumberService::class),
            $app->make(DueLedgerService::class),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();
        Gate::policy(AttendanceSession::class, AttendanceSessionPolicy::class);
        Gate::policy(Batch::class, BatchPolicy::class);
        Gate::policy(BatchSchedule::class, BatchSchedulePolicy::class);
        Gate::policy(FeeHead::class, FeeHeadPolicy::class);
        Gate::policy(FeeStructure::class, FeeStructurePolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(StudentDue::class, StudentDuePolicy::class);
        Gate::policy(StudentEnrollment::class, StudentEnrollmentPolicy::class);
        Gate::policy(StudentFeeOverride::class, StudentFeeOverridePolicy::class);
        Gate::policy(Teacher::class, TeacherPolicy::class);

        Gate::before(function ($user, string $ability): ?bool {
            if ($ability === 'super-admin') {
                return $user->hasRole(Role::SUPER_ADMIN);
            }

            return $user->hasRole(Role::SUPER_ADMIN) ? true : null;
        });

        Event::listen(PaymentReceived::class, DispatchPostPaymentActions::class);
    }
}
