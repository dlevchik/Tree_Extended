<?php

namespace BlueM\Tree;

interface NodeInterface
{
  /**
   * @param string|int $id
   * @param string|int $parent
   */
    public function __construct($id, $parent, array $properties = []);

  /**
   * @param mixed  $args
   *
   * @throws \BadFunctionCallException
   *
   * @return mixed
   */
    public function __call(string $name, $args);

  /**
   * Returns a single node property by its name.
   *
   * @throws \InvalidArgumentException
   *
   * @return mixed
   *
   * @todo delete this method for only __get() usage
   */
    public function get(string $name);

  /**
   * @throws \RuntimeException
   *
   * @return mixed
   */
    public function __get(string $name);

  /**
   * @param string $name
   *
   * @return bool
   */
    public function __isset(string $name): bool;

  /**
   * @param string $name
   * @param $value
   *
   * @return mixed
   */
    public function __set(string $name, $value);

  /**
   * Returns a textual representation of this node.
   *
   * @return string The node's ID
   */
    public function __toString(): string;

  /**
   * Adds the given node to this node's children.
   */
    public function addChild(NodeInterface $child): void;

  /**
   * Returns previous node in the same level, or NULL if there's no previous node.
   */
    public function getPrecedingSibling(): ?NodeInterface;

  /**
   * Returns following node in the same level, or NULL if there's no following node.
   */
    public function getFollowingSibling(): ?NodeInterface;

  /**
   * Returns siblings of the node.
   *
   * @return NodeInterface[]
   */
    public function getSiblings(): array;

  /**
   * Returns siblings of the node and the node itself.
   *
   * @return NodeInterface[]
   */
    public function getSiblingsAndSelf(): array;

  /**
   * Returns all direct children of this node.
   *
   * @return NodeInterface[]
   */
    public function getChildren(): array;

  /**
   * Returns the parent node or null, if the node is the root node.
   */
    public function getParent(): ?NodeInterface;

  /**
   * Returns a node's ID.
   *
   * @return mixed
   */
    public function getId();

  /**
   * Returns the level of this node in the tree.
   *
   * @return int Tree level (1 = top level)
   */
    public function getLevel(): int;

  /**
   * Returns whether or not this node has at least one child node.
   */
    public function hasChildren(): bool;

  /**
   * Returns number of children this node has.
   */
    public function countChildren(): int;

  /**
   * Returns any node below (children, grandchildren, ...) this node.
   *
   * The order is as follows: A, A1, A2, ..., B, B1, B2, ..., where A and B are
   * 1st-level items in correct order, A1/A2 are children of A in correct order,
   * and B1/B2 are children of B in correct order. If the node itself is to be
   * included, it will be the very first item in the array.
   *
   * @return NodeInterface[]
   */
    public function getDescendants(): array;

  /**
   * Returns an array containing this node and all nodes below (children,
   * grandchildren, ...) it.
   *
   * For order of nodes, see comments on getDescendants()
   *
   * @return NodeInterface[]
   */
    public function getDescendantsAndSelf(): array;

  /**
   * Returns any node above (parent, grandparent, ...) this node.
   *
   * The array returned from this method will include the root node. If you
   * do not want the root node, you should do an array_pop() on the array.
   *
   * @return NodeInterface[] Indexed array of nodes, sorted from the nearest
   *                one (or self) to the most remote one
   */
    public function getAncestors(): array;

  /**
   * Returns an array containing this node and all nodes above (parent, grandparent,
   * ...) it.
   *
   * Note: The array returned from this method will include the root node. If you
   * do not want the root node, you should do an array_pop() on the array.
   *
   * @return NodeInterface[] Indexed, sorted array of nodes: self, parent, grandparent, ...
   */
    public function getAncestorsAndSelf(): array;

  /**
   * Self to array presentation.
   *
   * @return array
   */
    public function toArray(): array;
}
