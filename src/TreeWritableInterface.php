<?php

namespace BlueM;

use BlueM\Tree\NodeInterface;
use BlueM\Tree\NodeWritableInterface;

// @todo method to clear tree fully
interface TreeWritableInterface extends TreeInterface
{
    /**
     * Is given node exists in node list.
     *
     * @param string $id
     * @return bool
     */
    public function isNodeExistsById(string $id): bool;

    /**
     * Deletes given nodes from node storage. It also detaches each node from its parent.
     * Note that node descendants will still exist in nodes list unless specified.
     *
     * This method is mostly used by other node methods, so it's not recommended using it manually.
     *
     * @helper
     *
     * @param NodeWritableInterface[] $nodes
     * @return NodeWritableInterface[]
     *
     * @throws \InvalidArgumentException
     *  When trying to delete root node OR node don't attached to this tree.
     */
    public function unsetNodes(array $nodes): array;

    /**
     * Add node to tree nodes list. While createNode() used only for node initialization, this method is aimed to add
     * node to tree, and it's parent. If node wasn't already in parent children, add it. Add node children to tree as
     * well.
     *
     * @param NodeInterface $node
     *
     * @throws \RuntimeException
     *  When node id ia already in use.
     *
     * @todo force rewrite
     */
    public function addNode(NodeInterface $node): void;

    /**
     * Can be used to calculate tree nodes again based on every node children. Used for correct getting node by id.
     */
    public function regenerateNodesList(): void;
}
