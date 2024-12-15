<?php

declare(strict_types=1);

namespace Appleton\OrganisationalUnit\QueryBuilders\Contracts;

trait HasAggregationQueries
{
    public function countDescendants()
    {
        return $this->fromSub(function ($query) {
            $query->select('id', 'parent_id', \DB::raw('COUNT(*) as descendant_count'))
                ->from('organisational_units')
                ->groupBy('id', 'parent_id')
                ->unionAll(
                    $query->newQuery()
                        ->select('ou.id', 'ou.parent_id', \DB::raw('COUNT(descendants.id)'))
                        ->from('organisational_units as ou')
                        ->join('descendants', 'ou.id', '=', 'descendants.parent_id')
                        ->groupBy('ou.id', 'ou.parent_id')
                );
        }, 'descendant_counts');
    }

    public function aggregateMetadata($column)
    {
        return $this->fromSub(function ($query) use ($column) {
            $query->select('id', 'parent_id', \DB::raw("SUM(metadata.$column) as total_$column"))
                ->from('organisational_units')
                ->join('metadata', 'metadata.unit_id', '=', 'organisational_units.id')
                ->groupBy('id', 'parent_id');
        }, 'metadata_totals');
    }

}