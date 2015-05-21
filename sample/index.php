<?php

require_once 'vendor/autoload.php';

$config =
    [
        "__webroot" => pathinfo(__FILE__, PATHINFO_DIRNAME),
        "site.css" => [
            "transform" => ["bundle"],
            "files" => [
                "app/styles/bootstrap.css",
                "app/styles/local.css"
            ]
        ]
    ];

$container = new \DC\IoC\Container();
$container
    ->register(function() {
        // by providing no parameters, store files in system temp folder
        return new \DC\Bundler\FileBasedCompiledAssetStore();
    })
    ->to('\DC\Bundler\ICompiledAssetStore');
$container
    ->register(
        /**
         * @param $transformers \DC\Bundler\ITransformer[]
         */
        function(\DC\Bundler\ICompiledAssetStore $assetStore, array $transformers) use ($config) {
            return new \DC\Bundler\Bundler(
                $config,
                \DC\Bundler\Mode::Debug,
                $assetStore,
                $transformers);
        })
    ->to('\DC\Bundler\Bundler');

\DC\Router\IoC\RouterSetup::route($container, ['\DC\Bundler\BundleController']);