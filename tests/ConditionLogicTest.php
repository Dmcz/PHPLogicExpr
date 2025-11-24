<?php

declare(strict_types=1);

namespace Dmcz\LogicExpr\Tests;

use Dmcz\LogicExpr\Condition;
use Dmcz\LogicExpr\Constraint;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ConditionLogicTest extends TestCase
{
    #[DataProvider('provider')]
    public function testLogic(string $expected, array $builders)
    {
        foreach ($builders as $index => $builder) {
            $condition = $builder();
            $this->assertSame($expected, $condition->explain(), "Case index = {$index}");
        }
    }

    public static function provider()
    {
        return [
            [
                'expected' => 'foo = "a" or foo = "b"',
                'builders' => [
                    fn () => (new Condition())->where('foo', '=', 'a')->orWhere('foo', '=', 'b'),
                    fn () => (new Condition())->where('foo', '=', 'a')->orEqual('foo', 'b'),
                    fn () => (new Condition())->equal('foo', 'a')->orEqual('foo', 'b'),
                    fn () => (new Constraint('foo'))->equal('a')->orEqual('b'),
                ],
            ],
            [
                'expected' => 'foo = "a" and bar = "b"',
                'builders' => [
                    fn () => (new Condition())->where('foo', '=', 'a')->where('bar', '=', 'b'),
                    fn () => (new Condition())->where('foo', 'a')->where('bar', 'b'),
                    fn () => (new Condition())->equal('foo', 'a')->equal('bar', 'b'),
                ],
            ],
            [
                'expected' => 'foo = "a" or bar = "b"',
                'builders' => [
                    fn () => (new Condition())->where('foo', '=', 'a')->orWhere('bar', '=', 'b'),
                    fn () => (new Condition())->where('foo', 'a')->orWhere('bar', 'b'),
                    fn () => (new Condition())->equal('foo', 'a')->orEqual('bar', 'b'),
                ],
            ],
            [
                'expected' => 'foo = "a" and (bar = "b" or baz = "c")',
                'builders' => [
                    fn () => (new Condition())->where('foo', '=', 'a')->where(fn (Condition $condition) => $condition->where('bar', 'b')->orWhere('baz', 'c')),
                ],
            ],
            [
                'expected' => '(foo = "a" or foo = "b") and (bar = "b" or baz = "c")',
                'builders' => [
                    fn () => (new Condition())->where(fn (Condition $condition) => $condition->where('foo', 'a')->orWhere('foo', 'b'))->where(fn (Condition $condition) => $condition->where('bar', 'b')->orWhere('baz', 'c')),
                ],
            ],
            [   
                // 多余的括号会被移除
                'expected' => 'foo = 1 or (foo = 2 and bar = 3) or (foo = 3 and bar = 4) or baz in 7,8,9',
                'builders' => [
                    function () {
                        return (new Condition())->where(function (Condition $condition) {
                            $condition->where('foo', 1)->orWhere(function (Condition $condition) {
                                $condition->where('foo', 2)->where('bar', 3);
                            });
                        })->orWhere(function (Condition $condition) {
                            $condition->where('foo', 3)->where('bar', 4);
                        })->orIn('baz', [7, 8, 9]);
                    },
                ],
            ],
        ];
    }
}
