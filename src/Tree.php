<?php

namespace BlueM;

use BlueM\Tree\Node;
use BlueM\Tree\NodeInterface;

/**
 * Builds and gives access to a tree of nodes which is constructed thru nodes' parent node ID references.
 *
 * @author Carsten Bluem <carsten@bluem.net>
 * @license http://www.opensource.org/licenses/bsd-license.php BSD 3-Clause License
 */
class Tree extends TreeJsonSerializableBase
{
  /**
   * {@inheritdoc}
   */
    public function createNode($id, $parent, array $properties): NodeInterface
    {
        return new Node($id, $parent, $properties);
    }
}
