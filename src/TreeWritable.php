<?php

namespace BlueM;

use BlueM\Tree\NodeInterface;
use BlueM\Tree\NodeWritable;

class TreeWritable extends TreeNullable implements TreeWritableInterface
{
    /**
     * {@inheritdoc}
     */
    public function deleteNodes(array $nodes): array
    {
        foreach ($nodes as $node) {
            if ($node->getTree() !== $this) {
                throw new \InvalidArgumentException("All given nodes should be attached to this tree");
            }
            if ($node->getId() === $this->rootId) {
                throw new \InvalidArgumentException("You can't delete root node.");
            }

            $node_parent = $node->getParent();
            if (!is_null($node_parent)) {
                $node_parent->deleteChildById($node->id());
            }

            unset($this->nodes[$node->getId()]);
            $node->tree = null;
        }

        return $nodes;
    }

    /**
     * {@inheritdoc}
     */
    public function addNode(NodeInterface $node): void
    {
        if ($this->isNodeExistsById($node->id())) {
            throw new \RuntimeException('Given node id is already in use. You need to delete it before adding a new one.');
        }
        $node->setTree($this);

        $node_parent = $node->getParent();
        if (!is_null($node_parent) && !$node_parent->hasChild($node->id())) {
            $node_parent->addChild($node);
        }

        $this->nodes[$node->id()] = $node;

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $this->addNode($child);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function regenerateNodesList(): void
    {
        $this->nodes = $this->nodes[$this->rootId]->getDescendantsAndSelf();
    }

    /**
     * {@inheritdoc}
     */
    public function createNode($id, $parent, array $properties): NodeInterface
    {
        return new NodeWritable($id, $parent, $properties);
    }

    /**
     * {@inheritdoc}
     */
    public function isNodeExistsById(string $id): bool
    {
        return isset($this->nodes[$id]);
    }
}
