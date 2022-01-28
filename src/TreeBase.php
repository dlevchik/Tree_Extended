<?php

namespace BlueM;

use BlueM\Tree\Exception\InvalidDatatypeException;
use BlueM\Tree\Exception\InvalidParentException;
use BlueM\Tree\NodeInterface;

class TreeBase implements TreeInterface
{
  /**
   * @var int|float|string|null
   */
    protected $rootId = 0;

  /**
   * @var string
   */
    protected $idKey = 'id';

  /**
   * @var string
   */
    protected $parentKey = 'parent';

  /**
   * @var NodeInterface[]
   */
    protected $nodes;

  /**
   * @var callable
   */
    protected $buildWarningCallback;

  /**
   * {@inheritdoc}
   */
    public function __construct($data = [], array $options = [])
    {
        $options = array_change_key_case($options, CASE_LOWER);

        if (array_key_exists('rootid', $options)) {
            if (!\is_scalar($options['rootid']) && null !== $options['rootid']) {
                throw new \InvalidArgumentException('Option “rootid” must be scalar or null');
            }
            $this->rootId = $options['rootid'];
        }

        if (!empty($options['id'])) {
            if (!\is_string($options['id'])) {
                throw new \InvalidArgumentException('Option “id” must be a string');
            }
            $this->idKey = $options['id'];
        }

        if (!empty($options['parent'])) {
            if (!\is_string($options['parent'])) {
                throw new \InvalidArgumentException('Option “parent” must be a string');
            }
            $this->parentKey = $options['parent'];
        }

        if (!empty($options['buildwarningcallback'])) {
            if (!is_callable($options['buildwarningcallback'])) {
                throw new \InvalidArgumentException('Option “buildWarningCallback” must be a callable');
            }
            $this->buildWarningCallback = $options['buildwarningcallback'];
        } else {
            $this->buildWarningCallback = [$this, 'buildWarningHandler'];
        }

        $this->build($data);
    }

  /**
   * {@inheritdoc}
   */
    public function rebuildWithData(array $data): void
    {
        $this->build($data);
    }

  /**
   * Core method for creating the tree.
   *
   * @param array|\Traversable $data The data from which to generate the tree
   *
   * @throws InvalidParentException
   * @throws InvalidDatatypeException
   */
    protected function build($data): void
    {
        if (!\is_array($data) && !($data instanceof \Traversable)) {
            throw new InvalidDatatypeException('Data must be an iterable (array or implement Traversable)');
        }

        $this->nodes = [];
        $children = [];

      // Create the root node
        $this->nodes[$this->rootId] = $this->createNode($this->rootId, null, []);

        foreach ($data as $row) {
            if ($row instanceof \Iterator) {
                $row = iterator_to_array($row);
            }

            $this->nodes[$row[$this->idKey]] = $this->createNode(
                $row[$this->idKey],
                $row[$this->parentKey],
                $row
            );

            if (empty($children[$row[$this->parentKey]])) {
                $children[$row[$this->parentKey]] = [$row[$this->idKey]];
            } else {
                $children[$row[$this->parentKey]][] = $row[$this->idKey];
            }
        }

        foreach ($children as $pid => $childIds) {
            foreach ($childIds as $id) {
                if (isset($this->nodes[$pid])) {
                    if ($this->nodes[$pid] === $this->nodes[$id]) {
                        call_user_func($this->buildWarningCallback, $this->nodes[$id], $pid);
                    } else {
                        $this->nodes[$pid]->addChild($this->nodes[$id]);
                    }
                } else {
                    call_user_func($this->buildWarningCallback, $this->nodes[$id], $pid);
                }
            }
        }
    }

  /**
   * @param mixed $parentId
   */
    protected function buildWarningHandler(NodeInterface $node, $parentId): void
    {
        if ((string) $parentId === (string) $node->getId()) {
            throw new InvalidParentException('Node with ID ' . $node->getId() . ' references its own ID as parent ID');
        }

        if (empty($this->nodes[$parentId])) {
            throw new InvalidParentException('Node with ID ' . $node->getId() . " points to non-existent parent with ID $parentId");
        }

        throw new \InvalidArgumentException('Unrecognized build warning reason');
    }

  /**
   * {@inheritdoc}
   */
    public function __toString(): string
    {
        $str = [];
        foreach ($this->getNodes() as $node) {
            $indent1st = str_repeat('  ', $node->getLevel() - 1) . '- ';
            $indent = str_repeat('  ', ($node->getLevel() - 1) + 2);
            $node = (string) $node;
            $str[] = $indent1st . str_replace("\n", "$indent\n  ", $node);
        }

        return implode("\n", $str);
    }

  /**
   * {@inheritdoc}
   */
    public function createNode($id, $parent, array $properties): NodeInterface
    {
        throw new \BadMethodCallException("Ypu can't call this method in TreeBase, you need to rewrite it with subclass.");
    }

  /**
   * {@inheritdoc}
   */
    public function getNodes(): array
    {
        $nodes = [];
        foreach ($this->nodes[$this->rootId]->getDescendants() as $subnode) {
            $nodes[] = $subnode;
        }

        return $nodes;
    }

  /**
   * {@inheritdoc}
   */
    public function getNodeById($id): ?NodeInterface
    {
        if (empty($this->nodes[$id])) {
            throw new \InvalidArgumentException("Invalid node primary key $id");
        }

        return $this->nodes[$id];
    }

  /**
   * {@inheritdoc}
   */
    public function getRootNodes(): array
    {
        return $this->nodes[$this->rootId]->getChildren();
    }

  /**
   * {@inheritdoc}
   */
    public function getNodeByValuePath(string $name, array $search): ?NodeInterface
    {
        $findNested = function (array $nodes, array $tokens) use ($name, &$findNested) {
            $token = array_shift($tokens);
            foreach ($nodes as $node) {
                $nodeName = $node->get($name);
                if ($nodeName === $token) {
                    // Match
                    if (\count($tokens)) {
                        // Search next level
                        return $findNested($node->getChildren(), $tokens);
                    }

                    // We found the node we were looking for
                    return $node;
                }
            }

            return null;
        };

        return $findNested($this->getRootNodes(), $search);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdKey(): string
    {
        return $this->idKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentKey(): string
    {
        return $this->parentKey;
    }
}
