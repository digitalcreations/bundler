<?php

namespace DC\Bundler;

use DC\Bundler\Exceptions\InvalidConfigurationException;

class Bundler {
    private $config;
    private $mode;
    /**
     * @var ICompiledAssetStore
     */
    private $assetStore;
    /**
     * @var array|ITransformer[]
     */
    private $transformers;

    /**
     * @param array|string $config The configuration array or a path to a JSON file
     * @param int $mode See \DC\Bundler\Mode
     * @param ICompiledAssetStore $assetStore
     * @param ITransformer[] $transformers
     * @throws Exceptions\InvalidConfigurationException
     */
    function __construct($config,
                         $mode = Mode::Debug,
                         ICompiledAssetStore $assetStore = null,
                         array $transformers = null)
    {
        $this->mode = $mode;
        if (is_array($config)) {
            $this->config = $config;
        }
        elseif (is_string($config) && file_exists($config)) {
            $json = file_get_contents($config);
            $this->config = json_decode($json);
        }

        if (!isset($this->config[Node::WebRoot])) {
            throw new \DC\Bundler\Exceptions\InvalidConfigurationException("Could not locate root folder. Add __webroot to configuration.");
        }
        $this->assetStore = $assetStore;
        if ($assetStore == null) {
            $this->assetStore = new FileBasedCompiledAssetStore();
        }

        $this->transformers = [];
        if (is_array($transformers)) {
            foreach ($transformers as $transformer) {
                $this->transformers[strtolower($transformer->getName())] = $transformer;
            }
        }
    }

    private function getFileListInternal($bundle, $recursive = true) {
        $files = [];
        if (is_array($bundle[Node::Parts])) {
            foreach ($bundle[Node::Parts] as $pattern) {
                if (is_string($pattern)) {
                    $patternFiles = glob($this->config[Node::WebRoot] .'/'. $pattern);
                }
                elseif (is_array($pattern) && $recursive) {
                    $patternFiles = $this->getFileListInternal($pattern);
                }
                elseif (is_array($pattern) && !$recursive) {
                    $patternFiles = [$pattern];
                }
                if (count($patternFiles) == 0) {
                    trigger_error("Requested file $pattern matched no files on disk", E_USER_WARNING);
                }
                $files = array_merge($files, $patternFiles);
            }
        }
        return $files;
    }

    public function getFileListForBundle($name) {
        return $this->getFileListInternal($this->config[$name]);
    }

    public function needsRecompile($name) {
        /**
         * @var \DateTime $saved
         */
        $saved = $this->assetStore->getSaveTime($name);
        if ($saved == null) {
            return true;
        }

        $files = $this->getFileListForBundle($name);
        $latestModified = max(array_map(function($x) { return filemtime($x); }, $files));
        return $latestModified > $saved->getTimestamp();
    }

    /**
     * @param Content[] $contents
     * @return Content
     * @throws InvalidConfigurationException
     */
    private function mergeContentsInternal(array $contents) {
        if (count($contents) == 0 || $contents == null) {
            return null;
        }
        $outputText = array_map(function(Content $c) { return $c->getContent(); }, $contents);
        $types = array_values(array_unique(array_map(function(Content $c) { return $c->getContentType(); }, $contents)));
        if (count($types) > 1) {
            throw new InvalidConfigurationException("Nodes have different content types, cannot merge.");
        }
        return new Content($types[0], implode('', $outputText));
    }

    private function getContentInternal($node) {
        if (!isset($node[Node::Transform])) {
            $transform = ["bundle"];
        }
        elseif (is_string($node[Node::Transform])) {
            $transform = [$node[Node::Transform]];
        }
        else {
            $transform = $node[Node::Transform];
        }

        $files = $this->getFileListInternal($node, false);
        $contents = [];
        // load contents of all files (and compile subgroups as necessary)
        foreach ($files as $file) {
            if (is_string($file)) {
                $contents[$file] = new Content("text/plain", file_get_contents($file));
            }
            else {
                $contents[sha1(microtime())] = $this->getContentInternal($file);
            }
        }

        // apply transforms to our files
        foreach ($transform as $transformName) {
            if ($transformName == "bundle") {
                $contents = [$this->mergeContentsInternal($contents)];
            }
            else {
                $transformer = $this->transformers[$transformName];
                foreach ($contents as $file => $content) {
                    $contents[$file] = $transformer->transform($content, $file);
                }
            }
        }

        return $this->mergeContentsInternal($contents);
    }

    public function compile($name) {
        if ($this->needsRecompile($name)) {
            $content = $this->getContentInternal($this->config[$name]);
            $this->assetStore->save($name, $content);
            return $content;
        }
        return $this->assetStore->get($name);
    }
} 