<?php

namespace Bundler\AssetStores;

class InMemoryCompiledAssetStore implements \DC\Bundler\ICompiledAssetStore {

    private $store = [];
    private $times = [];

    /**
     * @inheritdoc
     */
    function save($name, array $content)
    {
        $this->store[$name] = $content;
        $this->times[$name] = new \DateTime();
    }

    /**
     * @inheritdoc
     */
    function get($name)
    {
        return $this->store[$name];
    }

    /**
     * @inheritdoc
     */
    function getSaveTime($name)
    {
        return isset($this->times[$name]) ? $this->times[$name] : null;
    }
}