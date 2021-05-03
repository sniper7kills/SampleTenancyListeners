<?php
namespace App\Tenancy\Listeners\Hooks;

use Tenancy\Hooks\Migration\Events\ConfigureMigrations;

// use Tenancy\Tenant\Events\Created;
// use Tenancy\Tenant\Events\Updated;
// use Tenancy\Tenant\Events\Deleted;

class MigrationHook
{
    public function handle(ConfigureMigrations $event)
    {
        $event->flush()
            ->path(database_path('tenant_migrations'));
        // if ( $event->event instanceof Created ) {
        //     // Do Something when a Tenant is created
        // } else if ( $event->event instanceof Updated ) {
        //     // Do Something else when a Tenant is updated
        // } else if ( $event->event instanceof Deleted ) {
        //     // Do Something different when a Tenant is deleted
        // }
    }
}
