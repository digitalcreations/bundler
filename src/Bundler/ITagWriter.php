<?php

namespace DC\Bundler;

interface ITagWriter {
    /**
     * @return string[] The supported MIME types.
     */
    function getSupportedContentTypes();

    /**
     * @param string $path Web root relative path
     * @return string HTML tag that includes this. Empty string is acceptable.
     */
    function getTagForPath($path);
}