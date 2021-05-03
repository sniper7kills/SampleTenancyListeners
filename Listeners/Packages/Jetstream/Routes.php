<?php
namespace App\Tenancy\Listeners\Packages\Jetstream;

use Tenancy\Affects\Routes\Events\ConfigureRoutes;

class Routes
{
    public function handle(ConfigureRoutes $event)
    {
        if ($event->event->tenant) {
            $event->fromFile(
                [],
                base_path('vendor/laravel/fortify/routes/routes.php') // Fortify Routes
            )->fromFile(
                [],
                base_path('vendor/laravel/jetstream/routes/inertia.php') // Jetstream Routes
            );
        }
    }
}
