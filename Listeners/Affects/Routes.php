<?php

namespace App\Tenancy\Listeners\Affects;

use App\Domains\System\Middleware\CanViewTenant;
use Tenancy\Affects\Routes\Events\ConfigureRoutes;

class Routes
{
    public function handle(ConfigureRoutes $event)
    {
        if ($event->event->tenant != null) {
            $event->flush()
                ->fromFile(
                    ['middleware' => ['web', CanViewTenant::class]],
                    base_path('routes/tenants/web.php')
                );
        }
    }
}
