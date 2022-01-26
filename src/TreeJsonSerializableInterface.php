<?php

namespace BlueM;

use BlueM\Tree\Serializer\TreeJsonSerializerInterface;

interface TreeJsonSerializableInterface extends \JsonSerializable
{
  /**
   * Sets the JSON serializer class to be used, if a different one than the default is required.
   *
   * By passing null, the serializer can be reset to the default one.
   */
    public function setJsonSerializer(TreeJsonSerializerInterface $serializer = null): void;
}
