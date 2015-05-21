<?php

namespace DC\Tests\Bundler;

class FileBasedCompiledAssetStoreTest extends \PHPUnit_Framework_TestCase {
    public function testConstructor_missingFolder_usesTempFolderSubdirectory() {
        $expectedFolder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "dc_bundler";
        rmdir($expectedFolder);
        new \DC\Bundler\FileBasedCompiledAssetStore();
        $this->assertTrue(is_dir($expectedFolder));
    }

    public function testConstructor_save_storesAndRetrievesFile() {
        $folder = pathinfo(__FILE__, PATHINFO_DIRNAME) . '/assets/compiled';
        $content = new \DC\Bundler\Content("text/plain", "abc123");
        $store = new \DC\Bundler\FileBasedCompiledAssetStore($folder);
        $store->save('test', $content);
        $this->assertEquals(filemtime($folder . DIRECTORY_SEPARATOR . 'test'), $store->getSaveTime('test')->getTimestamp());
        $this->assertEquals('abc123', $store->get('test')->getContent());
        $this->assertEquals('text/plain', $store->get('test')->getContentType());
        unlink($folder . DIRECTORY_SEPARATOR . 'test');
    }
} 