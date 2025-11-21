<?php

declare(strict_types=1);

namespace Dmcz\FilterBlocks\Tests;

use Dmcz\FilterBlocks\Condition;
use Dmcz\FilterBlocks\Operator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ConditionSyntaxTest extends TestCase
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
                    fn () => (new Condition())->where('foo', 'a'),
                    fn () => (new Condition())->where('foo', '=', 'a'),
                    fn () => (new Condition())->where('foo', Operator::EQ, 'a'),
                    fn () => (new Condition())->equal('foo', 'a'),
                ],
            ],
            [
                'expected' => 'foo != "a"',
                'builders' => [
                    fn () => (new Condition())->where('foo', '<>', 'a'),
                    fn () => (new Condition())->where('foo', '!=', 'a'),
                    fn () => (new Condition())->where('foo', Operator::NEQ, 'a'),
                    fn () => (new Condition())->notEqual('foo', 'a'),
                ],
            ],
            [
                'expected' => 'a > 1',
                'builders' => [
                    fn () => (new Condition())->where('a', '>', 1),
                    fn () => (new Condition())->where('a', Operator::GT, 1),
                    fn () => (new Condition())->greaterThan('a', 1),
                ],
            ],
            [
                'expected' => 'a >= 1',
                'builders' => [
                    fn () => (new Condition())->where('a', '>=', 1),
                    fn () => (new Condition())->where('a', Operator::GTE, 1),
                    fn () => (new Condition())->greaterEqual('a', 1),
                ],
            ],
            [
                'expected' => 'a < 1',
                'builders' => [
                    fn () => (new Condition())->where('a', '<', 1),
                    fn () => (new Condition())->where('a', Operator::LT, 1),
                    fn () => (new Condition())->lessThan('a', 1),
                ],
            ],
            [
                'expected' => 'a <= 1',
                'builders' => [
                    fn () => (new Condition())->where('a', '<=', 1),
                    fn () => (new Condition())->where('a', Operator::LTE, 1),
                    fn () => (new Condition())->lessEqual('a', 1),
                ],
            ],
            [
                'expected' => 'a is null',
                'builders' => [
                    fn () => (new Condition())->whereNull('a'),
                    fn () => (new Condition())->isNull('a'),
                    fn () => (new Condition())->where('a', Operator::IS_NULL),
                ],
            ],
            [
                'expected' => 'a not null',
                'builders' => [
                    fn () => (new Condition())->whereNotNull('a'),
                    fn () => (new Condition())->notNull('a'),
                    fn () => (new Condition())->where('a', Operator::NOT_NULL),
                ],
            ],
            [
                'expected' => 'a in 1,2,3',
                'builders' => [
                    fn () => (new Condition())->where('a', 'in', [1, 2, 3]),
                    fn () => (new Condition())->whereIn('a', [1, 2, 3]),
                    fn () => (new Condition())->in('a', [1, 2, 3]),
                    fn () => (new Condition())->where('a', Operator::IN, [1, 2, 3]),
                ],
            ],
            [
                'expected' => 'a not in 1,2,3',
                'builders' => [
                    fn () => (new Condition())->where('a', 'not in', [1, 2, 3]),
                    fn () => (new Condition())->whereNotIn('a', [1, 2, 3]),
                    fn () => (new Condition())->notIn('a', [1, 2, 3]),
                    fn () => (new Condition())->where('a', Operator::NOT_IN, [1, 2, 3]),
                ],
            ],
            [
                'expected' => 'foo contain "a"',
                'builders' => [
                    fn () => (new Condition())->where('foo', 'contain', 'a'),
                    fn () => (new Condition())->contain('foo', 'a'),
                    fn () => (new Condition())->whereContain('foo', 'a'),
                    fn () => (new Condition())->where('foo', Operator::CONTAIN, 'a'),
                ],
            ],
            [
                'expected' => 'foo start with "a"',
                'builders' => [
                    fn () => (new Condition())->where('foo', 'start with', 'a'),
                    fn () => (new Condition())->startWith('foo', 'a'),
                    fn () => (new Condition())->whereStartWith('foo', 'a'),
                    fn () => (new Condition())->where('foo', Operator::START_WITH, 'a'),
                ],
            ],
            [
                'expected' => 'foo end with "a"',
                'builders' => [
                    fn () => (new Condition())->where('foo', 'end with', 'a'),
                    fn () => (new Condition())->endWith('foo', 'a'),
                    fn () => (new Condition())->whereEndWith('foo', 'a'),
                    fn () => (new Condition())->where('foo', Operator::END_WITH, 'a'),
                ],
            ],
            [
                'expected' => 'foo = "a" or foo = "b"',
                'builders' => [
                    fn () => (new Condition())->orWhere('foo', 'a')->orWhere('foo', 'b'),
                    fn () => (new Condition())->orWhere('foo', '=', 'a')->orWhere('foo', '=', 'b'),
                    fn () => (new Condition())->orEqual('foo', 'a')->orEqual('foo', 'b'),
                    fn () => (new Condition())->orWhere('foo', Operator::EQ, 'a')->orWhere('foo', Operator::EQ, 'b'),
                ],
            ],
            [
                'expected' => 'foo != "a" or foo != "b"',
                'builders' => [
                    fn () => (new Condition())->orNotEqual('foo', 'a')->orNotEqual('foo', 'b'),
                    fn () => (new Condition())->orWhere('foo', '!=', 'a')->orWhere('foo', '!=', 'b'),
                    fn () => (new Condition())->orWhere('foo', '<>', 'a')->orWhere('foo', '<>', 'b'),
                    fn () => (new Condition())->orWhere('foo', Operator::NEQ, 'a')->orWhere('foo', Operator::NEQ, 'b'),
                ],
            ],
            [
                'expected' => 'a < 1 or a < 0',
                'builders' => [
                    fn () => (new Condition())->orLessThan('a', 1)->orLessThan('a', 0),
                    fn () => (new Condition())->orWhere('a', '<', 1)->orWhere('a', '<', 0),
                    fn () => (new Condition())->orWhere('a', Operator::LT, 1)->orWhere('a', Operator::LT, 0),
                ],
            ],
            [
                'expected' => 'a not in 1,2 or a not in 3,4',
                'builders' => [
                    fn () => (new Condition())->orWhereNotIn('a', [1, 2])->orWhereNotIn('a', [3, 4]),
                    fn () => (new Condition())->orNotIn('a', [1, 2])->orNotIn('a', [3, 4]),
                    fn () => (new Condition())->orWhere('a', 'not in', [1, 2])->orWhere('a', 'not in', [3, 4]),
                    fn () => (new Condition())->orWhere('a', Operator::NOT_IN, [1, 2])->orWhere('a', Operator::NOT_IN, [3, 4]),
                ],
            ],
            [
                'expected' => 'a > 1 or a > 0',
                'builders' => [
                    fn () => (new Condition())->orGreaterThan('a', 1)->orGreaterThan('a', 0),
                    fn () => (new Condition())->orWhere('a', '>', 1)->orWhere('a', '>', 0),
                    fn () => (new Condition())->orWhere('a', Operator::GT, 1)->orWhere('a', Operator::GT, 0),
                ],
            ],
            [
                'expected' => 'a >= 1 or a >= 0',
                'builders' => [
                    fn () => (new Condition())->orGreaterEqual('a', 1)->orGreaterEqual('a', 0),
                    fn () => (new Condition())->orWhere('a', '>=', 1)->orWhere('a', '>=', 0),
                    fn () => (new Condition())->orWhere('a', Operator::GTE, 1)->orWhere('a', Operator::GTE, 0),
                ],
            ],
            [
                'expected' => 'a <= 1 or a <= 0',
                'builders' => [
                    fn () => (new Condition())->orLessEqual('a', 1)->orLessEqual('a', 0),
                    fn () => (new Condition())->orWhere('a', '<=', 1)->orWhere('a', '<=', 0),
                    fn () => (new Condition())->orWhere('a', Operator::LTE, 1)->orWhere('a', Operator::LTE, 0),
                ],
            ],
            [
                'expected' => 'a is null or b is null',
                'builders' => [
                    fn () => (new Condition())->orWhereNull('a')->orWhereNull('b'),
                    fn () => (new Condition())->orIsNull('a')->orIsNull('b'),
                    fn () => (new Condition())->orWhere('a', Operator::IS_NULL)->orWhere('b', Operator::IS_NULL),
                ],
            ],
            [
                'expected' => 'a not null or b not null',
                'builders' => [
                    fn () => (new Condition())->orWhereNotNull('a')->orWhereNotNull('b'),
                    fn () => (new Condition())->orNotNull('a')->orNotNull('b'),
                    fn () => (new Condition())->orWhere('a', Operator::NOT_NULL)->orWhere('b', Operator::NOT_NULL),
                ],
            ],
            [
                'expected' => 'a in 1,2 or a in 3,4',
                'builders' => [
                    fn () => (new Condition())->orWhereIn('a', [1, 2])->orWhereIn('a', [3, 4]),
                    fn () => (new Condition())->orIn('a', [1, 2])->orIn('a', [3, 4]),
                    fn () => (new Condition())->orWhere('a', 'in', [1, 2])->orWhere('a', 'in', [3, 4]),
                    fn () => (new Condition())->orWhere('a', Operator::IN, [1, 2])->orWhere('a', Operator::IN, [3, 4]),
                ],
            ],
            [
                'expected' => 'foo contain "a" or foo contain "b"',
                'builders' => [
                    fn () => (new Condition())->orContain('foo', 'a')->orContain('foo', 'b'),
                    fn () => (new Condition())->orWhereContain('foo', 'a')->orWhereContain('foo', 'b'),
                    fn () => (new Condition())->orWhere('foo', 'contain', 'a')->orWhere('foo', 'contain', 'b'),
                    fn () => (new Condition())->orWhere('foo', Operator::CONTAIN, 'a')->orWhere('foo', Operator::CONTAIN, 'b'),
                ],
            ],
            [
                'expected' => 'foo start with "a" or foo start with "b"',
                'builders' => [
                    fn () => (new Condition())->orStartWith('foo', 'a')->orStartWith('foo', 'b'),
                    fn () => (new Condition())->orWhereStartWith('foo', 'a')->orWhereStartWith('foo', 'b'),
                    fn () => (new Condition())->orWhere('foo', 'start with', 'a')->orWhere('foo', 'start with', 'b'),
                    fn () => (new Condition())->orWhere('foo', Operator::START_WITH, 'a')->orWhere('foo', Operator::START_WITH, 'b'),
                ],
            ],
            [
                'expected' => 'foo end with "a" or foo end with "b"',
                'builders' => [
                    fn () => (new Condition())->orEndWith('foo', 'a')->orEndWith('foo', 'b'),
                    fn () => (new Condition())->orWhereEndWith('foo', 'a')->orWhereEndWith('foo', 'b'),
                    fn () => (new Condition())->orWhere('foo', 'end with', 'a')->orWhere('foo', 'end with', 'b'),
                    fn () => (new Condition())->orWhere('foo', Operator::END_WITH, 'a')->orWhere('foo', Operator::END_WITH, 'b'),
                ],
            ],
        ];
    }
}
