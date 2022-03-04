<?php
namespace App\Tenancy\Listeners\Packages\SpatiePermission;

use Illuminate\Support\Env;

trait OnSystem
{
    public function getConnectionName()
    {
        return Env::get('DB_CONNECTION');
    }
}
