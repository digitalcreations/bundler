<?php

namespace DC\Bundler\TagWriters;

class JavascriptTagWriter implements \DC\Bundler\ITagWriter {

    /**
     * @return string[] The supported MIME types.
     */
    function getSupportedContentTypes()
    {
        return ["application/javascript", "text/javascript"];
    }

    /**
     * @param string $path Web root relative path
     * @return string HTML tag that includes this. Empty string is acceptable.
     */
    function getTagForPath($path)
    {
        return '<script src="' . htmlentities($path) . '"></script>';
    }
}