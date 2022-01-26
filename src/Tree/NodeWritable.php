<?php

namespace BlueM\Tree;

use BlueM\TreeWritableInterface;

class NodeWritable extends NodeNullable implements NodeWritableInterface
{
    /**
     * @var TreeWritableInterface
     */
    protected $tree;

    /**
     * {@inheritdoc}
     */
    protected function getReservedPropertyNames()
    {
        return array_merge(parent::getReservedPropertyNames(), ['tree']);
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(NodeInterface $child): void
    {
        $tree = $this->getTree();
        if ($this->hasChild($child->getId()) || (!is_null($tree) && $tree->isNodeExistsById($child->getId()))) {
            throw new \RuntimeException('Given node id is already in use. You need to delete it before adding a new one.');
        }

        $this->children[$child->getId()] = $child;
        $child->parent = $this;
        $child->properties['parent'] = $this->getId();

        if (is_null($tree)) {
            return;
        }
        $tree->addNode($child);
    }

    /**
     * {@inheritdoc}
     */
    public function getTree(): ?TreeWritableInterface
    {
        return $this->tree ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function setTree(TreeWritableInterface $tree)
    {
        $this->tree = $tree;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(): array
    {
        $tree = $this->getTree();
        if (is_null($tree)) {
            throw new \RuntimeException('You need to attach node to a tree before deleting it.');
        }

        $nodes_to_delete = $this->getDescendantsAndSelf();
        return $tree->deleteNodes($nodes_to_delete);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDescendants(): void
    {
        $tree = $this->getTree();
        if (is_null($tree)) {
            throw new \RuntimeException('You need to attach node to a tree before deleting it.');
        }

        $nodes_to_delete = $this->getDescendants();
        $tree->deleteNodes($nodes_to_delete);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteButSaveDescendants(): void
    {
        $tree = $this->getTree();
        if (is_null($tree)) {
            throw new \RuntimeException('You need to attach node to a tree before deleting it.');
        }

        $descendants = $this->getDescendants();
        $parent = $this->getParent();

        $tree->deleteNodes(array_merge($descendants, $this));
        foreach ($descendants as $descendant) {
            $descendant->setParent($parent);
            $tree->addNode($descendant);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteChildById(string $id): void
    {
        $child = $this->children[$id] ?? null;
        if (!is_null($child)) {
            $child->unsetParent();
        }

        unset($this->children[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function hasChild(string $id): bool
    {
        return isset($this->children[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetParent(): void
    {
        unset($this->parent, $this->properties['parent']);
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(NodeInterface $node = null): void
    {
        if (is_null($node)) {
            $this->unsetParent();
            return;
        }
        $this->parent = $node;
        $this->properties['parent'] = $node->getId();
    }
}
