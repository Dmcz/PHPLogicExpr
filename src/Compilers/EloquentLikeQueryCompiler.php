<?php

declare(strict_types=1);

namespace Dmcz\LogicExpr\Compilers;

use Closure;
use Dmcz\LogicExpr\Expression;
use Dmcz\LogicExpr\ExpressionTree;
use Dmcz\LogicExpr\Filter;
use Dmcz\LogicExpr\Identifier;
use Dmcz\LogicExpr\Literal;
use Dmcz\LogicExpr\Logic;
use Dmcz\LogicExpr\Operator;
use Exception;
use UnexpectedValueException;

class EloquentLikeQueryCompiler
{
    /**
     * @param ?Closure(string):string $nameHandler The handler for field names in the where condition. Example: function(string $name): string
     * @param ?Closure(string,mixed):mixed $valueHandler The handler for field values in the where condition. Example: function(mixed $value, string $name): mixed
     *
     * @example
     * $nameHandler = function($name) {
     *     return 'prefix_' . $name;
     * };
     *
     * $valueHandler = function($name, $value) {
     *     if ($name === 'age') {
     *         return (int) $value;
     *     }
     *
     *     if ($value instanceof DataTime){
     *         return $value->format(DateTime::ATOM);
     *     }
     *     return $value;
     * };
     *
     * $processor = new EloquentLikeQueryCompiler($nameHandler, $valueHandler);
     */
    public function __construct(
        public readonly ?Closure $nameHandler = null,
        public readonly ?Closure $valueHandler = null,
    ) {
    }

    public function compile(Expression|ExpressionTree $expression, $query)
    {
        if ($expression instanceof Expression) {
            $this->compileExpression($expression, $query, logic: Logic::AND);
        } else {
            if ($expression instanceof Filter) {
                $this->compileFilter($expression, $query);
            } else {
                $this->compileExpressionTree($expression, $query);
            }
        }
    }

    public function compileFilter(Filter $filter, $query): void
    {
        $constraints = $filter->getConstraints();
        $constraintTotal = $filter->countConstraints();
        $expressionTotal = $filter->countExpressions();

        // DESIGN NOTE：区分多种情况，主要是避免没有意义的括号

        // 没有约束
        if ($constraintTotal == 0 && $expressionTotal > 0) {
            $this->compileExpressionTree($filter, $query);
            return;
        }

        // 仅有约束
        if ($constraintTotal > 0 && $expressionTotal == 0) {
            $this->compileConstraints($constraints, $query);
            return;
        }

        // 约束优先与条件
        // 约束和条件表达式之间的关系为且
        $this->compileConstraints($constraints, $query);

        if ($expressionTotal == 1) {  // 单条表达式
            $this->compileExpressionTree($filter, $query);
        } else { // 多条表达式
            if ($filter->getLogic() === Logic::AND) { // 当逻辑是与的是可以省略括号
                $this->compileExpressionTree($filter, $query);
            } else {
                $query->where(function ($query) use ($filter) {
                    $this->compileExpressionTree($filter, $query);
                });
            }
        }
    }

    public function compileConstraints(array $constraints, $query): void
    {
        foreach ($constraints as $constraint) {
            // 多个约束间的关系为且
            // 单条约束中存在多个表达式时需要被包裹
            if ($constraint->countExpressions() > 1) {
                if ($constraint->getLogic() == Logic::AND) {
                    $this->compileExpressionTree($constraint, $query);
                } else {
                    $query->where(function ($query) use ($constraint) {
                        $this->compileExpressionTree($constraint, $query);
                    });
                }
            } else {
                $this->compileExpressionTree($constraint, $query);
            }
        }
    }

