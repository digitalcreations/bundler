<?php

namespace DC\Bundler;

class FileBasedCompiledAssetStore implements ICompiledAssetStore {

    /**
     * @var string
     */
    private $folder;

    /**
     * @param string $folder
     */
    function __construct($folder = null)
    {
        if ($folder == null) {
            $folder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "dc_bundler";
            if (!is_dir($folder)) {
                mkdir($folder, 0770, true);
            }
        }
        if (!is_dir($folder)) {
            throw new \InvalidArgumentException("$folder was not a valid folder");
        }
        $this->folder = rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    function save($name, Content $content)
    {
        file_put_contents($this->folder . $name, serialize($content));
    }

    function get($name)
    {
        return unserialize(file_get_contents($this->folder . $name));
    }

    /**
     * @param $name
     * @return \DateTime
     */
    function getSaveTime($name)
    {
        $dt = new \DateTime();
        $dt->setTimestamp(filemtime($this->folder . $name));
        return $dt;
    }
}