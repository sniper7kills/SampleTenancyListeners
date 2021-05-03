<?php

namespace App\Tenancy\Listeners\Affects\Connections;

use Tenancy\Affects\Connections\Events\Drivers\Configuring;

class ConfigureConnection
{
    public function handle(Configuring $event)
    {
        $overrides = array_merge(
            $event->defaults($event->tenant),
            [
                // Change the username and database to use the id without any tacks in it.
                'username' => str_replace('-', '', $event->tenant->getTenantKey()),
                'database' => str_replace('-', '', $event->tenant->getTenantKey()),
            ],
        );
        $event->useConnection('mysql', $overrides);
    }
}
