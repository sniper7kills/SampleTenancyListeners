<?php
namespace App\Support\Packages\SpatiePermission;

use Tenancy\Affects\Connections\Support\Traits\OnTenant;

class Role extends \Spatie\Permission\Models\Role 
{
    use OnTenant;
}
