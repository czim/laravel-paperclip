<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\Paperclip\Test\Config;

use Czim\Paperclip\Config\Variant;
use Czim\Paperclip\Test\TestCase;

class VariantTest extends TestCase
{

    /**
     * @test
     */
    function it_takes_fluent_information()
    {
        $config = Variant::make('thumb')
            ->steps(['auto-orient' => [],])
            ->extension('txt');

        static::assertEquals('thumb', $config->getName());
        static::assertEquals(['auto-orient' => []], $config->getSteps());
        static::assertEquals('txt', $config->getExtension());
    }

    /**
     * @test
     */
    function it_takes_non_array_steps_data()
    {
        $config = Variant::make('thumb')->steps('auto-orient');

        static::assertEquals(['auto-orient'], $config->getSteps());
    }

    /**
     * @test
     */
    function it_takes_an_extension_and_strips_the_starting_period()
    {
        $config = Variant::make('test')->extension('.txt');

        static::assertEquals('txt', $config->getExtension());
    }

    /**
     * @test
     */
    function it_returns_null_for_extension_by_default()
    {
        $config = Variant::make('test');

        static::assertNull($config->getExtension());
    }

    /**
     * @test
     */
    function it_takes_and_returns_null_for_extension()
    {
        $config = Variant::make('test')->extension(null);

        static::assertNull($config->getExtension());
    }

    /**
     * @test
     */
    function it_takes_a_url()
    {
        $config = Variant::make('test')->url('http://www.doesnotexist.com');

        static::assertEquals('http://www.doesnotexist.com', $config->getUrl());
    }

    /**
     * @test
     */
    function it_returns_null_for_url_by_default()
    {
        $config = Variant::make('test');

        static::assertNull($config->getUrl());
    }

}
