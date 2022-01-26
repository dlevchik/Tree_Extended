<?php

namespace BlueM;

use BlueM\Tree\NodeNullable;
use BlueM\Tree\NodeInterface;

class TreeNullable extends TreeBase {

  /**
   * {@inheritdoc}
   *
   * The main difference between parent is that this returns null instead of
   * throwing an exception.
   */
  public function getNodeById($id): ?NodeInterface {
    try {
      return parent::getNodeById($id);
    } catch (\InvalidArgumentException $e) {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  function createNode($id, $parent, array $properties): NodeInterface {
    return new NodeNullable($id, $parent, $properties);
  }

}
