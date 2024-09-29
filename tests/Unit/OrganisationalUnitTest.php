<?php

declare(strict_types=1);

namespace Tests\Unit;

use Appleton\OrganisationalUnit\Models\OrganisationalUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\Models\SomeOtherType;
use Tests\Models\SomeType;
use Tests\TestCase;

/**
 * @covers \Appleton\OrganisationalUnit\Models\OrganisationalUnit
 */
class OrganisationalUnitTest extends TestCase
{
    use RefreshDatabase;

    public function testItCanCreateAnOrganisationalUnit(): void
    {
        $someType = SomeType::factory()->create();

        $unit = OrganisationalUnit::factory()->create([
            'entity_id' => $someType->id,
            'entity_type' => SomeType::class,
        ]);

        $this->assertNotNull($unit->id);
        $this->assertNull($unit->parent_id);
        $this->assertEquals($someType->id, $unit->entity_id);
        $this->assertEquals(SomeType::class, $unit->entity_type);
    }

    public function testItCanGetItsChildren(): void
    {
        $someType = SomeType::factory()->create();
        $parent = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $child = OrganisationalUnit::factory()->create([
            'parent_id' => $parent->id,
            'entity_id' => 2,
            'entity_type' => SomeType::class,
        ]);

        $this->assertCount(1, $parent->children);
        $this->assertEquals($child->id, $parent->children->first()->id);
    }

    public function testItCanGetItsTree(): void
    {
        $someType = SomeType::factory()->create();
        $root = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $child = OrganisationalUnit::factory()->create([
            'parent_id' => $root->id,
            'entity_id' => 2,
            'entity_type' => SomeType::class,
        ]);

        $tree = $root->getTree();

        $this->assertCount(1, $tree->first()->children);
        $this->assertEquals($child->id, $tree->first()->children->first()->id);
    }

