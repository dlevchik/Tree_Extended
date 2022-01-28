<?php

/* @noinspection ReturnTypeCanBeDeclaredInspection */
/* @noinspection PhpParamsInspection */

namespace BlueM\Tree\Serializer;

use BlueM\Tree;
use BlueM\TreeTest;
use BlueM\TreeWritable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \BlueM\Tree\Serializer\DefaultArraySerializer
 */
class DefaultArraySerializerTest extends TestCase
{
    private const SERIALIZED_DATA = [
        [
            'id_node' => 'building',
            'id_parent' => '',
            'id' => 'building',
            'parent' => ''
        ],
        [
            'id_node' => 'library',
            'id_parent' => 'building',
            'id' => 'library',
            'parent' => 'building'
        ],
        [
            'id_node' => 'school',
            'id_parent' => 'building',
            'id' => 'school',
            'parent' => 'building'
        ],
        [
            'id_node' => 'primary-school',
            'id_parent' => 'school',
            'id' => 'primary-school',
            'parent' => 'school',
        ],
        [
            'id_node' => 'vehicle',
            'id_parent' => '',
            'id' => 'vehicle',
            'parent' => ''
        ],
        [
            'id_node' => 'bicycle',
            'id_parent' => 'vehicle',
            'id' => 'bicycle',
            'parent' => 'vehicle'
        ],
        [
            'id_node' => 'car',
            'id_parent' => 'vehicle',
            'id' => 'car',
            'parent' => 'vehicle'
        ]
    ];

    /**
     * @test
     */
    public function orditaryTreeSelializationTest()
    {
        $data = self::dataWithStringKeys(true, 'id_node', 'id_parent');

        $tree = new Tree($data, ['rootId' => '', 'id' => 'id_node', 'parent' => 'id_parent']);
        $serializedData = DefaultArraySerializer::toArray($tree);

        static::assertEquals(self::SERIALIZED_DATA, $serializedData);

        // Test if we can create a tree back from serialized data.
        $tree_from_serializedData = new Tree($serializedData, ['rootId' => '', 'id' => 'id_node', 'parent' => 'id_parent']);
        static::assertSame($serializedData, DefaultArraySerializer::toArray($tree_from_serializedData));
    }

    /**
     * @test
     */
    public function writableTreeSerializationTest() {
        $tree = self::buildWritableTree();

        $serializedData = DefaultArraySerializer::toArray($tree);

        $tree_from_serializedData = new TreeWritable($serializedData, ['rootId' => '', 'id' => 'id_node', 'parent' => 'id_parent']);
        static::assertSame($serializedData, DefaultArraySerializer::toArray($tree_from_serializedData));

        $tree_from_serializedData->getNodeById('high-school')->delete();
        static::assertSame(self::SERIALIZED_DATA, DefaultArraySerializer::toArray($tree_from_serializedData));
    }

    private static function buildWritableTree()
    {
        $data = self::dataWithStringKeys(true, 'id_node', 'id_parent');

        $tree = new TreeWritable($data, ['rootId' => '', 'id' => 'id_node', 'parent' => 'id_parent']);

        $node_id = 'high-school';
        $parent_id = 'school';
        $node = $tree->createNode($node_id, $parent_id, []);
        $parent = $tree->getNodeById($parent_id);

        $parent->addChild($node);

        return $tree;
    }

    // @todo remove this duplicate and transport to TestService class
    private static function dataWithStringKeys(bool $sorted = true, string $idName = 'id', string $parentName = 'parent'): array
    {
        $data = [
            [$idName => 'vehicle', $parentName => ''],
            [$idName => 'bicycle', $parentName => 'vehicle'],
            [$idName => 'car', $parentName => 'vehicle'],
            [$idName => 'building', $parentName => ''],
            [$idName => 'school', $parentName => 'building'],
            [$idName => 'library', $parentName => 'building'],
            [$idName => 'primary-school', $parentName => 'school'],
        ];

        if ($sorted) {
            usort(
                $data,
                function ($a, $b) use ($idName) {
                    if ($a[$idName] < $b[$idName]) {
                        return -1;
                    }
                    if ($a[$idName] > $b[$idName]) {
                        return 1;
                    }

                    return 0;
                }
            );
        }

        return $data;
    }
}
