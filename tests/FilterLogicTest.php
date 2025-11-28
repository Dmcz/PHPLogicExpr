<?php

declare(strict_types=1);

namespace Dmcz\LogicExpr\Tests;

use Dmcz\LogicExpr\Constraint;
use Dmcz\LogicExpr\Filter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class FilterLogicTest extends TestCase
{
    /**
     * @param array<callable():Filter> $builders
     */
    #[DataProvider('provider')]
    public function testSyntax(string $expected, array $builders)
    {
        foreach ($builders as $index => $builder) {
            [$filter, $state] = $builder();

            switch ($state) {
                case 'same':
                    $this->assertSame($expected, $filter->explain(), "Case index = {$index}");
                    break;
                case 'not same':
                    $this->assertNotSame($expected, $filter->explain(), "Case index = {$index}");
                    break;
            }
        }
    }

    public static function provider()
    {
        return [
            [
                'expected' => 'foo = "a"',
                'builders' => [
                    function () {
                        $filter = new Filter();
                        $filter->foo = 'a';
                        return [$filter, 'same'];
                    },
                    function () {
                        $filter = new Filter();
                        $filter->foo->equal('a');
                        return [$filter, 'same'];
                    },
                ],
            ],
            [
                'expected' => 'foo = "a" and bar = "b"',
                'builders' => [
                    function () {
                        $filter = new Filter();
                        $filter->foo = 'a';
                        $filter->bar = 'b';
                        return [$filter, 'same'];
                    },
                    function () {
                        $filter = new Filter();
                        $filter->foo->equal('a');
                        $filter->bar->equal('b');
                        return [$filter, 'same'];
                    },
                    function () {
                        $filter = new Filter();
                        $filter->foo->equal('a');
                        $filter->bar = 'b';
                        return [$filter, 'same'];
                    },
                    function () {
                        $filter = new Filter();
                        $filter->where('foo', 'a');
                        $filter->where('bar', 'b');
                        return [$filter, 'same'];
                    },
                    function () {
                        $filter = new Filter();
                        $filter->foo = 'a';
                        $filter->where('bar', 'b');
                        return [$filter, 'same'];
                    },
                    function () {
                        $filter = new Filter();
                        $filter->foo = 'a';
                        $filter->where(function (Filter $filter) {
                            $filter->bar = 'b';
                        });
                        return [$filter, 'same'];
                    },
                    // NOTE: 会优先解析约束，所以这里的输出为: 'bar = "b" and foo = "a"'
                    function () {
                        $filter = new Filter();
                        $filter->where('foo', 'a');
                        $filter->bar = 'b';
                        return [$filter, 'not same'];
                    },
                ],
            ],
            [
                'expected' => 'foo = "a" and bar = "b" and (baz > 0 or baz < 10)',
                'builders' => [
                    function () {
                        $filter = new Filter();
                        $filter->foo = 'a';
                        $filter->bar = 'b';
                        $filter->where(function (Filter $filter) {
                            $filter->baz->greaterThan(0)->orLessThan(10);
                        });
                        return [$filter, 'same'];
                    },
                ],
            ],
            [
                'expected' => 'foo = "a" and (bar = "b" or baz = "c")',
                'builders' => [
                    // 纯表达式
                    function () {
                        $filter = new Filter();
                        $filter->where('foo', 'a')->where(function (Filter $filter) {
                            $filter->where('bar', 'b')->orWhere('baz', 'c');
                        });
                        return [$filter, 'same'];
                    },
                    // 一个约束 一个表达式
                    function () {
                        $filter = new Filter();
                        $filter->foo = 'a';
                        $filter->where(function (Filter $filter) {
                            $filter->where('bar', 'b')->orWhere('baz', 'c');
                        });
                        return [$filter, 'same'];
                    },
                    // 一个约束 一个表达式
                    function () {
                        $filter = new Filter();
                        $filter->foo = 'a';
                        $filter->where('bar', 'b')->orWhere('baz', 'c');
                        return [$filter, 'same'];
                    },
                    // 一个约束 一个表达式
                    function () {
                        $filter = new Filter();
                        $filter->foo = 'a';
                        $filter->orWhere('bar', 'b')->orWhere('baz', 'c');
                        return [$filter, 'same'];
                    },
                    // NOTE: 由于约束和条件是且的关系，所以的orWhere会失效不会生成or，这里的的输出为: 'foo = "a" and (bar = "b" and baz = "c")'
                    function () {
                        $filter = new Filter();
                        $filter->foo = 'a';
                        $filter->where(function (Filter $filter) {
                            $filter->bar = 'b';
                            $filter->orWhere('baz', 'c');
                        });
                        return [$filter, 'not same'];
                    },
                ],
            ],
            [
                'expected' => 'foo in 1,2,3 and bar > 0 and bar < 10 and (baz = "c" or baz = "d") and qux >= 10 and qux <= 15',
                'builders' => [
                    // 全部是表达式
                    function () {
                        $filter = new Filter();
                        $filter->where('foo', 'in', [1, 2, 3])->where(function (Filter $filter) {
                            $filter->greaterThan('bar', 0)->lessThan('bar', 10);
                        })->where(function (Filter $filter) {
                            $filter->where('baz', 'c')->orWhere('baz', 'd');
                        })->where(function (Filter $filter) {
                            $filter->greaterEqual('qux', 10)->lessEqual('qux', 15);
                        });
                        return [$filter, 'same'];
                    },
                    // 全部是约束
                    function () {
                        $filter = new Filter();
                        $filter->foo = (new Constraint('foo'))->in([1, 2, 3]);
                        $filter->bar->greaterThan(0)->lessThan(10);
                        $filter->baz->equal('c')->orEqual('d');
                        $filter->qux->greaterEqual(10)->lessEqual(15);
                        return [$filter, 'same'];
                    },
                    // 两条约束一条表达式
                    function () {
                        $filter = new Filter();
                        $filter->foo = (new Constraint('foo'))->in([1, 2, 3]);
                        $filter->bar->greaterThan(0)->lessThan(10);
                        $filter->baz->equal('c')->orEqual('d');
                        $filter->where(function (Filter $filter) {
                            $filter->greaterEqual('qux', 10)->lessEqual('qux', 15);
                        });
                        return [$filter, 'same'];
                    },
                    // 一条约束两条表达式
                    function () {
                        $filter = new Filter();
                        $filter->foo = (new Constraint('foo'))->in([1, 2, 3]);
                        $filter->bar->greaterThan(0)->lessThan(10);
                        $filter->where(function (Filter $filter) {
                            $filter->where('baz', 'c')->orWhere('baz', 'd');
                        })->where(function (Filter $filter) {
                            $filter->greaterEqual('qux', 10)->lessEqual('qux', 15);
                        });
                        return [$filter, 'same'];
                    },
                ],
            ],
        ];
    }
}
