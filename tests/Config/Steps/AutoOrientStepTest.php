<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\Paperclip\Test\Config\Steps;

use Czim\Paperclip\Config\Steps\AutoOrientStep;
use Czim\Paperclip\Test\TestCase;

class AutoOrientStepTest extends TestCase
{

    /**
     * @test
     */
    function it_allows_static_fluent_syntax_to_array_build()
    {
        // Default
        $array = AutoOrientStep::make()->toArray();

        static::assertIsArray($array);
        static::assertEquals(
            [
                'auto-orient' => [
                    'quiet' => false,
                ],
            ], $array
        );

        // Specific
        $array = AutoOrientStep::make('testname')->quiet()->toArray();

        static::assertIsArray($array);
        static::assertEquals(
            [
                'testname' => [
                    'quiet' => true,
                ],
            ], $array
        );
    }

}
