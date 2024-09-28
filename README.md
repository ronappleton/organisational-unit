# Organisational Unit

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

### `entity()`

Returns the associated entity for the organisational unit.

### `parent()`

Returns the parent organisational unit.

### `children()`

Returns the children organisational units.

## Scopes

### `scopeEntityType(, )`

Filter units by entity type.

### `scopeRoot()`

Filter root units (no parent).

## Utility Functions

### `getTree( = true)`

Get the tree of organisational units, optionally with associated entities.

### `buildTree( = null)`

Recursively build the organisational unit tree.

### `moveToParent()`

Move the organisational unit to a new parent.

### `detachFromParent()`

Detach the organisational unit from its current parent.

### `rebuildTreeFromFlatList()`

Rebuild the tree structure from a flat list of units.

### `descendants()`

Get the descendants of the organisational unit.

### `getParentChain()`

Get the chain of parent organisational units (ancestors) up to the root.

### `getSiblings()`

Get the direct siblings of the organisational unit.

### `getAllRoots()`

Get all root organisational units (nodes with no parent).

### `getDescendantsCount()`

Get the total number of descendants for the current organisational unit.

### `getFieldsByConditions(array , array )`

Get specified fields of organisational units that match the given conditions along the tree path.

### `isRoot()`

Check if the organisational unit is a root node.

### `isLeaf()`

Check if the organisational unit is a leaf node (no children).

## Contributing

If you want to contribute to this package, please fork the repository and make a pull request.

## License

This package is open-sourced software licensed under the [MIT License](https://opensource.org/licenses/MIT).
