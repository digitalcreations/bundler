<?php

namespace DC\Bundler;

class Content {

    private $contentType;
    private $content;

    function __construct($contentType, $content)
    {
        $this->contentType = $contentType;
        $this->content = $content;
    }

    function getContent() { return $this->content; }
    function getContentType() { return $this->contentType; }
}