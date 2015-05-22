<?php

namespace DC\Bundler;

class Content {

    private $contentType;
    private $content;
    /**
     * @var bool
     */
    private $compiled;

    function __construct($contentType, $content, $compiled = true)
    {
        $this->contentType = $contentType;
        $this->content = $content;
        $this->compiled = $compiled;
    }

    function getContent() { return $this->content; }
    function getContentType() { return $this->contentType; }
    function wasCompiled() { return $this->compiled; }
}