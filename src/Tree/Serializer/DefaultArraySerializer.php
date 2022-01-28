<?php

namespace BlueM\Tree\Serializer;

use BlueM\TreeJsonSerializableInterface;

class DefaultArraySerializer extends FlatTreeJsonSerializer
{
    /**
     * {@inheritdoc}
     */
    public function serialize(TreeJsonSerializableInterface $tree): array
    {
        $nodes = parent::serialize($tree);
        $idKey = $tree->getIdKey();
        $parentKey = $tree->getParentKey();

        foreach ($nodes as $key => $node) {
            $nodes[$key] = $node->toArray();

            $nodes[$key] = [
                $idKey => $nodes[$key]['id'],
                $parentKey => $nodes[$key]['parent'],
            ] + $nodes[$key];
        }
        return $nodes;
    }

    /**
     * Static version of serialize function.
     *
     * @param TreeJsonSerializableInterface $tree
     * @return array|mixed
     */
    public static function toArray(TreeJsonSerializableInterface $tree)
    {
        return (new static())->serialize($tree);
    }
}
