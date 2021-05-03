<?php
namespace App\Tenancy\Listeners\Hooks;

use Database\Seeders\TenantSeeder;
use Tenancy\Hooks\Migration\Events\ConfigureSeeds;

use Tenancy\Tenant\Events\Created;

class SeedHook
{
    public function handle(ConfigureSeeds $event)
    {
        if ($event->event instanceof Created) {
            $event->seed(TenantSeeder::class);
        } else {
            $event->disable();
        }
    }
}
