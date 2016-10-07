# Asset bundler

This library enables you to bundle your assets into one for quicker loading.

## Installation

```
composer require dc/bundler
```

or in `composer.json`

```json
"require": {
    "dc/bundler": "master"
}
```

## Setup

This package depends on `dc/router` but also strongly suggests `dc/ioc`. The samples below assume both are installed.

```php
\DC\Bundler\Bundler::registerWithContainer(
    $container,
    new \DC\Bundler\BundlerConfiguration(
        $config, // see the configuration section
        \DC\Bundler\Mode::Production, 
        "bar")
    );
```

## Compiled asset store

This is an interface you can implement yourself to choose where the compiled files are stored. It is a very simple 
interface.

The built in implementation (`FileBasedCompiledAssetStore`) by default stores the files in the `/tmp/dc_bundler` on Linux,
and `C:\Windows\temp\dc_bundler` on Windows. When registering it, pass the folder you want to use to its constructor.

## Configuration

Bundler accepts either a PHP multi-dimensional array or a path to a JSON file on disk for its configuration. For speed
purposes, a PHP array is recommended and used throughout this documentation.

The configuration should have the following structure:

```php
[
    "__webroot" => "/var/www",
    "bundlename" => [
        "transform" => [
            // array of transforms to apply to these files in order
        ],
        "parts" => [
            // array of file names or other bundles to process
        ],
        "watch" => [
            // array of file names to watch for recompile
        ]
    ]
]
```

The **web root** is important because all paths in the configuration are relative to that root. This needs to be the
local path to your web root folder, so Bundler can find the files and serve up non-bundled versions. 

Besides the web root, the configuration consists of a range of names pointing to individual bundles. For a bundle to
be a bundle, you need at least the `parts` key. This specifies which files to include in the bundle, but you can also
write a new bundle here (see Nested bundles below).

When including files you specify a `glob` expression relative to the web root. Here are some samples:

- `app/app.js` &mdash; just the `app.js` file in the `app` directory.
- `app/directives/*.js` &mdash; all `.js` files in `app/directives`. You knew that already.
- `app/templates/**/*.html` &mdash; all `.html` files in any subfolder of `app/templates`. 

The **watch** specifies additional files to be watched for changes to determine if the bundle needs to be recompiled.
This is useful for e.g. Less files, where `@import` statements are not visible to Bundler. Then it would help if you
added a `watch` glob that matches all files in a specific subfolder. If any of those files change, the bundle is 
recompiled.

The **transforms** specify which transformations should be done on the items in this bundle. Each transform has a name, 
and all of these come as their own Composer package):

- `less` for transforming Less to CSS (see `dc/bundler-less`)
- `ngtpl` for caching Angular templates as Javascript (see `dc/bundler-ngtpl`) 
- `jsmin` for minifying Javascript (see `dc/bundler-jsmin`)

If you do not specify a transform, the default transform will be `["bundle"]`, which means it will simply concatenate
all of the parts together in order.

The special "bundle" transform is the only transform that acts on multiple items at once. This makes it possible to
decide when bundling happens &mdash; if you specify `["bundle", "jsmin"]` (which is the same as `["jsmin"]`), bundling 
happens before minification. Conversely, if you sepcify `["jsmin", "bundle"]`, bundling happens after minification
has been applied separately to each file.
 
### Nested bundles

Nested bundles are useful in some cases. A common case would be wanting to include the following items in your bundle:

- Third party libraries (already minified)
- Your site's Javascript code (written in Angular)
- Angular templates for your Javascript

Here is a configuration that could work well for you:

```php
[
    "__webroot" => "/var/www",
    "site.js" => [
        "parts" => [
            // do nothing with dependencies
            "app/dependencies/*.js", 
            [
                // minify everything you've written yourself
                "transform" => ["jsmin"],
                "parts" => [
                    "app/app.js",
                    "app/controllers/*.js",
                    "app/directives/*.js",
                    [
                        // convert Angular templates to JS
                        "transform => ["ngtpl"]
                        "parts" => ["app/templates/**/*.html"]
                    ]
                ]
            ]
        ]
    ]
]
```

## Automatic routing

If you ensure that `dc/router` knows about `'\DC\Bundler\BundleController`, your bundles will be available for use in
your app directly. The URL is `/bundle/{cacheBreaker}/{bundlename}`, so for the example above: `/bundle/foo/site.js`.

The `{cacheBreaker}` allows (forces?) you to easily skip each user's cache whenever you make changes. It is the 
recommended approach to use to ensure all of your user's get the latest assets.

## Including bundles in your HTML

`Bundler::getTagsForBundle` helps you by outputting all the HTML tags required for a given bundle. It automatically
keeps tracks of debug/production mode, and outputs the correct tags. It even handles the case were you have a Less
file that needs compilation even in debug mode.

If you are using a templating system such as Smarty, you may find the following snippet helpful:

```php
<?php
$smarty->registerPlugin(
    "function", 
    "bundle", 
    function(array $params) use (\DC\Bundler\Bundler $bundler) {
        return $bundler->getTagsForBundle($params['name']);
    });
```

Then you can easily use the bundles in your templates:

```
{bundle name="site.css"}
```

And it will result in the following HTML being produced in production:

```html
<link rel="stylesheet" type="text/css" src="/bundle/foo/site.css" />
```

And this is the result in debug:

```html
<link rel="stylesheet" type="text/css" src="/app/styles/bootstrap.css" />
<link rel="stylesheet" type="text/css" src="/bundle/foo/site.css?part=app/styles/app.less" />
```

Woohoo, magic!
