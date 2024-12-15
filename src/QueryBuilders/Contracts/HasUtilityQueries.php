<?php

declare(strict_types=1);

namespace Appleton\OrganisationalUnit\QueryBuilders\Contracts;

trait HasUtilityQueries
{
    public function atLevel($level)
    {
        return $this->fromSub(function ($query) use ($level) {
            $query->select('id', 'parent_id', \DB::raw('0 as level'))
                ->from('organisational_units')
                ->whereNull('parent_id')
                ->unionAll(
                    $query->newQuery()
                        ->select('ou.id', 'ou.parent_id', \DB::raw('ancestors.level + 1'))
                        ->from('organisational_units as ou')
                        ->join('ancestors', 'ou.parent_id', '=', 'ancestors.id')
                );
        }, 'hierarchy')->where('level', $level);
    }

    public function rootUnits()
    {
        return $this->whereNull('parent_id');
    }

}