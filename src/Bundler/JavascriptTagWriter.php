<?php

namespace DC\Bundler;

class JavascriptTagWriter implements ITagWriter {

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
        return '<script src="' . htmlentities($path) . '" />';
    }
}