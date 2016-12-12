<?php

namespace DC\Bundler;

/**
 * Interface to allow you to write bundles to a file, so your web server can take over.
 */
interface IBundleWriter {
    /**
     * @param string $webRoot
     * @param string $name
     * @param string $content
     */
    function writeBundle($webRoot, $name, $content);
}