<?php

use EnvFile\File;

class FileTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    function can_get_all_definitions_from_a_file()
    {
        $file = File::loadFrom(__DIR__ . '/../fixtures/basic.env');

        $definitions = $file->getAllDefinitions();

        $this->assertEquals([
            'FOO' => 'BAR',
            'BAR' => 'BAZ',
        ], $definitions );
    }

    /** @test */
    function only_key_value_lines_are_included_in_definitions()
    {
        $file = File::loadFrom(__DIR__ . '/../fixtures/basic-with-blank-lines.env');

        $definitions = $file->getAllDefinitions();

        $this->assertEquals([
            'FOO' => 'BAR',
            'BAR' => 'BAZ',
        ], $definitions );
    }

    /** @test */
    function throws_an_exception_when_loading_a_nonexistent_file()
    {
        $file = new File('non-existent-file');

        try {
            $file->load();
        } catch (\EnvFile\NonExistentFileException $exception) {
            $this->assertContains('non-existent-file', $exception->getMessage());
            return;
        }

        $this->fail();
    }

    /** @test */
    function can_add_new_definitions()
    {
        $file = File::loadFrom(__DIR__ . '/../fixtures/basic.env');

        $file->define('NEW_KEY', 'new-value');

        $this->assertArraySubset([
            'NEW_KEY' => 'new-value',
        ], $file->getAllDefinitions());
    }

    /** @test */
    function can_persist_changes_to_the_file()
    {
        $path = $this->copyFixture('basic.env');
        $file = File::loadFrom($path);
        $this->assertEquals([
            'FOO' => 'BAR',
            'BAR' => 'BAZ',
        ], $file->getAllDefinitions());

        $file->define('MAMMA', 'JAMMA');
        $file->save();
        $copy = File::loadFrom($path);

        $this->assertSame([
            'FOO' => 'BAR',
            'BAR' => 'BAZ',
            'MAMMA' => 'JAMMA',
        ], $copy->getAllDefinitions());
    }

    /** @test */
    function can_get_a_single_definition_from_the_file()
    {
        $file = File::loadFrom(__DIR__ . '/../fixtures/basic.env');

        $this->assertSame('BAR', $file->get('FOO'));
    }

    protected function copyFixture($path)
    {
        $temp_dir = '/tmp/env-file/' . time();
        $target_path = "$temp_dir/$path";

        mkdir($temp_dir, 0777, true);

        copy(__DIR__ . "/../fixtures/$path", $target_path);

        return $target_path;
    }
}
