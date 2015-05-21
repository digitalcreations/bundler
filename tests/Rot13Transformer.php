<?php

namespace DC\Tests\Bundler;

class Rot13Transformer implements \DC\Bundler\ITransformer {

    function getName()
    {
        return "rot13";
    }

    function transform(\DC\Bundler\Content $content, $file = null)
    {
        return new \DC\Bundler\Content("text/plain", str_rot13($content->getContent()));
    }

    /**
     * @return string The MIME type this outputs (e.g. application/javascript)
     */
    function getOutputContentType()
    {
        return "text/plain";
    }
}