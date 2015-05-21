<?php

namespace DC\Bundler;

class BundleController extends \DC\Router\ControllerBase {
    /**
     * @var Bundler
     */
    private $bundler;

    function __construct(Bundler $bundler)
    {
        $this->bundler = $bundler;
    }

    /**
     * @route /bundle/{cacheBreaker}/{name}
     * @param string $name
     * @return \DC\Router\Response
     */
    public function get($name) {
        $content = $this->bundler->compile($name);
        header_remove('Expires');
        header_remove('Set-Cookie');
        header_remove('ETag');
        header_remove('Cache-Control');
        header_remove('Last-Modified');
        $response = new \DC\Router\Response();
        $response->setContent($content->getContent());
        $response->setContentType($content->getContentType());
        $response->setCustomHeader('Cache-Control', 'no-transform,public,max-age=31556926');
        $response->setCustomHeader('ETag', sha1($content->getContent()));
        return $response;
    }
} 