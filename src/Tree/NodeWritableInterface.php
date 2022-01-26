<?php

namespace BlueM\Tree;

use BlueM\TreeWritableInterface;

// @todo: make move method
// @todo: make methods with tags @helper to work manually
interface NodeWritableInterface extends NodeInterface
{
    /**
     * Get node tree.
     *
     * @return TreeWritableInterface|null
     */
    public function getTree(): ?TreeWritableInterface;

    /**
     * Set node tree.
     *
     * @param TreeWritableInterface $tree
     * @return mixed
     */
    public function setTree(TreeWritableInterface $tree);

    /**
     * Deletes this node from parent. Note that all its children and Descendants will be deleted as well.
     *
     * @return NodeInterface[]
     *  Node children in case you want to perform some actions with them.
     *
     * @throws \RuntimeException
     */
    public function delete(): array;

    /**
     * Deletes only node children, and their children.
     */
    public function deleteDescendants(): void;

    /**
     * Deletes this node and transforms its children to deleted node parent.
     */
    public function deleteButSaveDescendants(): void;

    /**
     * Removes given node from this node children and unsets its parent. This does not change tree nodes list.
     *
     * This method is mostly used by other node methods, so it's not recommended using it manually.
     *
     * @helper
     *
     * @param string $id
     */
    public function deleteChildById(string $id): void;

    /**
     * Checks if this node has given child id in list.
     *
     * @param string $id
     * @return bool
     */
    public function hasChild(string $id): bool;

    /**
     * Unsets node parent.
     */
    public function unsetParent(): void;

    /**
     * Sets node parent. Note that this method won't change node position inside the tree.
     * If null given it unsets parent.
     *
     * This method is mostly used by other node methods, so it's not recommended using it manually.
     *
     * @helper
     */
    public function setParent(NodeInterface $node): void;
}
