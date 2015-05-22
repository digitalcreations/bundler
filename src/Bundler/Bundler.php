<?php

namespace DC\Bundler;

use DC\Bundler\Exceptions\InvalidConfigurationException;

class Bundler {
    /**
     * @var BundlerConfiguration
     */
    private $config;
    /**
     * @var ICompiledAssetStore
     */
    private $assetStore;
    /**
     * @var array|ITransformer[]
     */
    private $transformers;
    /**
     * @var array|ITagWriter[]
     */
    private $tagWriters;

    /**
     * @param $config \DC\Bundler\BundlerConfiguration The configuration
     * @param $assetStore \DC\Bundler\ICompiledAssetStore
     * @param $transformers \DC\Bundler\ITransformer[]
     * @param $tagWriters \DC\Bundler\ITagWriter[]
     * @throws Exceptions\InvalidConfigurationException
     */
    function __construct(BundlerConfiguration $config,
                         ICompiledAssetStore $assetStore = null,
                         array $transformers = null,
                         array $tagWriters = null)
    {
        $this->config = $config;

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

        $this->tagWriters = [];
        if (!is_array($tagWriters)) {
            $tagWriters = [new JavascriptTagWriter(), new StylesheetTagWriter()];
        }
        array_walk($tagWriters, function(ITagWriter $tagWriter) {
            $types = $tagWriter->getSupportedContentTypes();
            array_walk($types, function($type) use ($tagWriter) {
                $this->tagWriters[$type] = $tagWriter;
            });
        });
    }

    public function getMode() {
        return $this->config->getMode();
    }

    public function getWebroot() {
        return $this->config->getBundles()[Node::WebRoot];
    }

    private function getFileListInternal($bundle, $recursive = true, $includeWatch = false) {
        $files = [];
        if (is_array($bundle[Node::Parts])) {
            foreach ($bundle[Node::Parts] as $pattern) {
                if (is_string($pattern)) {
                    $patternFiles = glob($this->getWebroot() .'/'. $pattern);
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

        if ($includeWatch && isset($bundle[Node::Watch]) && is_array($bundle[Node::Watch])) {
            foreach ($bundle[Node::Watch] as $pattern) {
                $files = array_merge($files, glob($this->getWebroot() .'/'. $pattern));
            }
        }

        if ($recursive) return array_unique($files);
        return $files;
    }

    public function getFileListForBundle($name) {
        return $this->getFileListInternal($this->config->getBundles()[$name]);
    }

    private function getFileListForWatch($name) {
        return $this->getFileListInternal($this->config->getBundles()[$name], true, true);
    }

    private function getSaveName($name) {
        return $this->config->getMode() . $name;
    }

    public function needsRecompile($name) {
        /**
         * @var \DateTime $saved
         */
        $saved = $this->assetStore->getSaveTime($this->getSaveName($name));
        if ($saved == null) {
            return true;
        }

        $files = $this->getFileListForWatch($name);
        $latestModified = max(array_map(function($x) { return filemtime($x); }, $files));
        return $latestModified > $saved->getTimestamp();
    }

    private static function flatten(array $array) {
        $result = array();
        foreach($array as $key=>$value) {
            if(is_array($value)) {
                $result = $result + self::flatten($value);
            }
            else {
                $result[$key] = $value;
            }
        }
        return $result;
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
        $flatContents = self::flatten($contents);
        $outputText = array_map(function(Content $c) { return $c->getContent(); }, $flatContents);
        $types = array_values(array_unique(array_map(function(Content $c) { return $c->getContentType(); }, $flatContents)));
        if (count($types) !== 1) {
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
        $isDebug = $this->getMode() == Mode::Debug;

        $files = $this->getFileListInternal($node, false);
        $contents = [];
        // load contents of all files (and compile subgroups as necessary)
        foreach ($files as $file) {
            if (is_string($file)) {
                $mimeRepos = new \Dflydev\ApacheMimeTypes\FlatRepository();
                $contents[$file] = new Content($mimeRepos->findType(pathinfo($file, PATHINFO_EXTENSION)), file_get_contents($file), false);
            }
            else {
                $contents[sha1(microtime())] = $this->getContentInternal($file);
            }
        }

        // apply transforms to our files
        foreach ($transform as $transformName) {
            if (!$isDebug && $transformName == "bundle") {
                $contents = [$this->mergeContentsInternal($contents)];
            }
            elseif (isset($this->transformers[$transformName])) {
                $transformer = $this->transformers[$transformName];
                if ($isDebug && !$transformer->runInDebugMode()) {
                    continue;
                }
                foreach ($contents as $file => $content) {
                    $contents[$file] = $transformer->transform($content, $file);
                }
            }
        }

        return $isDebug ? $contents : [$this->mergeContentsInternal($contents)];
    }

    public function getTagsForBundle($name) {
        $content = $this->compile($name);
        $tags = [];
        if ($this->getMode() == Mode::Debug) {
            foreach ($content as $path => $c) {
                $path = str_replace($this->getWebroot(), '', $path);
                if ($c->wasCompiled()) {
                    $path = '/bundle/' . $this->config->getCacheBreaker() . '/' . $name .'?part=' . htmlentities($path);
                }
                $tagWriter = $this->tagWriters[$c->getContentType()];
                $tags[] = $tagWriter->getTagForPath($path);
            }
        }
        else {
            $path = '/bundle/' . $this->config->getCacheBreaker() . '/' . $name;
            $tagWriter = $this->tagWriters[$content[0]->getContentType()];
            return $tagWriter->getTagForPath($path);
        }
        return implode("\n", $tags);
    }

    /**
     * @param $name
     * @return Content[]
     */
    public function compile($name) {
        if ($this->needsRecompile($name)) {
            $content = self::flatten($this->getContentInternal($this->config->getBundles()[$name]));
            $this->assetStore->save($this->getSaveName($name), $content);
            return $content;
        }
        return $this->assetStore->get($this->getSaveName($name));
    }

    public static function registerWithContainer(\DC\IoC\Container $container, BundlerConfiguration $config) {
        $container->register(function() {
                // by providing no parameters, store files in system temp folder
                return new \DC\Bundler\FileBasedCompiledAssetStore();
            })
            ->to('\DC\Bundler\ICompiledAssetStore');
        $container->register('\DC\Bundler\JavascriptTagWriter')->to('\DC\Bundler\ITagWriter');
        $container->register('\DC\Bundler\StylesheetTagWriter')->to('\DC\Bundler\ITagWriter');
        $container->register($config);
    }
} 