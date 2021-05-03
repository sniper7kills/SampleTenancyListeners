<?php
namespace App\Tenancy\Listeners\Packages\Lighthouse;

use Tenancy\Affects\Configs\Events\ConfigureConfig;

class Config
{
    public function handle(ConfigureConfig $event)
    {
        if (!is_null($event->event->tenant)) {
            // Register our Tenant root schema
            $event->set('lighthouse.schema', ['register' =>  base_path('/graphql/tenant.graphql')]);
        }
    }
}
