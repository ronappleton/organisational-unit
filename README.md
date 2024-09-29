# Organisational Unit

![Laravel](https://img.shields.io/badge/laravel-%23FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=white)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/6e09ac1648ef4a05afbabb6d798373dc)](https://app.codacy.com/gh/ronappleton/organisational-unit/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/6e09ac1648ef4a05afbabb6d798373dc)](https://app.codacy.com/gh/ronappleton/organisational-unit/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_coverage)

The `OrganisationalUnit` package provides a way to manage hierarchical structures within an organisation. This Eloquent model allows for the creation, retrieval, and manipulation of organisational units and their relationships, including parent-child relationships.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Model Properties](#model-properties)
- [Relationships](#relationships)
- [Scopes](#scopes)
- [Utility Functions](#utility-functions)
- [Contributing](#contributing)
- [License](#license)

## Installation

You can install the package via Composer:

```bash
composer require appleton/organisational-unit
```

## Usage

To use the `OrganisationalUnit` model, simply create a new instance and set the properties as needed:

```php
use Appleton\OrganisationalUnit\Models\OrganisationalUnit;

 $unit = new OrganisationalUnit();
 $unit->entity_id = 1;
 $unit->entity_type = SomeType::class;
 $unit->save();
```

You can also create parent-child relationships:

```php
 $unit = OrganisationalUnit::create(['entity_id' => 'parent-entity', 'entity_type' => 'ParentType']);
 $unit = OrganisationalUnit::create(['entity_id' => 'child-entity', 'entity_type' => 'ChildType', 'parent_id' => ]);
```

## Model Properties

- `id`: Unique identifier for the organisational unit (can be either an integer or a UUID).
- `parent_id`: The ID of the parent organisational unit.
- `entity_id`: Identifier of the associated entity.
- `entity_type`: Type of the associated entity.

## Relationships

```php
public function entity(): MorphTo
```

Returns the associated entity for the organisational unit.

```php
public function parent(): BelongsTo
```

Returns the parent organisational unit.

```php
public function children(): HasMany
```

Returns the children organisational units.

## Scopes

```php
 public function scopeEntityType(Builder $query, string $type): Builder
```

Filter units by entity type.

```php
 public function scopeRoot(Builder $query): Builder
```

Filter root units (no parent).

## Utility Functions

```php
public function getTree(bool $withEntities = true): Collection
```

Get the tree of organisational units, optionally with associated entities.

```php
public function buildTree(int|string|null $parentId = null): Collection
```

Recursively build the organisational unit tree.

```php
public function moveToParent(int|string|null $newParentId): void
```

Move the organisational unit to a new parent.

```php
public function detachFromParent(): void
```

Detach the organisational unit from its current parent.

```php
public function rebuildTreeFromFlatList(Collection $flatUnits): void
```

Rebuild the tree structure from a flat list of units.

```php
public function descendants(): Collection
```

Get the descendants of the organisational unit.

```php
public function getParentChain(): Collection
```

Get the chain of parent organisational units (ancestors) up to the root.

```php
public function getSiblings(): Collection
```

Get the direct siblings of the organisational unit.

```php
public function getAllRoots(): Collection
```

Get all root organisational units (nodes with no parent).

```php
public function getDescendantsCount(): int
```

Get the total number of descendants for the current organisational unit.

```php
public function getFieldsByConditions(array $fields, array $conditions): Collection
```

Get specified fields of organisational units that match the given conditions along the tree path.

```php
public function isRoot(): bool
```

Check if the organisational unit is a root node.

```php
public function isLeaf(): bool
```

Check if the organisational unit is a leaf node (no children).

## Contributing

If you want to contribute to this package, please fork the repository and make a pull request.

## License

This package is open-sourced software licensed under the [MIT License](https://opensource.org/licenses/MIT).
