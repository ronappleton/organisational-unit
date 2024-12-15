<?php

declare(strict_types=1);

namespace Appleton\OrganisationalUnit\QueryBuilders;

use Appleton\OrganisationalUnit\QueryBuilders\Contracts\HasAggregationQueries;
use Appleton\OrganisationalUnit\QueryBuilders\Contracts\HasHierarchicalQueries;
use Appleton\OrganisationalUnit\QueryBuilders\Contracts\HasUtilityQueries;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class OrganisationalUnitQueryBuilder extends Builder
{
    use HasHierarchicalQueries;
    use HasAggregationQueries;
    use HasUtilityQueries;

        public function __construct(QueryBuilder $query)
        {
            parent::__construct($query);
        }
}