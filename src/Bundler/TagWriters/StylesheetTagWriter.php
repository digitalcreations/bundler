<?php

namespace DC\Bundler\TagWriters;

class StylesheetTagWriter implements \DC\Bundler\ITagWriter {

    /**
     * @return string[] The supported MIME types.
     */
    function getSupportedContentTypes()
    {
        return ["text/css"];
    }

    /**
     * @param string $path Web root relative path
     * @return string HTML tag that includes this. Empty string is acceptable.
     */
    function getTagForPath($path)
    {
        return '<link rel="stylesheet" href="' . htmlentities($path) . '" />';
    }
}