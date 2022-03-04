<?php
namespace App\Tenancy\Listeners\Packages\SpatiePermission;

use Tenancy\Affects\Configs\Events\ConfigureConfig;
use Tenancy\Affects\Connections\Contracts\ProvidesConfiguration;
use Tenancy\Affects\Connections\Events\Drivers\Configuring;
use Tenancy\Identification\Contracts\Tenant;

class Config implements ProvidesConfiguration
{
    public function handle(ConfigureConfig $event)
    {
        if($tenant = $event->event->tenant)
        {
            // Register our Tenant root schema
            $event->set('permission.models.role', Role::class);
            $event->set('permission.models.permission', Permission::class);
            $event->set('permission.table_names.permissions', Env('DB_DATABASE').'.'.config('permission.table_names.permissions'));
            $event->set('permission.table_names.role_has_permissions', $this->configure($tenant)['database'].'.'.config('permission.table_names.role_has_permissions'));
            $event->set('permission.table_names.model_has_permissions', 
                $this->configure($tenant)['database'].
                '.'.
                config('permission.table_names.model_has_permissions')
            );
        }
    }

    public function configure(Tenant $tenant): array
    {
        $config = [];

        event(new Configuring($tenant, $config, $this));

        return $config;
    }
}
