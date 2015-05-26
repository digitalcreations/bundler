<?php

namespace DC\Bundler\Transformers;

class BundleTransformer implements \DC\Bundler\IMultiFileTransformer {
    /**
     * @inheritdoc
     */
    function transformMultiple(array $contents)
    {
        $flatContents = \DC\Bundler\ArrayHelper::flatten($contents);
        $outputText = array_map(function(\DC\Bundler\Content $c) { return $c->getContent(); }, $flatContents);
        $types = array_values(array_unique(array_map(function(\DC\Bundler\Content $c) { return $c->getContentType(); }, $flatContents)));
        if (count($types) !== 1) {
            throw new \DC\Bundler\Exceptions\InvalidConfigurationException("Nodes have different content types, cannot merge.");
        }
        return new \DC\Bundler\Content($types[0], implode('', $outputText));
    }

    /**
     * @inheritdoc
     */
    function getName()
    {
        return "bundle";
    }

    /**
     * @inheritdoc
     */
    function runInDebugMode()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    function transform(\DC\Bundler\Content $content, $file = null)
    {
        throw new \Exception("Not supported");
    }

    /**
     * @inheritdoc
     */
    function getOutputContentType()
    {
        return null;
    }
}