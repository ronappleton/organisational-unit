<?php

declare(strict_types=1);

namespace Appleton\OrganisationalUnit\Models;

use Database\Factories\OrganisationalUnitFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * OrganisationalUnit
 *
 *
 * @property-read int|string $id
 * @property int|string|null $parent_id
 * @property string $entity_id
 * @property string $entity_type
 * @property OrganisationalUnit|null $parent
 * @property Collection<int, OrganisationalUnit> $children
 */
class OrganisationalUnit extends Model
{
    /** @use HasFactory<OrganisationalUnitFactory> */
    use HasFactory;
    use HasUuids;

    use SoftDeletes;

    /** @var array<int, string> */
    protected $fillable = [
        'parent_id',
        'entity_id',
        'entity_type',
    ];

    protected static function newFactory(): OrganisationalUnitFactory
    {
        return OrganisationalUnitFactory::new();
    }

    // Relationships

    /**
     * Get the associated entity.
     *
     * @return MorphTo<Model, OrganisationalUnit>
     */
    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the parent organisational unit.
     *
     * @return BelongsTo<OrganisationalUnit, OrganisationalUnit>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(OrganisationalUnit::class, 'parent_id');
    }

    /**
     * Get the children organisational units.
     *
     * @return HasMany<OrganisationalUnit>
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * Warning: Fetching children without eager loading may cause N+1 query issues.
     *
     * @example
     * $units = OrganisationalUnit::with('children')->get();
     * foreach ($units as $unit) {
     *     foreach ($unit->children as $child) {
     *         // Process child
     *     }
     * }
     */
    public function children(): HasMany
    {
        return $this->hasMany(OrganisationalUnit::class, 'parent_id');
    }

    // Scopes

    /**
     * Scope to filter units by entity type.
     *
     * @param  Builder<OrganisationalUnit>  $query
     * @param  class-string  $type
     * @return Builder<OrganisationalUnit>
     */
    public function scopeEntityType(Builder $query, string $type): Builder
    {
        return $query->where('entity_type', $type);
    }

    /**
     * Scope to filter root units (no parent).
     *
     * @param  Builder<OrganisationalUnit>  $query
     * @return Builder<OrganisationalUnit>
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    // Utility Functions

    /**
     * Get the tree of organisational units, optionally with associated entities.
     *
     * @param  bool  $withEntities  Whether to load associated entities
     * @return Collection<int, OrganisationalUnit>
     *
     * Warning: Fetching units without eager loading their entities may cause N+1 issues.
     *
     * @example
     * $units = (new OrganisationalUnit())->getTree(true); // Eager load entities
     */
    public function getTree(bool $withEntities = true): Collection
    {
        $query = OrganisationalUnit::query()->with('children');

        if ($withEntities) {
            $query->with('children.entity');
        }

        return $query->root()->get();
    }

    /**
     * Recursively build the organisational unit tree.
     *
     * @return Collection<int, OrganisationalUnit>
     *
     * Warning: Building the tree without eager loading may cause N+1 issues when accessing children.
     *
     * @example
     * $tree = OrganisationalUnit::buildTree();
     * foreach ($tree as $unit) {
     *     // Process unit and its children
     * }
     */
    public static function buildTree(int|string|null $parentId = null): Collection
    {
        $units = OrganisationalUnit::where('parent_id', $parentId)->with('entity')->get();

        foreach ($units as $unit) {
            $unit->children = self::buildTree($unit->id);
        }

        return $units;
    }

    /**
     * Move the organisational unit to a new parent.
     *
     * @return void
     *
     * Warning: Moving a unit without checking its current children may cause data integrity issues.
     *
     * @example
     * $unit = OrganisationalUnit::find($unitId);
     * $unit->moveToParent($newParentId);
     */
    public function moveToParent(int|string|null $newParentId): void
    {
        if ($this->id === $newParentId) {
            throw new \InvalidArgumentException('An organisational unit cannot be its own parent.');
        }

        $this->parent_id = $newParentId;
        $this->save();
    }

    /**
     * Detach the organisational unit from its current parent (make it a root node).
     *
     *
     * @example
     * $unit->detachFromParent();
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
     *
     * @example
     * OrganisationalUnit::rebuildTreeFromFlatList($flatUnits);
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
     * Get the descendants of the organisational unit.
     *
     * @return Collection<int, OrganisationalUnit>
     *
     * Warning: Fetching descendants without eager loading may cause N+1 issues.
     *
     * @example
     * $descendants = $unit->descendants(); // Eager load if necessary
     * foreach ($descendants as $descendant) {
     *     // Process descendant
     * }
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
     * Get the chain of parent organisational units (ancestors) up to the root.
     *
     * @return Collection<int, OrganisationalUnit>
     *
     * Warning: Fetching ancestors without eager loading may cause N+1 issues.
     *
     * @example
     * $ancestors = $unit->getParentChain();
     * foreach ($ancestors as $ancestor) {
     *     // Process ancestor
     * }
     */
    public function getParentChain(): Collection
    {
        $ancestors = collect();
        $current = $this;

        while ($current->parent) {
            $ancestors->push($current->parent);
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Get the direct siblings of the organisational unit.
     *
     * @return Collection<int, OrganisationalUnit>
     *
     * @example
     * $siblings = $unit->getSiblings();
     * foreach ($siblings as $sibling) {
     *     // Process sibling
     * }
     */
    public function getSiblings(): Collection
    {
        return OrganisationalUnit::where('parent_id', $this->parent_id)
            ->where('id', '!=', $this->id)
            ->get();
    }

    /**
     * Get all root organisational units (nodes with no parent).
     *
     * @return Collection<int, OrganisationalUnit>
     *
     * @example
     * $roots = OrganisationalUnit::getAllRoots();
     * foreach ($roots as $root) {
     *     // Process root unit
     * }
     */
    public static function getAllRoots(): Collection
    {
        return OrganisationalUnit::whereNull('parent_id')->get();
    }

    /**
     * Get the total number of descendants for the current organisational unit.
     *
     *
     * @example
     * $totalDescendants = $unit->getDescendantsCount();
     */
    public function getDescendantsCount(): int
    {
        return $this->descendants()->count();
    }

    /**
     * Get specified fields of organisational units that match the given conditions along the tree path.
     *
     * @param  array<int, string>  $fields
     * @param  array<string, string>  $conditions
     * @return Collection<int, array<string, mixed>>
     *
     * @example
     * $results = $unit->getFieldsByConditions(['entity_id'], ['entity_type' => 'SomeType']);
     */
    public function getFieldsByConditions(array $fields, array $conditions): Collection
    {
        $results = collect();

        $matches = true;
        foreach ($conditions as $field => $value) {
            if ($this->{$field} !== $value) {
                $matches = false;
                break;
            }
        }

        if ($matches) {
            $result = [];
            foreach ($fields as $field) {
                $result[$field] = $this->{$field};
            }
            $results->push($result);
        }

        foreach ($this->children as $child) {
            $results = $results->merge($child->getFieldsByConditions($fields, $conditions));
        }

        return $results;
    }

    /**
     * Check if the organisational unit is a root node.
     *
     *
     * @example
     * if ($unit->isRoot()) {
     *     // Handle root unit
     * }
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if the organisational unit is a leaf node (no children).
     *
     *
     * @example
     * if ($unit->isLeaf()) {
     *     // Handle leaf unit
     * }
     */
    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (OrganisationalUnit $unit): void {
            $unit->children()->delete();
        });

        static::restoring(function (OrganisationalUnit $unit): void {
            $unit->children()->withTrashed()->restore();
        });

        static::forceDeleting(function (OrganisationalUnit $unit): void {
            $unit->children()->forceDelete();
        });

        static::creating(function (OrganisationalUnit $unit): void {
            if (! class_exists($unit->entity_type)) {
                throw new \InvalidArgumentException('Invalid entity type.');
            }
        });

        static::updating(function (OrganisationalUnit $unit): void {
            if (! class_exists($unit->entity_type)) {
                throw new \InvalidArgumentException('Invalid entity type.');
            }
        });
    }
}
