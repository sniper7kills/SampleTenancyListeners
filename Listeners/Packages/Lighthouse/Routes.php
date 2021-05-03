<?php

namespace App\Tenancy\Listeners\Packages\Lighthouse;

use App\Domains\System\Middleware\CanViewTenant;
use Tenancy\Affects\Routes\Events\ConfigureRoutes;

class Routes
{
    public function handle(ConfigureRoutes $event)
    {
        if (!is_null($event->event->tenant)) {
            $event->fromFile(
                // TODO: Look at the required middleware for Lighthouse
                ['middleware' => [CanViewTenant::class]],
                base_path('vendor/nuwave/lighthouse/src/Support/Http/routes.php')
            );
        }
    }
}
