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


\DC\Bundler\Less\LessTransformer::registerWithContainer($container);
\DC\Bundler\JSMin\JSMinTransformer::registerWithContainer($container);
\DC\Bundler\Bundler::registerWithContainer($container,
    new \DC\Bundler\BundlerConfiguration($config, \DC\Bundler\Mode::Production, "bar"));

\DC\Router\IoC\RouterSetup::route($container, ['\DC\Bundler\BundleController']);

/*
 * Try hitting http://bundler.local/bundle/whatever/site.css
 *
 * Then switch to Debug mode above, and try hitting
 * http://bundler.local/bundle/whatever/site.css?part=app/styles/local.less
 */