    public function compileExpressionTree(ExpressionTree $expressionTree, $query)
    {
        foreach ($expressionTree->getExpressions() as $subExpression) {
            $logic = $expressionTree->getLogic();
            if ($logic === null) {
                $logic = Logic::AND;
            }

            if ($subExpression instanceof Expression) {
                $this->compileExpression($subExpression, $query, $logic);
            } elseif ($subExpression instanceof Filter) {
                if ($expressionTree->getLogic() == $subExpression->getLogic() || ($expressionTree->getLogic() == null && $subExpression->getLogic() == Logic::AND)) { # 相同逻辑可以省略括号
                    $this->compileFilter($subExpression, $query, $logic);
                } else {
                    $query->where(function ($query) use ($subExpression, $logic) {
                        $this->compileExpressionTree($subExpression, $query, $logic);
                    }, boolean: $logic->value);
                }
            } elseif ($subExpression instanceof ExpressionTree) {
                if (($expressionTree->getLogic() == $subExpression->getLogic()) || ($expressionTree->getLogic() == null && $subExpression->getLogic() == Logic::AND)) { # 相同逻辑可以省略括号
                    $this->compileExpressionTree($subExpression, $query, $logic);
                } else {
                    $query->where(function ($query) use ($subExpression, $logic) {
                        $this->compileExpressionTree($subExpression, $query, $logic);
                    }, boolean: $logic->value);
                }
            } else {
                throw new Exception('The express not support');
            }
        }
    }

    public function compileExpression(Expression $expression, $query, Logic $logic)
    {
        [$left, $right] = $this->ensureOperand($expression->left, $expression->right);

        switch ($expression->operator) {
            case Operator::EQ:
                $query->where($left, '=', $right, $logic->value);
                break;
            case Operator::NEQ:
                $query->where($left, '<>', $right, $logic->value);
                break;
            case Operator::GT:
                $query->where($left, '>', $right, $logic->value);
                break;
            case Operator::GTE:
                $query->where($left, '>=', $right, $logic->value);
                break;
            case Operator::LT:
                $query->where($left, '<', $right, $logic->value);
                break;
            case Operator::LTE:
                $query->where($left, '<=', $right, $logic->value);
                break;
            case Operator::IN:
                $query->whereIn($left, $right, $logic->value);
                break;
            case Operator::NOT_IN:
                $query->whereIn($left, $right, $logic->value, true);
                break;
            case Operator::IS_NULL:
                $query->whereNull($left, $logic->value);
                break;
            case Operator::NOT_NULL:
                $query->whereNull($left, $logic->value, true);
                break;
            case Operator::CONTAIN:
                $query->where($left, 'like', '%' . $right . '%');
                break;
            case Operator::START_WITH:
                $query->where($left, 'like', $right . '%');
                break;
            case Operator::END_WITH:
                $query->where($left, 'like', '%' . $right);
                break;
            default:
                throw new UnexpectedValueException('The expression not support.');
        }
    }

    public function ensureOperand(Identifier|Literal|null $left, Identifier|Literal|null $right): array
    {
        $leftIsId = $left instanceof Identifier;
        $rightIsId = $right instanceof Identifier;

        if ($leftIsId && $rightIsId) { // 两个都是字段
            return [
                $this->ensureName($left->name),
                $this->ensureName($right->name),
            ];
        }
        if ($leftIsId) { // 左边是字段
            return [
                $this->ensureName($left->name),
                $this->ensureValue($left->name, $right instanceof Literal ? $right->value : null),
            ];
        }
        if ($rightIsId) { // 右边是字段
            return [
                $this->ensureValue($right->name, $left instanceof Literal ? $left->value : null),
                $this->ensureName($right->name),
            ];
        }

        // 都是值
        return [
            $left instanceof Literal ? $left->value : $left,
            $right instanceof Literal ? $right->value : $right,
        ];
    }

    protected function ensureName(string $name): string
    {
        if ($this->nameHandler !== null) {
            return call_user_func($this->nameHandler, $name);
        }
        return $name;
    }

    protected function ensureValue(string $name, mixed $value): mixed
    {
        if ($this->valueHandler !== null) {
            return call_user_func($this->valueHandler, $name, $value);
        }
        return $value;
    }
}
