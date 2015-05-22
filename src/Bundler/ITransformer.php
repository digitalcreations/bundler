<?php

namespace DC\Bundler;

interface ITransformer {
    /**
     * @return string Short name to identify this in configuration files.
     */
    function getName();

    /**
     * Allows the transformer to run even in debug mode.
     *
     * This is very useful for file formats that the browser cannot understand natively (e.g. Less files).
     *
     * @return bool Return true to run this transformer in debug mode.
     */
    function runInDebugMode();

    /**
     * @param \DC\Bundler\Content $content The content to optimize
     * @param string|null $file The path to the file that is processed, or null if the content is not on disk
     * @return Content
     */
    function transform(Content $content, $file = null);

    /**
     * @return string The MIME type this outputs (e.g. application/javascript)
     */
    function getOutputContentType();
}