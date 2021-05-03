<?php
namespace App\Tenancy;

use App\Domains\System\Models\Organization;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Tenancy\Providers\Provides\ProvidesHooks;
use Tenancy\Identification\Contracts\ResolvesTenants;

class TenancyProvider extends EventServiceProvider
{
    use ProvidesHooks;

    /**
     * T/T Ecosystem Custom Integration Hooks
     */
    protected $hooks = [

    ];

    /**
     * T/T Ecosystem Integration Event Listener Mappings
     *
     * @var array
     */
    protected $listen = [
        /**
         * Affects
         */
        \Tenancy\Affects\Connections\Events\Drivers\Configuring::class => [
            Listeners\Affects\Connections\ConfigureConnection::class,
        ],

        \Tenancy\Affects\Connections\Events\Resolving::class => [
            Listeners\Affects\Connections\ResolveConnection::class,
        ],

        \Tenancy\Affects\Routes\Events\ConfigureRoutes::class => [
            Listeners\Affects\Routes::class,
            Listeners\Packages\Jetstream\Routes::class,
            Listeners\Packages\Lighthouse\Routes::class,
        ],

        \Tenancy\Affects\Configs\Events\ConfigureConfig::class => [
            Listeners\Packages\Lighthouse\Config::class,
        ],

         // Register Affect Listeners Here

        /**
         * HOOKS
         */
        \Tenancy\Hooks\Database\Events\ConfigureDatabaseMutation::class => [
            Listeners\Hooks\Database\ConfigureHook::class,
        ],
        \Tenancy\Hooks\Database\Events\Drivers\Configuring::class => [
            Listeners\Hooks\Database\ConfigureHookDriver::class,
        ],
        \Tenancy\Hooks\Migration\Events\ConfigureMigrations::class => [
            Listeners\Hooks\MigrationHook::class,
        ],

        \Tenancy\Hooks\Migration\Events\ConfigureSeeds::class => [
            Listeners\Hooks\SeedHook::class,
        ],
         // Register Hook Listeners Here
    ];

    /**
     * Bootstrap any T/T Integration Services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        $this->runTrait('boot');

        // Additional Boot Code
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
        $this->runTrait('register');

        // Register Our Tenant Models
        $this->app->resolving(ResolvesTenants::class, function (ResolvesTenants $resolver) {
            // Duplicate this line if you have multiple Tenant models.
            $resolver->addModel(Organization::class);
            return $resolver;
        });
        // Additional Register Code
    }


    /**
     * Ensure we run run the appropriate methods
     * from the T/T trait we are forcing to be included
     * ensuring maximum flexability for integration
     *
     * @var String
     * @return void
     */
    protected function runTrait(string $runtime)
    {
        $class = static::class;

        foreach (class_uses_recursive($class) as $trait) {
            if (method_exists($class, $method = $runtime.class_basename($trait))) {
                call_user_func([$this, $method]);
            }
        }
    }
}
