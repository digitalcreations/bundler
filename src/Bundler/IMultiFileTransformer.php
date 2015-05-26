<?php

namespace DC\Bundler;

interface IMultiFileTransformer extends ITransformer {
    /**
     * @param \DC\Bundler\Content[] $contents The content to optimize
     * @return \DC\Bundler\Content
     */
    function transformMultiple(array $contents);
} 