<?php

declare(strict_types=1);

namespace Dmcz\FilterBlocks\Tests;

use Dmcz\FilterBlocks\Constraint;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ConstraintSyntaxTest extends TestCase
{
    #[DataProvider('provider')]
    public function testSyntax(string $expected, array $builders)
    {
        foreach ($builders as $index => $builder) {
            $filter = $builder();
            $this->assertSame($expected, $filter->explain(), "Case index = {$index}");
        }
    }

    public static function provider()
    {
        return [
            [
                'expected' => 'foo = "a"',
                'builders' => [
                    fn () => (new Constraint('foo'))->equal('a'),
                ],
            ],
        ];
    }
}
