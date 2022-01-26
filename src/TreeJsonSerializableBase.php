<?php

namespace BlueM;

use BlueM\Tree\Serializer\FlatTreeJsonSerializer;
use BlueM\Tree\Serializer\TreeJsonSerializerInterface;

// @todo rename jsonSerializer to ordinary serializer
class TreeJsonSerializableBase extends TreeBase implements TreeJsonSerializableInterface
{
  /**
   * @var TreeJsonSerializerInterface
   */
    protected $jsonSerializer;

  /**
   * {@inheritdoc}
   */
    public function __construct($data = [], array $options = [])
    {
        $options = array_change_key_case($options, CASE_LOWER);

        if (!empty($options['jsonserializer'])) {
            if (!is_object($options['jsonserializer'])) {
                throw new \InvalidArgumentException('Option “jsonSerializer” must be an object');
            }
            $this->setJsonSerializer($options['jsonserializer']);
        }

        parent::__construct($data, $options);
    }

  /**
   * {@inheritdoc}
   */
    public function setJsonSerializer(TreeJsonSerializerInterface $serializer = null): void
    {
        $this->jsonSerializer = $serializer;
    }

  /**
   * {@inheritdoc}
   */
    public function jsonSerialize()
    {
        if (!$this->jsonSerializer) {
            $this->jsonSerializer = new FlatTreeJsonSerializer();
        }

        return $this->jsonSerializer->serialize($this);
    }
}
