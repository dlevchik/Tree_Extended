<?php

namespace BlueM;

use BlueM\Tree\Node;

class TreeNullable extends Tree {

  /**
   * {@inheritdoc}
   *
   * The main difference between parent is that this returns null instead of
   * throwing an exception.
   */
  public function getNodeById($id) :?Node {
    try {
      return parent::getNodeById($id);
    } catch (\InvalidArgumentException $e) {
      return NULL;
    }
  }

}
