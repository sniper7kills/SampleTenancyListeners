<?php
namespace App\Tenancy\Listeners\Hooks\Database;

use Tenancy\Hooks\Database\Events\ConfigureDatabaseMutation;
use Tenancy\Tenant\Events\Created;
use Tenancy\Tenant\Events\Updated;
use Tenancy\Tenant\Events\Deleted;

class ConfigureHook
{
    public function handle(ConfigureDatabaseMutation $event)
    {
        if ($event->event instanceof Deleted) {
            if (! $event->event->tenant->forceDeleting) {
                $event->disable();
            }
        }
    }
}
