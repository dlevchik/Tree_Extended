<?php

namespace BlueM;

use BlueM\Tree\Exception\InvalidDatatypeException;
use BlueM\Tree\Exception\InvalidParentException;
use BlueM\Tree\Node;
use BlueM\Tree\NodeInterface;

class TreeBase implements TreeInterface {

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
  protected function build(array $data): void
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
      throw new InvalidParentException('Node with ID '.$node->getId().' references its own ID as parent ID');
    }

    if (empty($this->nodes[$parentId])) {
      throw new InvalidParentException('Node with ID '.$node->getId()." points to non-existent parent with ID $parentId");
    }

    throw new \InvalidArgumentException('Unrecognized build warning reason');
  }

  /**
   * Returns a textual representation of the tree.
   *
   * @return string
   */
  public function __toString(): string
  {
    $str = [];
    foreach ($this->getNodes() as $node) {
      $indent1st = str_repeat('  ', $node->getLevel() - 1).'- ';
      $indent = str_repeat('  ', ($node->getLevel() - 1) + 2);
      $node = (string) $node;
      $str[] = $indent1st.str_replace("\n", "$indent\n  ", $node);
    }

    return implode("\n", $str);
  }

  /**
   * Creates and returns a node with the given properties.
   *
   * Can be overridden by subclasses to use a Node subclass for nodes.
   *
   * @param string|int $id
   * @param string|int $parent
   */
  protected function createNode($id, $parent, array $properties): NodeInterface
  {
    return new Node($id, $parent, $properties);
  }

  public function getNodes(): array
  {
    // TODO: Implement getNodes() method.
  }

  public function getNodeById($id): ?NodeInterface
  {
    // TODO: Implement getNodeById() method.
  }

  public function getRootNodes(): array
  {
    // TODO: Implement getRootNodes() method.
  }

  public function getNodeByValuePath(string $name, array $search): ?NodeInterface
  {
    // TODO: Implement getNodeByValuePath() method.

  }

}
