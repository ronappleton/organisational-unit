<?php

declare(strict_types=1);

namespace Appleton\OrganisationalUnit\Models;

use Appleton\OrganisationalUnit\QueryBuilders\OrganisationalUnitQueryBuilder;
use Appleton\OrganisationalUnit\Traits\HasMetadata;
use Database\Factories\OrganisationalUnitFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int|null $parent_id
 * @property string|null $entity_type
 * @property int|null $entity_id
 * @property string $name
 * @property string|null $code
 * @property string|null $type
 * @property int|null $tenant_id
 * @property-read OrganisationalUnit|null $parent
 * @property-read Collection<int, OrganisationalUnit> $children
 */
class OrganisationalUnit extends Model
{
    /** @use HasFactory<OrganisationalUnitFactory> */
    use HasFactory;

    use HasMetadata;
    use SoftDeletes;

    /** @var array<int, string> */
    protected $fillable = [
        'parent_id',
        'entity_type',
        'entity_id',
        'name',
        'code',
        'type',
        'tenant_id',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'id' => 'int',
        'parent_id' => 'int',
        'entity_id' => 'int',
        'tenant_id' => 'int',
    ];

    protected static function newFactory(): OrganisationalUnitFactory
    {
        return OrganisationalUnitFactory::new();
    }

    /**
     * Use the custom query builder.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    public function newEloquentBuilder($query): OrganisationalUnitQueryBuilder
    {
        return new OrganisationalUnitQueryBuilder($query);
    }

    // Relationships

    /**
     * The associated entity (optional).
     *
     * @return MorphTo<Model, OrganisationalUnit>
     */
    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Parent organisational unit.
     *
     * @return BelongsTo<OrganisationalUnit, OrganisationalUnit>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Child organisational units.
     *
     * @return HasMany<OrganisationalUnit>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    // Scopes

    /**
     * Scope to filter units by entity type.
     *
     * @param  Builder<OrganisationalUnit>  $query
     */
    public function scopeEntityType(Builder $query, string $type): Builder
    {
        return $query->where('entity_type', $type);
    }

    /**
     * Scope to filter root units (no parent).
     *
     * @param  Builder<OrganisationalUnit>  $query
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to filter by tenant.
     *
     * @param  Builder<OrganisationalUnit>  $query
     */
    public function scopeTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    // Utility functions

    /**
     * Get the tree of organisational units, optionally with associated entities.
     *
     * @return Collection<int, OrganisationalUnit>
     */
    public function getTree(bool $withEntities = true): Collection
    {
        $query = self::query()->with('children');

        if ($withEntities) {
            $query->with('children.entity');
        }

        return $query->root()->get();
    }

    /**
     * Recursively build the organisational unit tree from a parent.
     *
     * @return Collection<int, OrganisationalUnit>
     */
    public static function buildTree(int|string|null $parentId = null): Collection
    {
        $units = self::where('parent_id', $parentId)
            ->with('entity')
            ->get();

        foreach ($units as $unit) {
            // Attach a dynamic children property (not persisted)
            $unit->setRelation('children', self::buildTree($unit->id));
        }

        return $units;
    }

    /**
     * Move the organisational unit to a new parent.
     */
    public function moveToParent(int|string|null $newParentId): void
    {
        if ($this->id === $newParentId) {
            throw new \InvalidArgumentException('A unit cannot be its own parent.');
        }

        if ($newParentId !== null && $this->descendants()->contains('id', $newParentId)) {
            throw new \InvalidArgumentException('Cannot move a unit under one of its descendants.');
        }

        $this->parent_id = $newParentId;
        $this->save();
    }

    /**
     * Detach the organisational unit from its parent (make it a root node).
     */
    public function detachFromParent(): void
    {
        $this->parent_id = null;
        $this->save();
    }

    /**
     * Rebuild the tree structure from a flat list of units.
     *
     * @param  Collection<int, OrganisationalUnit>  $flatUnits
     */
    public static function rebuildTreeFromFlatList(Collection $flatUnits): void
    {
        $flatUnits->each(function (OrganisationalUnit $unit) use ($flatUnits): void {
            $parent = $flatUnits->firstWhere('id', $unit->parent_id);

            if ($parent) {
                $parent->children()->save($unit);
            }
        });
    }

    /**
     * Get all descendants of this unit (recursive).
     *
     * @return Collection<int, OrganisationalUnit>
     */
    public function descendants(): Collection
    {
        $descendants = collect();
        $children = $this->children()->with('entity')->get();

        foreach ($children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
        }

        return $descendants;
    }

    /**
     * Get the chain of parent units (ancestors) up to the root.
     *
     * @return Collection<int, OrganisationalUnit>
     */
    public function getParentChain(): Collection
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Get siblings of this unit (same parent, excluding self).
     *
     * @return Collection<int, OrganisationalUnit>
     */
    public function getSiblings(): Collection
    {
        return self::where('parent_id', $this->parent_id)
            ->where('id', '!=', $this->id)
            ->get();
    }

    /**
     * Get all root units.
     *
     * @return Collection<int, OrganisationalUnit>
     */
    public static function getAllRoots(): Collection
    {
        return self::whereNull('parent_id')->get();
    }

    /**
     * Count all descendants of this unit.
     */
    public function getDescendantsCount(): int
    {
        return $this->descendants()->count();
    }

    /**
     * Recursively get selected fields for units matching conditions.
     *
     * @param  array<int, string>  $fields
     * @param  array<string, mixed>  $conditions
     * @return Collection<int, array<string, mixed>>
     */
    public function getFieldsByConditions(array $fields, array $conditions): Collection
    {
        $results = collect();

        $matches = collect($conditions)->every(
            fn ($value, string $field) => $this->{$field} === $value
        );

        if ($matches) {
            $result = [];
            foreach ($fields as $field) {
                $result[$field] = $this->{$field};
            }
            $results->push($result);
        }

        // NOTE: may cause N+1 if children relation not eager-loaded
        foreach ($this->children as $child) {
            $results = $results->merge(
                $child->getFieldsByConditions($fields, $conditions)
            );
        }

        return $results;
    }

    /**
     * Is this unit a root (no parent)?
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Is this unit a leaf (no children)?
     */
    public function isLeaf(): bool
    {
        return ! $this->children()->exists();
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (OrganisationalUnit $unit): void {
            if (! $unit->isForceDeleting()) {
                $unit->children()->delete();
            }
        });

        static::restoring(function (OrganisationalUnit $unit): void {
            $unit->children()->withTrashed()->restore();
        });

        static::forceDeleted(function (OrganisationalUnit $unit): void {
            $unit->children()->forceDelete();
        });
    }
}
