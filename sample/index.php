<?php

require_once 'vendor/autoload.php';

$config =
    [
        "__webroot" => pathinfo(__FILE__, PATHINFO_DIRNAME),
        "site.css" => [
            "transform" => ["bundle"],
            "parts" => [
                "app/styles/bootstrap.css",
                [
                    "transform" => ["less"],
                    "parts" => ["app/styles/local.less"]
                ]
            ]
        ],
        "site.js" => [
            "transform" => ["bundle"],
            "parts" => [
                "app/scripts/angular.min.js",
                [
                    "transform" => ["jsmin"],
                    "parts" => [
                        "app/scripts/app.js"
                    ]
                ]
            ]
        ]
    ];

$container = new \DC\IoC\Container();
$container
    ->register('\DC\Bundler\JSMin\JSMinTransformer')
    ->to('\DC\Bundler\ITransformer');
$container
    ->register('\DC\Bundler\Less\LessTransformer')
    ->to('\DC\Bundler\ITransformer');
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