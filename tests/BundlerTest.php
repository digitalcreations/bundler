<?php

namespace DC\Tests\Bundler;

class BundlerTest extends \PHPUnit_Framework_TestCase {
    protected function setUp()
    {
        $this->root = pathinfo(__FILE__, PATHINFO_DIRNAME) . '/assets';
        parent::setUp();
    }


    private $root;

    private function getTestBundles() {
        return [
            '__webroot' => $this->root,
            'missing' => [
                'transform' => ['bundle'],
                'parts' => [
                    'foo/x.js'
                ]
            ],
            'completeGlob' => [
                'parts' => [
                    'js/*.js'
                ]
            ],
            'withTransform' => [
                'transform' => ['rot13', 'bundle'],
                'parts' => [
                    'js/test1.js'
                ]
            ],
            'withNestedTransform' => [
                'parts' => [
                    'js/test1.js',
                    [
                        'transform' => ['rot13'],
                        'files' => ['js/test2.js']
                    ]
                ]
            ]
        ];
    }

    /**
     * @expectedException \Exception
     */
    public function testGetFileListForBundle_missingFileTriggersWarning() {
        $bundler = new \DC\Bundler\Bundler($this->getTestBundles());
        $bundler->getFileListForBundle('missing');
    }

    public function testGetFileListForBundle_ReturnsCorrectList() {
        $bundler = new \DC\Bundler\Bundler($this->getTestBundles());
        $files = $bundler->getFileListForBundle('completeGlob');
        $this->assertContains($this->root . "/js/test1.js", $files);
        $this->assertContains($this->root . "/js/test2.js", $files);
    }

    public function testGetFileListForBundle_nestedBundle_returnsCorrectList() {
        $bundler = new \DC\Bundler\Bundler($this->getTestBundles());
        $files = $bundler->getFileListForBundle('withNestedTransform');
        $this->assertContains($this->root . "/js/test1.js", $files);
        $this->assertContains($this->root . "/js/test2.js", $files);
    }

    public function testNeedsRecompile_notCompiled_returnsTrue() {
        $mockAssetStore = $this->getMock('\DC\Bundler\ICompiledAssetStore');
        $mockAssetStore
            ->expects($this->once())
            ->method('getSaveTime')
            ->willReturn(null);
        $bundler = new \DC\Bundler\Bundler($this->getTestBundles(), \DC\Bundler\Mode::Debug, $mockAssetStore);

        $this->assertTrue($bundler->needsRecompile('completeGlob'));
    }

    public function testNeedsRecompile_oldCompiled_returnsTrue() {
        $mockAssetStore = $this->getMock('\DC\Bundler\ICompiledAssetStore');
        $mockAssetStore
            ->expects($this->once())
            ->method('getSaveTime')
            ->willReturn(new \DateTime('2015-01-01'));
        $bundler = new \DC\Bundler\Bundler($this->getTestBundles(), \DC\Bundler\Mode::Debug, $mockAssetStore);

        $this->assertTrue($bundler->needsRecompile('completeGlob'));
    }

    public function testNeedsRecompile_newCompiled_returnsFalse() {
        $mockAssetStore = $this->getMock('\DC\Bundler\ICompiledAssetStore');
        $mockAssetStore
            ->expects($this->once())
            ->method('getSaveTime')
            ->willReturn(new \DateTime());
        $bundler = new \DC\Bundler\Bundler($this->getTestBundles(), \DC\Bundler\Mode::Debug, $mockAssetStore);

        $this->assertFalse($bundler->needsRecompile('completeGlob'));
    }

    public function testGetContent_needsRecompile_isBundledAndReturned() {
        $mockAssetStore = $this->getMock('\DC\Bundler\ICompiledAssetStore');
        $mockAssetStore
            ->expects($this->once())
            ->method('getSaveTime')
            ->willReturn(new \DateTime('2015-01-01'));

        $bundler = new \DC\Bundler\Bundler($this->getTestBundles(), \DC\Bundler\Mode::Debug, $mockAssetStore, []);
        $content = $bundler->compile('completeGlob');
        $this->assertEquals("console.log('test1.js');console.log('test2.js');", $content->getContent());
    }

    public function testGetContent_needsRecompile_isCompiledBundledAndReturned() {
        $mockAssetStore = $this->getMock('\DC\Bundler\ICompiledAssetStore');
        $mockAssetStore
            ->expects($this->once())
            ->method('getSaveTime')
            ->willReturn(new \DateTime('2015-01-01'));

        $bundler = new \DC\Bundler\Bundler($this->getTestBundles(), \DC\Bundler\Mode::Debug, $mockAssetStore, [new Rot13Transformer()]);
        $content = $bundler->compile('withTransform');
        $this->assertEquals("pbafbyr.ybt('grfg1.wf');", $content->getContent());
    }

    public function testGetContent_nestedContent_isCompiledBundledAndReturned() {
        $mockAssetStore = $this->getMock('\DC\Bundler\ICompiledAssetStore');
        $mockAssetStore
            ->expects($this->once())
            ->method('getSaveTime')
            ->willReturn(new \DateTime('2015-01-01'));

        $bundler = new \DC\Bundler\Bundler($this->getTestBundles(), \DC\Bundler\Mode::Debug, $mockAssetStore, [new Rot13Transformer()]);
        $content = $bundler->compile('withNestedTransform');
        $this->assertEquals("console.log('test1.js');pbafbyr.ybt('grfg2.wf');", $content->getContent());
    }
}