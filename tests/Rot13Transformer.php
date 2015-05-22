<?php

namespace DC\Tests\Bundler;

class Rot13Transformer implements \DC\Bundler\ITransformer {

    function getName()
    {
        return "rot13";
    }

    function transform(\DC\Bundler\Content $content, $file = null)
    {
        return new \DC\Bundler\Content("application/javascript", str_rot13($content->getContent()));
    }

    /**
     * @inheritdoc
     */
    function getOutputContentType()
    {
        return "application/javascript";
    }

    /**
     * @inheritdoc
     */
    function runInDebugMode()
    {
        return true;
    }
}