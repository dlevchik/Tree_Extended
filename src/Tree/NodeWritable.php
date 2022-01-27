<?php

namespace BlueM\Tree;

use BlueM\TreeWritableInterface;

class NodeWritable extends NodeNullable implements NodeWritableInterface
{
    private const UNATACHED_MESSAGE = 'You need to attach node to a tree before deleting it.';

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
    public function getParent(): ?NodeInterface
    {
        if (!isset($this->parent) && !is_null($this->getTree())) {
            $this->parent = $this->getTree()->getNodeById($this->properties['parent']);
        }
        return parent::getParent();
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
            throw new \RuntimeException(self::UNATACHED_MESSAGE);
        }

        $nodes_to_delete = $this->getDescendantsAndSelf();
        return $tree->unsetNodes($nodes_to_delete);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDescendants(): void
    {
        $tree = $this->getTree();
        if (is_null($tree)) {
            throw new \RuntimeException(self::UNATACHED_MESSAGE);
        }

        $nodes_to_delete = $this->getDescendants();
        $tree->unsetNodes($nodes_to_delete);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteButSaveDescendants(): void
    {
        $tree = $this->getTree();
        if (is_null($tree)) {
            throw new \RuntimeException(self::UNATACHED_MESSAGE);
        }

        $descendants = $this->getDescendants();
        $parent = $this->getParent();

        $tree->unsetNodes(array_merge($descendants, [$this]));
        foreach ($descendants as $descendant) {
            $descendant->parent = $parent;
            $tree->addNode($descendant);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unsetChildById(string $id): void
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
}
