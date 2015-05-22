<?php

namespace DC\Bundler;

class BundlerConfiguration {

    /**
     * @var array
     */
    private $bundles;
    /**
     * @var string
     */
    private $cacheBreaker;
    /**
     * @var int See \DC\Bundler\Mode
     */
    private $mode;

    function __construct($bundles, $mode = Mode::Debug, $cacheBreaker = "_")
    {
        if (is_array($bundles)) {
            $this->bundles = $bundles;
        }
        elseif (is_string($bundles) && file_exists($bundles)) {
            $json = file_get_contents($bundles);
            $this->bundles = json_decode($json, true);
        }
        $this->mode = $mode;
        $this->cacheBreaker = $cacheBreaker;

        if (!isset($this->bundles[Node::WebRoot])) {
            throw new \DC\Bundler\Exceptions\InvalidConfigurationException("Could not locate root folder. Add __webroot to configuration.");
        }
    }

    /**
     * @return array
     */
    public function getBundles()
    {
        return $this->bundles;
    }

    /**
     * @param array $bundles
     */
    public function setBundles($bundles)
    {
        $this->bundles = $bundles;
    }

    /**
     * @return string
     */
    public function getCacheBreaker()
    {
        return $this->cacheBreaker;
    }

    /**
     * @param string $cacheBreaker
     */
    public function setCacheBreaker($cacheBreaker)
    {
        $this->cacheBreaker = $cacheBreaker;
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param int $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }
}