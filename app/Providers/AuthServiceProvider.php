<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Group;
use App\Models\Skip;
use App\Policies\GroupPolicy;
use App\Policies\SkipPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Group::class => GroupPolicy::class,
        Skip::class => SkipPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
