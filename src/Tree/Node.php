<?php

namespace BlueM\Tree;

/**
 * Represents a node in a tree of nodes.
 *
 * @author  Carsten Bluem <carsten@bluem.net>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 3-Clause License
 */
class Node implements NodeInterface, \JsonSerializable
{
    /**
     * Associative array, at least having keys "id" and "parent". Other keys may be added as needed.
     *
     * @var array
     */
    protected $properties = [];

    /**
     * Reference to the parent node, in case of the root object: null.
     *
     * @var NodeInterface
     */
    protected $parent;

    /**
     * Indexed array of child nodes in correct order.
     *
     * @var array
     */
    protected $children = [];

    /**
     * {@inheritdoc}
     */
    public function __construct($id, $parent, array $properties = [])
    {
        $this->properties = array_change_key_case($properties, CASE_LOWER);
        unset($this->properties['id'], $this->properties['parent']);
        $this->properties['id'] = $id;
        $this->properties['parent'] = $parent;
    }

  /**
   * {@inheritdoc}
   */
    public function addChild(NodeInterface $child): void
    {
        $this->children[] = $child;
        $child->parent = $this;
        $child->properties['parent'] = $this->getId();
    }

  /**
   * {@inheritdoc}
   */
    public function getPrecedingSibling(): ?NodeInterface
    {
        return $this->getSibling(-1);
    }

  /**
   * {@inheritdoc}
   */
    public function getFollowingSibling(): ?NodeInterface
    {
        return $this->getSibling(1);
    }

    /**
     * Returns the sibling with the given offset from this node, or NULL if there is no such sibling.
     */
    protected function getSibling(int $offset): ?NodeInterface
    {
        $siblingsAndSelf = $this->parent->getChildren();
        $pos = array_search($this, $siblingsAndSelf, true);

        return $siblingsAndSelf[$pos + $offset] ?? null;
    }

  /**
   * {@inheritdoc}
   */
    public function getSiblings(): array
    {
        return $this->getSiblingsGeneric(false);
    }

  /**
   * {@inheritdoc}
   */
    public function getSiblingsAndSelf(): array
    {
        return $this->getSiblingsGeneric(true);
    }

    protected function getSiblingsGeneric(bool $includeSelf): array
    {
        $siblings = [];
        foreach ($this->parent->getChildren() as $child) {
            if ($includeSelf || (string) $child->getId() !== (string) $this->getId()) {
                $siblings[] = $child;
            }
        }

        return $siblings;
    }

  /**
   * {@inheritdoc}
   */
    public function getChildren(): array
    {
        return $this->children;
    }

  /**
   * {@inheritdoc}
   */
    public function getParent(): ?NodeInterface
    {
        return $this->parent ?? null;
    }

  /**
   * {@inheritdoc}
   */
    public function getId()
    {
        return $this->properties['id'];
    }

  /**
   * {@inheritdoc}
   */
    public function get(string $name)
    {
        $lowerName = strtolower($name);
        if (isset($this->properties[$lowerName])) {
            return $this->properties[$lowerName];
        }
        throw new \InvalidArgumentException(
            "Undefined property: $name (Node ID: " . $this->properties['id'] . ')'
        );
    }

  /**
   * {@inheritdoc}
   */
    public function __call(string $name, $args)
    {
        $lowerName = strtolower($name);
        if (0 === strpos($lowerName, 'get')) {
            $property = substr($lowerName, 3);
            if (array_key_exists($property, $this->properties)) {
                return $this->properties[$property];
            }
        }
        throw new \BadFunctionCallException("Invalid method $name() called");
    }

  /**
   * {@inheritdoc}
   */
    public function __get(string $name)
    {
        if (in_array($name, $this->getReservedPropertyNames())) {
            return $this->$name;
        }
        $lowerName = strtolower($name);
        if (array_key_exists($lowerName, $this->properties)) {
            return $this->properties[$lowerName];
        }
        throw new \RuntimeException(
            "Undefined property: $name (Node ID: " . $this->properties['id'] . ')'
        );
    }

    /**
     * Get reserved properties names.
     *
     * @return string[]
     */
    protected function getReservedPropertyNames()
    {
        return ['parent', 'children'];
    }

  /**
   * {@inheritdoc}
   */
    public function __set(string $name, $value)
    {
        if (in_array($name, $this->getReservedPropertyNames())) {
            $this->$name = $value;
            return;
        }
        $lowerName = strtolower($name);
        $this->properties[$lowerName] = $value;
    }

  /**
   * {@inheritdoc}
   */
    public function __isset(string $name): bool
    {
        return 'parent' === $name ||
               'children' === $name ||
               array_key_exists(strtolower($name), $this->properties);
    }

  /**
   * {@inheritdoc}
   */
    public function getLevel(): int
    {
        if (null === $this->parent) {
            return 0;
        }

        return $this->parent->getLevel() + 1;
    }

  /**
   * {@inheritdoc}
   */
    public function hasChildren(): bool
    {
        return \count($this->children) > 0;
    }

  /**
   * {@inheritdoc}
   */
    public function countChildren(): int
    {
        return \count($this->children);
    }

  /**
   * {@inheritdoc}
   */
    public function getDescendants(): array
    {
        return $this->getDescendantsGeneric(false);
    }

  /**
   * {@inheritdoc}
   */
    public function getDescendantsAndSelf(): array
    {
        return $this->getDescendantsGeneric(true);
    }

    protected function getDescendantsGeneric(bool $includeSelf): array
    {
        $descendants = $includeSelf ? [$this] : [];
        foreach ($this->children as $childnode) {
            $descendants[] = $childnode;
            if ($childnode->hasChildren()) {
                // Note: array_merge() in loop looks bad, but measuring showed it's OK
                // here, unless maybe really large amounts of data
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $descendants = array_merge($descendants, $childnode->getDescendants());
            }
        }

        return $descendants;
    }

  /**
   * {@inheritdoc}
   */
    public function getAncestors(): array
    {
        return $this->getAncestorsGeneric(false);
    }

  /**
   * {@inheritdoc}
   */
    public function getAncestorsAndSelf(): array
    {
        return $this->getAncestorsGeneric(true);
    }

    protected function getAncestorsGeneric(bool $includeSelf): array
    {
        if (null === $this->parent) {
            return [];
        }

        return array_merge($includeSelf ? [$this] : [], $this->parent->getAncestorsGeneric(true));
    }

  /**
   * {@inheritdoc}
   */
    public function toArray(): array
    {
        return $this->properties;
    }

  /**
   * {@inheritdoc}
   */
    public function __toString(): string
    {
        return (string) $this->properties['id'];
    }

    /**
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
