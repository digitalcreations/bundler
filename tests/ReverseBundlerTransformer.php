<?php

namespace DC\Tests\Bundler;

class ReverseBundlerTransformer implements \DC\Bundler\IMultiFileTransformer {

    /**
     * @param \DC\Bundler\Content[] $contents The content to optimize
     * @return \DC\Bundler\Content
     */
    function transformMultiple(array $contents)
    {
        return new \DC\Bundler\Content("text/plain", implode("", array_map(function (\DC\Bundler\Content $c) {
                        return strrev($c->getContent());
                    }, $contents)));
    }

    /**
     * @return string Short name to identify this in configuration files.
     */
    function getName()
    {
        return "reverse-bundle";
    }

    /**
     * Allows the transformer to run even in debug mode.
     *
     * This is very useful for file formats that the browser cannot understand natively (e.g. Less files).
     *
     * @return bool Return true to run this transformer in debug mode.
     */
    function runInDebugMode()
    {
        return false;
    }

    /**
     * @param \DC\Bundler\Content $content The content to optimize
     * @param string|null $file The path to the file that is processed, or null if the content is not on disk
     * @throws \Exception
     * @return Content
     */
    function transform(\DC\Bundler\Content $content, $file = null)
    {
        throw new \Exception("Not supported");
    }

    /**
     * @return string The MIME type this outputs (e.g. application/javascript)
     */
    function getOutputContentType()
    {
        return "text/plain";
    }
}