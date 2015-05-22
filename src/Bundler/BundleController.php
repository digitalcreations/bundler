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
     * @route /bundle/{cacheBreaker}/{name}?part={file}
     * @param string $name
     * @return \DC\Router\Response
     */
    public function get($name, $file) {
        $content = $this->bundler->compile($name);
        $isDebug = $this->bundler->getMode() == Mode::Debug;
        if (count($content) == 1 && !$isDebug) {
            $content = $content[0];
        }
        elseif ($isDebug && $file != null) {
            $file = $this->bundler->getWebroot() . DIRECTORY_SEPARATOR . $file;
            $content = $content[$file];
        }
        else {
            $response = new \DC\Router\Response();
            $response->setStatusCode(\DC\Router\StatusCodes::HTTP_NOT_FOUND);
            $response->setContent('Not supported');
            return $response;
        }
        header_remove('Expires');
        header_remove('Set-Cookie');
        header_remove('ETag');
        header_remove('Cache-Control');
        header_remove('Last-Modified');
        $response = new \DC\Router\Response();
        $response->setContent($content->getContent());
        $response->setContentType($content->getContentType());
        if (!$isDebug) {
            $response->setCustomHeader('Cache-Control', 'no-transform,public,max-age=31556926');
            $response->setCustomHeader('ETag', sha1($content->getContent()));
        }
        return $response;
    }
} 