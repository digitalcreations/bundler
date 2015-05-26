<?php

namespace DC\Tests\Bundler\Transformers;

class BundleTransformerTest extends \PHPUnit_Framework_TestCase {
    function testTransformMultiple_keepsContentTypeOfInput() {
        $transformer = new \DC\Bundler\Transformers\BundleTransformer();
        $result = $transformer->transformMultiple([
            new \DC\Bundler\Content("text/plain", "abc123")
        ]);

        $this->assertEquals("text/plain", $result->getContentType());
    }

    function testTransformMultiple_multipleInput_concatenatedOutput() {
        $transformer = new \DC\Bundler\Transformers\BundleTransformer();
        $result = $transformer->transformMultiple([
            new \DC\Bundler\Content("text/plain", "abc"),
            new \DC\Bundler\Content("text/plain", "123")
        ]);

        $this->assertEquals("abc123", $result->getContent());
    }

    /**
     * @expectedException \DC\Bundler\Exceptions\InvalidConfigurationException
     */
    function testTransformMultiple_varyingContentTypes_throws()
    {
        $transformer = new \DC\Bundler\Transformers\BundleTransformer();
        $transformer->transformMultiple([
            new \DC\Bundler\Content("text/pain", "abc"),
            new \DC\Bundler\Content("text/gain", "123")
        ]);
    }

    /**
     * @expectedException \Exception
     */
    function testTransform_throws() {
        $transformer = new \DC\Bundler\Transformers\BundleTransformer();
        $transformer->transform(new \DC\Bundler\Content("text/plain", "abc"));
    }
} 