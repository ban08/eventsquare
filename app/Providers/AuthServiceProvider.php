<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\\Models\\Model' => 'App\\Policies\\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(GateContract $gate): void
    {
        $this->registerPolicies();

        $gate->define('admin', function (User $user) {
            return Admin::where('email', $user->email)->exists();
        });
    }
}
