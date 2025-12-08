<?php

declare(strict_types=1);

namespace Appleton\OrganisationalUnit\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class OrganisationalUnitQueryBuilder extends Builder
{
    /**
     * @param  QueryBuilder  $query
     */
    public function __construct($query)
    {
        parent::__construct($query);
    }

    /**
     * Only root units (no parent).
     */
    public function root(): self
    {
        return $this->whereNull('parent_id');
    }

    /**
     * Filter by tenant.
     */
    public function tenant(?int $tenantId): self
    {
        if ($tenantId === null) {
            return $this->whereNull('tenant_id');
        }

        return $this->where('tenant_id', $tenantId);
    }

    /**
     * Filter by unit type (e.g. 'warehouse', 'class', 'bin').
     */
    public function ofType(string $type): self
    {
        return $this->where('type', $type);
    }

    /**
     * Filter by entity morph type (raw string – class or alias).
     */
    public function entityType(string $entityType): self
    {
        return $this->where('entity_type', $entityType);
    }
}
