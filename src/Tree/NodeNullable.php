<?php

namespace BlueM\Tree;

class NodeNullable extends Node
{
    /**
     * {@inheritdoc}
     */
    public function get(string $name)
    {
        try {
            return parent::get($name);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __call(string $name, $args)
    {
        try {
            return parent::__call($name, $args);
        } catch (\BadFunctionCallException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __get(string $name)
    {
        try {
            return parent::__get($name);
        } catch (\RuntimeException $e) {
            return null;
        }
    }
}
