<?php

namespace DC\Bundler;

class Content {

    private $contentType;
    private $content;
    /**
     * @var bool
     */
    private $compiled;
    /**
     * @var null
     */
    private $path;

    function __construct($contentType, $content, $path = null, $compiled = true)
    {
        $this->contentType = $contentType;
        $this->content = $content;
        $this->compiled = $compiled;
        $this->path = $path;
    }

    function getContent() { return $this->content; }
    function getContentType() { return $this->contentType; }
    function getPath() { return $this->path; }
    function wasCompiled() { return $this->compiled; }
}