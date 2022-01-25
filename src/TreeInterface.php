<?php

namespace BlueM;

use BlueM\Tree\Node;
use BlueM\Tree\NodeInterface;
use BlueM\Tree\Serializer\TreeJsonSerializerInterface;

interface TreeInterface {

  /**
   * API version (will always be in sync with first digit of release version number).
   *
   * @var int
   */
  public const API = 3;

  /**
   * @param array|\Traversable $data    The data for the tree (iterable)
   * @param array $options 0 or more of the following keys, all of which are optional: "rootId" (ID of
   *                       the root node, default: 0), "id" (name of the ID field / array key, default:
   *                       "id"), "parent" (name of the parent ID field / array key, default: "parent"),
   *                       "jsonSerializer" (instance of \BlueM\Tree\Serializer\TreeJsonSerializerInterface),
   *                       "buildWarningCallback" (a callable which is called when detecting data
   *                       inconsistencies such as an invalid parent)
   */
  public function __construct($data = [], array $options = []);

  /**
   * Returns a textual representation of the tree.
   *
   * @return string
   */
  public function __toString(): string;

  /**
   * Returns a flat, sorted array of all node objects in the tree.
   *
   * @return NodeInterface[] Nodes, sorted as if the tree was hierarchical,
   *                i.e.: the first level 1 item, then the children of
   *                the first level 1 item (and their children), then
   *                the second level 1 item and so on.
   */
  public function getNodes(): array;

  /**
   * Build Tree again, if you haven't built it yet during construct.
   *
   * @param array $data
   */
  public function rebuildWithData(array $data): void;

  /**
   * Get node from nodes list by its id.
   *
   * @param $id
   *
   * @return \BlueM\Tree\NodeInterface|null
   */
  public function getNodeById($id): ?NodeInterface;

  /**
   * Returns an array of all nodes in the root level.
   *
   * @return NodeInterface[] Nodes in the correct order
   */
  public function getRootNodes(): array;

  /**
   * Returns the first node for which a specific property's values of all ancestors
   * and the node are equal to the values in the given argument.
   *
   * Example: If nodes have property "name", and on the root level there is a node with
   * name "A" which has a child with name "B" which has a child which has node "C", you
   * would get the latter one by invoking getNodeByValuePath('name', ['A', 'B', 'C']).
   * Comparison is case-sensitive and type-safe.
   */
  public function getNodeByValuePath(string $name, array $search): ?NodeInterface;

}
