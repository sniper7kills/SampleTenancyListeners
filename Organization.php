<?php
namespace App\Domains\System\Models;

use App\Traits\HasUUID;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Billable;
use Tenancy\Database\Drivers\Mysql\Concerns\ManagesSystemConnection;
use Tenancy\Identification\Concerns\AllowsTenantIdentification;
use Tenancy\Identification\Contracts\Tenant;
use Tenancy\Identification\Drivers\Console\Contracts\IdentifiesByConsole;
use Tenancy\Identification\Drivers\Http\Contracts\IdentifiesByHttp;
use Tenancy\Identification\Drivers\Queue\Events\Processing;
use Tenancy\Identification\Drivers\Queue\Contracts\IdentifiesByQueue;
use Symfony\Component\Console\Input\InputInterface;

class Organization extends \App\Domains\System\Models\AbstractModels\AbstractOrganization implements
    Tenant,
    ManagesSystemConnection,
    IdentifiesByHttp,
    IdentifiesByConsole,
    IdentifiesByQueue
{
    use AllowsTenantIdentification, Billable, HasFactory, HasUUID;

    protected $dispatchesEvents = [
        'created' => \Tenancy\Tenant\Events\Created::class,
        'updated' => \Tenancy\Tenant\Events\Updated::class,
        'deleted' => \Tenancy\Tenant\Events\Deleted::class,
    ];

    public function getManagingSystemConnection(): ?string
    {
        // This is the connection we want to use when managing Tenant databases
        return 'mysql-admin';
    }

    /**
     * HTTP Identification method for this model
     */
    public function tenantIdentificationByHttp(Request $request): ?Tenant
    {
        $fqdn = explode('.', $request->getHost(), 2);
        try {
            $domain = Domain::where('domain', $fqdn[1])->firstOrFail();
        } catch (Exception $e) {
            // TODO Proper Error Handling
            return null;
        }

        return $this->query()
            ->where('subdomain', $fqdn[0])
            ->where('domain_id', $domain->id)
            ->first();
    }

    /**
     * Console Identification for this model
     */
    public function tenantIdentificationByConsole(InputInterface $input): ?Tenant
    {
        if ($input->hasParameterOption('--tenant')) {
            $fqdn = explode('.', $input->getParameterOption('--tenant'), 2);
            try {
                $domain = Domain::where('domain', $fqdn[1])->firstOrFail();
            } catch (Exception $e) {
                //TODO Proper Error Handling
                return null;
            }

            return $this->query()
                ->where('subdomain', $fqdn[0])
                ->where('domain_id', $domain->id)
                ->first();
        }

        return null;
    }

    /**
     * Queue Identification for this model
     */
    public function tenantIdentificationByQueue(Processing $event): ?Tenant
    {
        if ($event->tenant) {
            return $event->tenant;
        }

        if ($event->tenant_key && $event->tenant_identifier === $this->getTenantIdentifier()) {
            return $this->newQuery()
                ->where($this->getTenantKeyName(), $event->tenant_key)
                ->first();
        }

        return null;
    }


    public function scopeUserSees(Builder $query, User $user = null): Builder
    {
        if ($user == null) {
            if (Auth::guest()) {
                return $query->where('id', '-1');
            }
            /** @var User */
            $user = Auth::user();
        }

        // Join the user and memberships tables
        $query->join('memberships', function (JoinClause $join) use ($user) {
            $join->on('organizations.id', '=', 'memberships.organization_id');
        });

        $query->where(function (Builder $query) use ($user) : Builder {
            $query->where('memberships.member_id', $user->getKey())
                ->where('memberships.member_type', $user->getMorphClass());
            return $query;
        });
        if ($user->can('organization.view')) {
            foreach ($user->organizations as $organization) {
                $query->orWhere(function (Builder $query) use ($organization) : Builder {
                    $query->where('memberships.member_id', $organization->getKey())
                        ->where('memberships.member_type', $organization->getMorphClass());
                    return $query;
                });
            }
        }
        $query->select('organizations.*');
        return $query;
    }

    /**
     * Member Type Scope
     *
     * @param Builder $query
     * @param mixed $role
     * @param User $user
     * @return Builder
     */
    public function scopeMemberType(Builder $query, $role = 'member', User $user = null): Builder
    {
        if ($user == null) {
            /** @var User */
            $user = Auth::user();
        }

        // Join the user and memberships tables
        $query->join('memberships', function (JoinClause $join) use ($user) {
            $join->on('organizations.id', '=', 'memberships.organization_id')
                ->where('memberships.member_id', $user->getKey())
                ->where('memberships.member_type', $user->getMorphClass());
        });

        if (is_array($role)) {
            foreach ($role as $r) {
                $query->orWhere('memberships.role', $r);
            }
        } else {
            $query->where('memberships.role', $role);
        }
        $query->select('organizations.*');
        return $query;
    }

    public function scopeOwnedBy(Builder $query, User $user = null): Builder
    {
        return $this->scopeMemberType($query, user: $user, role: 'owner');
    }

    public function scopeMemberOf(Builder $query, User $user = null): Builder
    {
        return $this->scopeMemberType($query, user: $user, role: ['owner', 'member']);
    }

    public function scopeGuestOf(Builder $query, User $user = null): Builder
    {
        return $this->scopeMemberType($query, user: $user, role: ['vendor', 'auditor', 'other']);
    }

    public function getOwnersAttribute()
    {
        return Membership::where('organization_id', $this->getKey())
            ->where('role', 'owner')
            ->get()
            ->pluck('member');
    }

    public function isOwner($owner)
    {
        // Direct Users & Orgs
        $query = Membership::where('member_id', $owner->getKey())
            ->where('member_type', $owner->getMorphClass())
            ->where('role', 'owner')
            ->where('organization_id', $this->id);

        // Inherited Users
        if ($owner->getMorphClass() == User::class) {
            foreach ($this->parentOrganizations as $parentOrganization) {
                $query->orWhere('organization_id', $parentOrganization->id);
            }
        }

        if (1 <= $query->count()) {
            return true;
        }

        return false;
    }

    public function isMember($member)
    {
        $query = Membership::query();
        $query->where('organization_id', $this->getKey())
            ->where('member_type', $member->getMorphClass())
            ->where('member_id', $member->getKey());

        return 0 < $query->count();
    }

    public function isInheritedMember($member)
    {
        $query = Membership::query();
        $query->where('member_type', $member->getMorphClass())
            ->where('member_id', $member->getKey());

        $parentOrganizations = $this->parentOrganizations;
        $query->where(function (Builder $builder) use ($parentOrganizations) {
            foreach ($parentOrganizations as $organization) {
                $builder->orWhere('organization_id', $organization->getKey());
            }
            return $builder;
        });

        return 0 < $query->count();
    }
}
