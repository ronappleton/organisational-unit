![Laravel](https://img.shields.io/badge/laravel-%23FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=white)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/6e09ac1648ef4a05afbabb6d798373dc)](https://app.codacy.com/gh/ronappleton/organisational-unit/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/6e09ac1648ef4a05afbabb6d798373dc)](https://app.codacy.com/gh/ronappleton/organisational-unit/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_coverage)

# 📦 Organisational Unit Backbone
A lightweight, high-performance hierarchical structure & metadata system for Laravel applications.

This package provides a **generic organisational unit backbone**, designed as a universal building block for representing **anything hierarchical**:

- Schools → Year Groups → Classes
- Warehouses → Zones → Aisles → Racks → Bins
- Corporate Org Charts
- Facilities → Buildings → Floors → Rooms
- Taxonomies
- Asset Locations
- Multi-tenant logical structures

It provides:

- A **lean organisational_units table** (bigint, indexed, fast)
- Optional **morph link to any model**
- Fully managed **parent/child tree structure**
- Typed, polymorphic **metadata**
- Clean, extensible **query builder helpers**
- PHP-side tree utilities (descendants, ancestors, etc.)
- Automatic cascading of soft deletes / restores

## 🚀 Why start your project with this backbone?

Most systems *accidentally* recreate these same problems:

- “We need a structure of buildings → rooms → sensors.”
- “We need departments → teams → roles.”
- “We need product categories.”
- “We need a place hierarchy.”
- “We need dynamic classifications.”
- “We need custom per-node metadata.”

Every time, developers start from scratch.

By beginning with this backbone you get:

- **Universal applicability**
- **Strong consistency**
- **Flexibility without performance loss**
- **Extensibility without schema rewrites**
- **Scalable metadata system**
- **Tree tools baked in**

This becomes a foundation your entire ecosystem can depend on.

## 📥 Installation

```bash
composer require appleton/organisational-unit
php artisan migrate
```

## 🗂️ Database Structure

The `organisational_units` table contains:

- id (bigint PK)
- parent_id (nullable FK)
- entity_type / entity_id (nullable morph)
- name
- code
- type
- tenant_id
- soft deletes + timestamps

Indexes exist on:

- parent_id
- entity_type, entity_id
- type
- code
- tenant_id
- tenant_id, type

## 🌳 Model Usage

```php
$unit = OrganisationalUnit::create([
    'name' => 'Warehouse A',
    'type' => 'warehouse',
]);
```

## Parent / Children

```php
$unit->parent;
$unit->children;
```

## Linking Entities

```php
$unit->entity()->associate($model)->save();
```

## Moving Units

```php
$unit->moveToParent($newParentId);
```

## Building Trees

```php
$tree = OrganisationalUnit::buildTree();
```

## Descendants / Ancestors

```php
$unit->descendants();
$unit->getParentChain();
```

## 🧠 Query Builder

```php
OrganisationalUnit::query()
    ->root()
    ->tenant(5)
    ->ofType('bin')
    ->entityType(User::class)
    ->get();
```

## 🏷️ Metadata

Set metadata:

```php
$unit->setMeta('capacity', 300);
$unit->setMeta('is_active', true);
$unit->setMeta('config', ['threshold' => 10]);
```

Get metadata:

```php
$unit->getMeta('capacity');
```

Remove:

```php
$unit->forgetMeta('capacity');
```

## 🏫 Example: School

```php
$school = OU::create(['name' => 'Greenfields Primary', 'type' => 'school']);
$ks1 = OU::create(['name' => 'Key Stage 1', 'parent_id' => $school->id]);
$y1 = OU::create(['name' => 'Year 1', 'parent_id' => $ks1->id]);

$classA = OU::create([
    'name' => '1A',
    'parent_id' => $y1->id,
]);
$classA->setMeta('max_size', 30);
```

## 📦 Example: WMS

```php
$wh = OU::create(['name' => 'Warehouse 1', 'type' => 'warehouse']);
$zone = OU::create(['name' => 'Zone A', 'parent_id' => $wh->id]);
$aisle = OU::create(['name' => 'Aisle 12', 'parent_id' => $zone->id]);
$bin = OU::create(['name' => 'Bin 7', 'parent_id' => $aisle->id]);

$bin->setMeta('max_weight', 250);
```

## 🏛️ Example: Facilities

```php
$campus = OU::create(['name' => 'North Campus']);
$building = OU::create(['name' => 'Block A', 'parent_id' => $campus->id]);
$room101 = OU::create(['name' => 'Room 101', 'parent_id' => $building->id']);
$room101->setMeta('capacity', 40);
```

## ⚙️ Extensibility

- Attach any model via `entity_type/entity_id`
- Add metadata dynamically
- Use `type` to build domain-specific trees
- Use `tenant_id` for SaaS isolation

## 🔧 Performance Notes

- Bigint PKs allow fast joins and small indexes
- Narrow row design supports millions of units
- Typed metadata avoids unbounded JSON blobs
- Recursion is PHP-based for reliability

## 🧱 Philosophy Summary

- A **universal tree system**
- A **universal metadata system**
- A **universal linking system**
- Extendable into any domain
- Highly scalable and predictable

This solves a problem once so your whole ecosystem doesn't need to reinvent structure management repeatedly.
