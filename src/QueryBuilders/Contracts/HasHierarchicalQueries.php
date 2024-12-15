<?php

declare(strict_types=1);

namespace Appleton\OrganisationalUnit\QueryBuilders\Contracts;

trait HasHierarchicalQueries
{
    public function descendants($id)
    {
        return $this->fromSub(function ($query) use ($id) {
            $query->select('id', 'parent_id')
                ->from('organisational_units')
                ->where('id', $id)
                ->unionAll(
                    $query->newQuery()
                        ->select('ou.id', 'ou.parent_id')
                        ->from('organisational_units as ou')
                        ->join('descendants', 'ou.parent_id', '=', 'descendants.id')
                );
        }, 'descendants');
    }

    public function ancestors($id)
    {
        return $this->fromSub(function ($query) use ($id) {
            $query->select('id', 'parent_id')
                ->from('organisational_units')
                ->where('id', $id)
                ->unionAll(
                    $query->newQuery()
                        ->select('ou.id', 'ou.parent_id')
                        ->from('organisational_units as ou')
                        ->join('ancestors', 'ou.id', '=', 'ancestors.parent_id')
                );
        }, 'ancestors');
    }

    public function hierarchyLevels()
    {
        return $this->fromSub(function ($query) {
            $query->select('id', 'parent_id', \DB::raw('0 as level'))
                ->from('organisational_units')
                ->whereNull('parent_id')
                ->unionAll(
                    $query->newQuery()
                        ->select('ou.id', 'ou.parent_id', \DB::raw('ancestors.level + 1'))
                        ->from('organisational_units as ou')
                        ->join('ancestors', 'ou.parent_id', '=', 'ancestors.id')
                );
        }, 'hierarchy');
    }

    public function hierarchyPaths($separator = '/')
    {
        return $this->fromSub(function ($query) use ($separator) {
            $query->select('id', 'parent_id', \DB::raw("CAST(name AS CHAR) AS path"))
                ->from('organisational_units')
                ->whereNull('parent_id')
                ->unionAll(
                    $query->newQuery()
                        ->select('ou.id', 'ou.parent_id', \DB::raw("CONCAT(ancestors.path, '$separator', ou.name)"))
                        ->from('organisational_units as ou')
                        ->join('ancestors', 'ou.parent_id', '=', 'ancestors.id')
                );
        }, 'paths');
    }

}