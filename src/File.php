<?php

namespace EnvFile;

class File
{
    protected $path;
    protected $lines;

    /**
     * File constructor.
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @param $path
     *
     * @return static
     */
    public static function loadFrom($path)
    {
        $file = new static($path);
        $file->load();

        return $file;
    }

    public function load()
    {
        if (! file_exists($this->path)) {
            throw new NonExistentFileException("No file exists at $this->path");
        }

        $this->lines = file($this->path, FILE_IGNORE_NEW_LINES);
    }

    public function get($key)
    {
        return $this->getAllDefinitions()[$key];
    }

    public function getAllDefinitions()
    {
        $definitions = [];

        foreach ($this->lines as $line) {
            if (false === strpos($line, '=')) {
                continue;
            }
            list($key, $value) = explode('=', $line, 2);
            $definitions[$key] = $value;
        }

        return $definitions;
    }

    public function define($key, $value)
    {
        $this->lines[] = "$key=$value";
    }

    public function save()
    {
        file_put_contents($this->path, join("\n", $this->lines));
    }
}
