<?php

declare(strict_types=1);

namespace Dmcz\LogicExpr\Tests;

use Dmcz\LogicExpr\Compilers\EloquentLikeQueryCompiler;
use Dmcz\LogicExpr\Condition;
use Dmcz\LogicExpr\Constraint;
use Dmcz\LogicExpr\Filter;
use Dmcz\LogicExpr\Logic;
use Hyperf\Database\Connection as HyperfConnection;
use Hyperf\Database\Query\Builder as HyperfBuilder;
use Hyperf\Database\Query\Grammars\MySqlGrammar as HyperfMysqlGrammar;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class EloquentLikeMysqlQueryCompilerTest extends TestCase
{
    #[DataProvider('provider')]
    public function testLogic(string $exceptWhere, array $exceptBinds, array $drivers, array $builders)
    {
        foreach ($builders as $index => $builder) {
            $compiler = new EloquentLikeQueryCompiler();
            $expression = $builder();

            foreach ($drivers as $driver) {
                $query = $this->makeQueryBuilder($driver);
                $compiler->compile($expression, $query);

                $binds = $query->getBindings();
                $this->assertSame($exceptBinds, $binds, "Case index = {$index}, Driver = {$driver}, Sql = {$query->toSql()}");

                $grammar = $this->makeGrammar($driver);

                $selectSql = $grammar->compileSelect($query);
                if ($exceptWhere !== '') {
                    $this->assertSame("select * where {$exceptWhere}", $selectSql, "Case index = {$index}, Driver = {$driver}");
                } else {
                    $this->assertSame('select *', $selectSql, "Case index = {$index}, Driver = {$driver}");
                }

                $updateSql = $grammar->compileUpdate($query, []);
                if ($exceptWhere !== '') {
                    // 注意这里set和where之间有两个空格
                    $this->assertSame("update `` set  where {$exceptWhere}", $updateSql, "Case index = {$index}, Driver = {$driver}");
                } else {
                    $this->assertSame('update `` set', $updateSql, "Case index = {$index}, Driver = {$driver}");
                }

                $deleteSql = $grammar->compileDelete($query);
                if ($exceptWhere !== '') {
                    $this->assertSame("delete from `` where {$exceptWhere}", $deleteSql, "Case index = {$index}, Driver = {$driver}");
                } else {
                    $this->assertSame('delete from ``', $deleteSql, "Case index = {$index}, Driver = {$driver}");
                }
            }
        }
    }

    public static function provider()
    {
        return [
            [
                'exceptWhere' => '`foo` = ?',
                'exceptBinds' => ['a'],
                'drivers' => ['hyperf'],
                'builders' => [
                    fn () => (new Constraint('foo'))->equal('a'),
                    fn () => (new Condition())->where('foo', 'a'),
                    function () {
                        $filter = new Filter();
                        $filter->foo = 'a';
                        return $filter;
                    },
                ],
            ],
            [
                'exceptWhere' => '`foo` = ? and `bar` = ?',
                'exceptBinds' => ['a', 'b'],
                'drivers' => ['hyperf'],
                'builders' => [
                    fn () => (new Condition())->where('foo', 'a')->where('bar', 'b'),
                    function () {
                        $filter = new Filter();
                        $filter->foo = 'a';
                        $filter->bar = 'b';
                        return $filter;
                    },
                    function () {
                        $filter = new Filter();
                        $filter->where('foo', 'a');
                        $filter->where('bar', 'b');
                        return $filter;
                    },
                    function () {
                        $filter = new Filter();
                        $filter->foo = 'a';
                        $filter->where('bar', 'b');
                        return $filter;
                    },
                ],
            ],
            [
                'exceptWhere' => '(`foo` = ? and `bar` = ?) or `baz` = ?',
                'exceptBinds' => ['a', 'b', 'c'],
                'drivers' => ['hyperf'],
                'builders' => [
                    fn () => (new Condition())->where(fn (Condition $condition) => $condition->where('foo', 'a')->where('bar', 'b'))->orWhere('baz', 'c'),
                ],
            ],
            [
                # 由于前面是约束，所以直接在条件中使用or会失效
                'exceptWhere' => '`foo` = ? and `bar` = ? and `baz` = ?',
                'exceptBinds' => ['a', 'b', 'c'],
                'drivers' => ['hyperf'],
                'builders' => [
                    function () {
                        $filter = new Filter();
                        $filter->foo = 'a';
                        $filter->bar = 'b';
                        $filter->orWhere(function (Filter $filter) {
                            $filter->baz = 'c';
                        });
                        return $filter;
                    },
                    function () {
                        $filter = new Filter();
                        $filter->foo = 'a';
                        $filter->bar = 'b';
                        $filter->orEqual('baz', 'c');
                        return $filter;
                    },
                    function () {
                        $filter = new Filter();
                        $filter->foo = 'a';
                        $filter->bar = 'b';
                        $filter->orWhere(function (Filter $filter) {
                            $filter->orWhere('baz', 'c');
                        });
                        return $filter;
                    },
                ],
            ],
            [
                'exceptWhere' => '`foo` = ? and `bar` = ? and (`baz` > ? or `baz` < ?)',
                'exceptBinds' => ['a', 'b', 0, 10],
                'drivers' => ['hyperf'],
                'builders' => [
                    fn () => (new Condition())->where(fn (Condition $condition) => $condition->where('foo', 'a')->where('bar', 'b'))->where(fn (Condition $condition) => $condition->where('baz', '>', 0)->orWhere('baz', '<', 10)),
                    function () {
                        $filter = new Filter();
                        $filter->foo = 'a';
                        $filter->bar = 'b';
                        $filter->where(function (Filter $filter) {
                            $filter->baz->greaterThan(0)->orLessThan(10);
                        });
                        return $filter;
                    },
                ],
            ],
            [
                'exceptWhere' => '',
                'exceptBinds' => [],
                'drivers' => ['hyperf'],
                'builders' => [
                    function () {
                        return new Filter();
                    },
                    function () {
                        $filter = new Filter();
                        $filter->foo;

                        return $filter;
                    },
                    function () {
                        $filter = new Filter();
                        $filter->foo->group(function (Constraint $constraint) {
                            $constraint->group(function (Constraint $constraint) {
                            }, Logic::AND);
                        }, Logic::AND);

                        return $filter;
                    },
                    function () {
                        $filter = new Filter();
                        $filter->foo = (new Constraint('foo'));

                        return $filter;
                    },
                    function () {
                        $filter = new Filter();
                        $filter->where(function (Filter $filter) {
                            $filter->where(function (Filter $filter) {
                            });
                        });

                        return $filter;
                    },
                ],
            ],
        ];
    }

    protected function makeQueryBuilder(string $type): HyperfBuilder
    {
        return match ($type) {
            'hyperf' => new HyperfBuilder(new HyperfConnection(null)),
        };
    }

    protected function makeGrammar(string $type): HyperfMysqlGrammar
    {
        return match ($type) {
            'hyperf' => new HyperfMysqlGrammar(),
        };
    }
}
