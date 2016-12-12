<?php

namespace DC\Bundler;

/**
 * Bundle writer that by default writes bundles in a location for your webserver to find them.
 *
 * I.e. it uses the same path as \DC\Bundler\BundleController uses.
 */
class DefaultBundleWriter implements IBundleWriter {
    private $configuration;
    function __construct(BundlerConfiguration $configuration) {
        $this->configuration = $configuration;
    }

    function writeBundle($webRoot, $name, $content) {
        $dir = $webRoot . DIRECTORY_SEPARATOR . 'bundle' . DIRECTORY_SEPARATOR . $this->configuration->getCacheBreaker() . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dir . $name, $content);
    }
}