    public function testItCanGetDescendants(): void
    {
        $someType1 = SomeType::factory()->create();
        $someType2 = SomeType::factory()->create();
        $someType3 = SomeType::factory()->create();
        $someType4 = SomeType::factory()->create();

        $root = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType1->id, 'entity_type' => SomeType::class]);
        $child1 = OrganisationalUnit::factory()->create(['parent_id' => $root->id, 'entity_id' => $someType2->id, 'entity_type' => SomeType::class]);
        OrganisationalUnit::factory()->create(['parent_id' => $root->id, 'entity_id' => $someType3->id, 'entity_type' => SomeType::class]);
        $grandchild = OrganisationalUnit::factory()->create(['parent_id' => $child1->id, 'entity_id' => $someType4->id, 'entity_type' => SomeType::class]);

        $descendants = $root->descendants();

        $this->assertCount(3, $descendants); // child1, child2, grandchild
        $this->assertTrue($descendants->contains('id', $grandchild->id));
    }

    public function testItCanGetParentChain(): void
    {
        $someType = SomeType::factory()->create();
        $grandparent = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $parent = OrganisationalUnit::factory()->create(['parent_id' => $grandparent->id, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $child = OrganisationalUnit::factory()->create(['parent_id' => $parent->id, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);

        $chain = $child->getParentChain();

        $this->assertCount(2, $chain); // parent and grandparent
        $this->assertTrue($chain->contains('id', $parent->id));
        $this->assertTrue($chain->contains('id', $grandparent->id));
    }

    public function testItCanCheckIfItIsRoot(): void
    {
        $someType = SomeType::factory()->create();
        $unit = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $this->assertTrue($unit->isRoot());

        $child = OrganisationalUnit::factory()->create(['parent_id' => $unit->id, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $this->assertFalse($child->isRoot());
    }

    public function testItCanCheckIfItIsLeaf(): void
    {
        $someType = SomeType::factory()->create();
        $unit = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $this->assertTrue($unit->isLeaf());

        $child = OrganisationalUnit::factory()->create(['parent_id' => $unit->id, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $this->assertFalse($unit->isLeaf());
    }

    public function testItCanGetSiblings(): void
    {
        $someType1 = SomeType::factory()->create();
        $someType2 = SomeType::factory()->create();
        $someType3 = SomeType::factory()->create();
        $someType4 = SomeType::factory()->create();

        $parent = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType1->id, 'entity_type' => SomeType::class]);
        $sibling1 = OrganisationalUnit::factory()->create(['parent_id' => $parent->id, 'entity_id' => $someType2->id, 'entity_type' => SomeType::class]);
        $sibling2 = OrganisationalUnit::factory()->create(['parent_id' => $parent->id, 'entity_id' => $someType3->id, 'entity_type' => SomeType::class]);
        $child = OrganisationalUnit::factory()->create(['parent_id' => $parent->id, 'entity_id' => $someType4->id, 'entity_type' => SomeType::class]);

        $siblings = $child->getSiblings();

        $this->assertCount(2, $siblings);
        $this->assertTrue($siblings->contains('id', $sibling1->id));
        $this->assertTrue($siblings->contains('id', $sibling2->id));
    }

    public function testItCanMoveToNewParent(): void
    {
        $someType1 = SomeType::factory()->create();
        $someType2 = SomeType::factory()->create();
        $someType3 = SomeType::factory()->create();

        $parent1 = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType1->id, 'entity_type' => SomeType::class]);
        $parent2 = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType2->id, 'entity_type' => SomeType::class]);
        $child = OrganisationalUnit::factory()->create(['parent_id' => $parent1->id, 'entity_id' => $someType3->id, 'entity_type' => SomeType::class]);

        $child->moveToParent($parent2->id);

        $this->assertEquals($parent2->id, $child->parent_id);
    }

    public function testItCanDetachFromParent(): void
    {
        $someType = SomeType::factory()->create();
        $parent = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $child = OrganisationalUnit::factory()->create(['parent_id' => $parent->id, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);

        $child->detachFromParent();

        $this->assertNull($child->parent_id);
    }

    public function testItCanRebuildTreeFromFlatList(): void
    {
        $someType = SomeType::factory()->create();
        $unit1 = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $unit2 = OrganisationalUnit::factory()->create(['parent_id' => $unit1->id, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $unit3 = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $unit4 = OrganisationalUnit::factory()->create(['parent_id' => $unit3->id, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);

        $flatList = collect([$unit1, $unit2, $unit3, $unit4]);

        OrganisationalUnit::rebuildTreeFromFlatList($flatList);

        $this->assertCount(1, $unit1->children);
        $this->assertCount(1, $unit3->children); // unit4
    }

    public function testItCanGetFieldsByConditions(): void
    {
        $someType1 = SomeType::factory()->create();
        $someType2 = SomeType::factory()->create();
        $someType3 = SomeType::factory()->create();

        $parent = OrganisationalUnit::factory()->create([
            'entity_type' => SomeType::class,
            'entity_id' => $someType1->id,
            'parent_id' => null,
        ]);

        $child1 = OrganisationalUnit::factory()->create([
            'entity_type' => SomeType::class,
            'entity_id' => $someType2->id,
            'parent_id' => $parent->id,
        ]);

        $child2 = OrganisationalUnit::factory()->create([
            'entity_type' => SomeType::class,
            'entity_id' => $someType3->id,
            'parent_id' => $parent->id,
        ]);

        $results = $parent->getFieldsByConditions(['entity_id'], ['entity_type' => SomeType::class]);

        $this->assertCount(3, $results);
        $this->assertTrue($results->contains('entity_id', $parent->entity_id));
        $this->assertTrue($results->contains('entity_id', $child1->entity_id));
        $this->assertTrue($results->contains('entity_id', $child2->entity_id));
    }

    public function testEntityTypeAndIdMustBeUniqueWithinSameParent(): void
    {
        $someType = SomeType::factory()->create();
        $parent = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);

        OrganisationalUnit::factory()->create([
            'parent_id' => $parent->id,
            'entity_id' => $someType->id,
            'entity_type' => SomeType::class,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        OrganisationalUnit::factory()->create([
            'parent_id' => $parent->id,
            'entity_id' => $someType->id,
            'entity_type' => SomeType::class,
        ]);
    }

    public function testMovingToSameParentDoesNotChangeParentId(): void
    {
        $someType = SomeType::factory()->create();
        $parent = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $child = OrganisationalUnit::factory()->create(['parent_id' => $parent->id, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);

        $originalParentId = $child->parent_id;
        $child->moveToParent($parent->id);

        $this->assertEquals($originalParentId, $child->parent_id);
    }

    public function testCannotCreateCircularReference(): void
    {
        $someType = SomeType::factory()->create();
        $parent = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $child = OrganisationalUnit::factory()->create(['parent_id' => $parent->id, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);

        $this->expectException(InvalidArgumentException::class);
        $child->moveToParent($child->id); // Trying to make itself a parent
    }

    public function testEntityTypeMustBeValid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        OrganisationalUnit::factory()->create(['entity_type' => 'InvalidType']);
    }

    public function testComplexTreeStructure(): void
    {
        $someType = SomeType::factory()->create();
        $grandparent = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $parent = OrganisationalUnit::factory()->create(['parent_id' => $grandparent->id, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $child = OrganisationalUnit::factory()->create(['parent_id' => $parent->id, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $grandchild = OrganisationalUnit::factory()->create(['parent_id' => $child->id, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);

        $this->assertCount(1, $grandparent->children);
        $this->assertCount(1, $parent->children);
        $this->assertCount(1, $child->children);
        $this->assertCount(0, $grandchild->children);
    }

    public function testDetachingLeafNodeDoesNotAffectParent(): void
    {
        $someType = SomeType::factory()->create();
        $parent = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $child = OrganisationalUnit::factory()->create(['parent_id' => $parent->id, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);

        $child->detachFromParent();

        $this->assertNull($child->parent_id);
        $this->assertCount(0, $parent->children); // Ensure child is removed
    }

    public function testAddingMultipleChildrenAndRemovingOne(): void
    {
        $someType1 = SomeType::factory()->create();
        $someType2 = SomeType::factory()->create();
        $someType3 = SomeType::factory()->create();

        $parent = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType1->id, 'entity_type' => SomeType::class]);

        $child1 = OrganisationalUnit::factory()->create(['parent_id' => $parent->id, 'entity_id' => $someType2->id, 'entity_type' => SomeType::class]);
        OrganisationalUnit::factory()->create(['parent_id' => $parent->id, 'entity_id' => $someType3->id, 'entity_type' => SomeType::class]);

        $this->assertCount(2, $parent->children);

        $child1->detachFromParent();

        $this->assertCount(1, $parent->fresh()->children);
    }

    public function testDeletingUnitDeletesChildren(): void
    {
        $someType1 = SomeType::factory()->create();
        $someType2 = SomeType::factory()->create();
        $someType3 = SomeType::factory()->create();

        $parent = OrganisationalUnit::factory()->create(['entity_id' => $someType1->id, 'entity_type' => SomeType::class]);
        $child1 = OrganisationalUnit::factory()->create(['parent_id' => $parent->id, 'entity_id' => $someType2->id, 'entity_type' => SomeType::class]);
        $child2 = OrganisationalUnit::factory()->create(['parent_id' => $parent->id, 'entity_id' => $someType3->id, 'entity_type' => SomeType::class]);

        $parent->delete();

        $this->assertNotNull(OrganisationalUnit::withTrashed()->find($child1->id)->deleted_at);
        $this->assertNotNull(OrganisationalUnit::withTrashed()->find($child2->id)->deleted_at);
    }

    public function testRestoringUnitRestoresChildren(): void
    {
        $someType1 = SomeType::factory()->create();
        $someType2 = SomeType::factory()->create();

        $parent = OrganisationalUnit::factory()->create(['entity_id' => $someType1->id, 'entity_type' => SomeType::class]);
        $child = OrganisationalUnit::factory()->create(['parent_id' => $parent->id, 'entity_id' => $someType2->id, 'entity_type' => SomeType::class]);

        $parent->delete();

        $parent->restore();

        $this->assertNull(OrganisationalUnit::find($child->id)->deleted_at);
    }

    public function testForceDeletingUnitForceDeletesChildren(): void
    {
        $someType1 = SomeType::factory()->create();
        $someType2 = SomeType::factory()->create();
        $someType3 = SomeType::factory()->create();

        $parent = OrganisationalUnit::factory()->create(['entity_id' => $someType1->id, 'entity_type' => SomeType::class]);
        $child1 = OrganisationalUnit::factory()->create(['parent_id' => $parent->id, 'entity_id' => $someType2->id, 'entity_type' => SomeType::class]);
        $child2 = OrganisationalUnit::factory()->create(['parent_id' => $parent->id, 'entity_id' => $someType3->id, 'entity_type' => SomeType::class]);

        $parent->forceDelete();

        $this->assertDatabaseMissing('organisational_units', ['id' => $child1->id]);
        $this->assertDatabaseMissing('organisational_units', ['id' => $child2->id]);
    }

    public function testScopeEntityType(): void
    {
        $someType = SomeType::factory()->create();
        $someOtherType = SomeOtherType::factory()->create();

        $unitTypeA = OrganisationalUnit::factory()->create(['entity_type' => SomeType::class, 'entity_id' => $someType->id]);
        $unitTypeB = OrganisationalUnit::factory()->create(['entity_type' => SomeOtherType::class, 'entity_id' => $someOtherType->id]);

        $filteredUnits = OrganisationalUnit::entityType(SomeType::class)->get();

        $this->assertTrue($filteredUnits->contains($unitTypeA));
        $this->assertFalse($filteredUnits->contains($unitTypeB));
    }

    public function testBuildTree(): void
    {
        $someType = SomeType::factory()->create();
        $someOtherType = SomeOtherType::factory()->create();

        $parentUnit = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $childUnit = OrganisationalUnit::factory()->create(['parent_id' => $parentUnit->id, 'entity_id' => $someOtherType->id, 'entity_type' => SomeOtherType::class]);

        $tree = OrganisationalUnit::buildTree();

        $this->assertNotNull($tree);
        $this->assertTrue($tree->contains($parentUnit));
        $this->assertTrue($tree->first()->children->contains($childUnit));
    }

    public function testGetAllRoots()
    {
        $someType = SomeType::factory()->create();
        $someOtherType = SomeOtherType::factory()->create();

        $rootUnit = OrganisationalUnit::factory()->create(['parent_id' => null, 'entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $childUnit = OrganisationalUnit::factory()->create(['parent_id' => $rootUnit->id, 'entity_id' => $someOtherType->id, 'entity_type' => SomeOtherType::class]);

        $roots = OrganisationalUnit::getAllRoots();

        $this->assertCount(1, $roots);
        $this->assertTrue($roots->contains($rootUnit));
        $this->assertFalse($roots->contains($childUnit));
    }

    public function testGetDescendantsCount()
    {
        // Arrange: Create a parent unit with multiple descendants.
        $someType = SomeType::factory()->create();
        $someType2 = SomeType::factory()->create();
        $someType3 = SomeType::factory()->create();
        $someType4 = SomeType::factory()->create();

        $parentUnit = OrganisationalUnit::factory()->create(['entity_id' => $someType->id, 'entity_type' => SomeType::class]);
        $childUnit1 = OrganisationalUnit::factory()->create(['parent_id' => $parentUnit->id, 'entity_id' => $someType2->id, 'entity_type' => SomeType::class]);
        OrganisationalUnit::factory()->create(['parent_id' => $parentUnit->id, 'entity_id' => $someType3->id, 'entity_type' => SomeType::class]);
        OrganisationalUnit::factory()->create(['parent_id' => $childUnit1->id, 'entity_id' => $someType4->id, 'entity_type' => SomeType::class]);

        $descendantsCount = $parentUnit->getDescendantsCount();

        $this->assertEquals(3, $descendantsCount);
    }
}
