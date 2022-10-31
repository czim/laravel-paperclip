<?php

declare(strict_types=1);

namespace Czim\Paperclip\Test\Config\Steps;

use BadMethodCallException;
use Czim\Paperclip\Config\Steps\ResizeStep;
use Czim\Paperclip\Test\TestCase;

class ResizeStepTest extends TestCase
{
    /**
     * @test
     */
    public function it_allows_static_fluent_syntax_to_array_build(): void
    {
        // Minimal
        $array = ResizeStep::make()->width(100)->toArray();

        static::assertIsArray($array);
        static::assertEquals(
            [
                'resize' => [
                    'dimensions'     => '100x',
                    'convertOptions' => [],
                ],
            ], $array
        );

        $array = ResizeStep::make()->height(100)->toArray();

        static::assertIsArray($array);
        static::assertEquals(
            [
                'resize' => [
                    'dimensions'     => 'x100',
                    'convertOptions' => [],
                ],
            ], $array
        );

        // Specific with ignore
        $array = ResizeStep::make('testname')->width(100)->height(150)
            ->ignoreRatio()
            ->toArray();

        static::assertEquals(
            [
                'testname' => [
                    'dimensions'     => '100x150!',
                    'convertOptions' => [],
                ],
            ], $array
        );

        // Specific with crop
        $array = ResizeStep::make('testname')->width(150)->height(100)
            ->crop()
            ->toArray();

        static::assertEquals(
            [
                'testname' => [
                    'dimensions'     => '150x100#',
                    'convertOptions' => [],
                ],
            ], $array
        );
    }

    /**
     * @test
     */
    public function it_takes_width_and_height_using_square_method(): void
    {
        $array = ResizeStep::make()->square(100)->toArray();

        static::assertEquals(
            [
                'resize' => [
                    'dimensions'     => '100x100',
                    'convertOptions' => [],
                ],
            ], $array
        );
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_neither_width_nor_height_are_set(): void
    {
        $this->expectException(BadMethodCallException::class);

        ResizeStep::make()->toArray();
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_width_and_height_are_not_both_set_when_using_crop(): void
    {
        $this->expectException(BadMethodCallException::class);

        ResizeStep::make()->width(100)->crop()->toArray();
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_width_and_height_are_not_both_set_when_using_ignore_ratio(): void
    {
        $this->expectException(BadMethodCallException::class);

        ResizeStep::make()->height(100)->ignoreRatio()->toArray();
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_corp_and_ignore_ratio_are_both_set(): void
    {
        $this->expectException(BadMethodCallException::class);

        ResizeStep::make()->height(100)->width(150)->crop()->ignoreRatio()->toArray();
    }
}
