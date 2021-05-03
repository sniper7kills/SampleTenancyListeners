<?php
namespace App\Tenancy\Listeners\Hooks\Database;

use Tenancy\Hooks\Database\Events\Drivers\Configuring;

use Tenancy\Tenant\Events\Created;
use Tenancy\Tenant\Events\Updated;
use Tenancy\Tenant\Events\Deleted;

class ConfigureHookDriver
{
    public function handle(Configuring $event)
    {
        $overrides = array_merge(
            $event->defaults($event->tenant),
            [
                // Use a wildcard host because we will be deploying to Laravel Vapor
                'host'=>'%',
                // Change the username and database to use the id without any tacks in it.
                'username' => str_replace('-', '', $event->tenant->getTenantKey()),
                'database' => str_replace('-', '', $event->tenant->getTenantKey()),
            ],
        );
        $event->useConnection('mysql', $overrides);
    }
}
