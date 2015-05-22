<?php

namespace DC\Bundler;

/**
 * Store compiled files.
 *
 * @package DC\Bundler
 */
interface ICompiledAssetStore {
    /**
     * @param string $name
     * @param \DC\Bundler\Content[] $content
     * @return void
     */
    function save($name, array $content);

    /**
     * @param string $name
     * @return \DC\Bundler\Content[]
     */
    function get($name);

    /**
     * @param $name
     * @return \DateTime
     */
    function getSaveTime($name);
} 