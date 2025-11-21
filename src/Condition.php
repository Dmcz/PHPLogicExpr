<?php

declare(strict_types=1);

namespace Dmcz\FilterBlocks;

use Closure;
use Dmcz\FilterBlocks\Compilers\Explainer;
use InvalidArgumentException;

class Condition extends ExpressionTree
{
    /**
     * 解释器.
     */
    protected Explainer $explainer;

    public function __construct(
    ) {
        $this->explainer = new Explainer();
    }

    /**
     * @return array{0:Condition|Expression,1:Logic}[]
     */
    public function expressions(): array
    {
        return $this->expressions;
    }

    public function equal(string $name, mixed $value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::equal($name, $value), $logic);
    }

    public function orEqual(string $name, mixed $value): static
    {
        return $this->equal($name, $value, Logic::OR);
    }

    public function notEqual(string $name, mixed $value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::notEqual($name, $value), $logic);
    }

    public function orNotEqual(string $name, mixed $value): static
    {
        return $this->notEqual($name, $value, Logic::OR);
    }

    public function greaterThan(string $name, mixed $value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::greaterThan($name, $value), $logic);
    }

    public function orGreaterThan(string $name, mixed $value): static
    {
        return $this->greaterThan($name, $value, Logic::OR);
    }

    public function greaterEqual(string $name, mixed $value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::greaterEqual($name, $value), $logic);
    }

    public function orGreaterEqual(string $name, mixed $value): static
    {
        return $this->greaterEqual($name, $value, Logic::OR);
    }

    public function lessThan(string $name, mixed $value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::lessThan($name, $value), $logic);
    }

    public function orLessThan(string $name, mixed $value): static
    {
        return $this->lessThan($name, $value, Logic::OR);
    }

    public function lessEqual(string $name, mixed $value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::lessEqual($name, $value), $logic);
    }

    public function orLessEqual(string $name, mixed $value): static
    {
        return $this->lessEqual($name, $value, Logic::OR);
    }

    public function where(Closure|string $name, mixed $operator = null, mixed $value = null, Logic $logic = Logic::AND): static
    {
        // 仅传入闭包，则是分组逻辑
        if ($name instanceof Closure) {
            return $this->group($name, $logic);
        }

        // 如果操作符和值全是为空则抛出异常
        if (is_null($operator) && is_null($value)) {
            throw new InvalidArgumentException('Value and operator is required.');
        }

        // 如果传入的不是操作符，则认为是等于
        if ($value === null && ! $operator instanceof Operator) {
            $value = $operator;
            $operator = Operator::EQ;
        }

        return $this->append(Expr::make($name, $operator, $value), $logic);
    }

    public function orWhere(Closure|string $name, mixed $operator = null, mixed $value = null): static
    {
        return $this->where($name, $operator, $value, logic: Logic::OR);
    }

    public function isNull(string $name, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::isNull($name), $logic);
    }

    public function orIsNull(string $name): static
    {
        return $this->isNull($name, Logic::OR);
    }

    public function whereNull(string $name, Logic $logic = Logic::AND): static
    {
        return $this->isNull($name, $logic);
    }

    public function orWhereNull(string $name): static
    {
        return $this->whereNull($name, Logic::OR);
    }

    public function notNull(string $name, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::notNull($name), $logic);
    }

    public function orNotNull(string $name): static
    {
        return $this->notNull($name, Logic::OR);
    }

    public function whereNotNull(string $name, Logic $logic = Logic::AND): static
    {
        return $this->notNull($name, $logic);
    }

    public function orWhereNotNull(string $name): static
    {
        return $this->whereNotNull($name, Logic::OR);
    }

    public function in(string $name, array $value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::in($name, $value), $logic);
    }

    public function orIn(string $name, array $value): static
    {
        return $this->in($name, $value, Logic::OR);
    }

    public function whereIn(string $name, array $value, Logic $logic = Logic::AND): static
    {
        return $this->in($name, $value, $logic);
    }

    public function orWhereIn(string $name, array $value): static
    {
        return $this->whereIn($name, $value, Logic::OR);
    }

    public function notIn(string $name, array $value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::notIn($name, $value), $logic);
    }

    public function orNotIn(string $name, array $value): static
    {
        return $this->notIn($name, $value, Logic::OR);
    }

    public function whereNotIn(string $name, array $value, Logic $logic = Logic::AND): static
    {
        return $this->notIn($name, $value, $logic);
    }

    public function orWhereNotIn(string $name, array $value): static
    {
        return $this->whereNotIn($name, $value, Logic::OR);
    }

    public function contain(string $name, string $value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::contain($name, $value), $logic);
    }

    public function orContain(string $name, string $value): static
    {
        return $this->contain($name, $value, Logic::OR);
    }

    public function whereContain(string $name, string $value, Logic $logic = Logic::AND): static
    {
        return $this->contain($name, $value, $logic);
    }

    public function orWhereContain(string $name, string $value): static
    {
        return $this->whereContain($name, $value, Logic::OR);
    }

    public function startWith(string $name, string $value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::startWith($name, $value), $logic);
    }

    public function orStartWith(string $name, string $value): static
    {
        return $this->startWith($name, $value, Logic::OR);
    }

    public function whereStartWith(string $name, string $value, Logic $logic = Logic::AND): static
    {
        return $this->startWith($name, $value, $logic);
    }

    public function orWhereStartWith(string $name, string $value): static
    {
        return $this->whereStartWith($name, $value, Logic::OR);
    }

    public function endWith(string $name, string $value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::endWith($name, $value), $logic);
    }

    public function orEndWith(string $name, string $value): static
    {
        return $this->endWith($name, $value, Logic::OR);
    }

    public function whereEndWith(string $name, string $value, Logic $logic = Logic::AND): static
    {
        return $this->endWith($name, $value, $logic);
    }

    public function orWhereEndWith(string $name, string $value): static
    {
        return $this->whereEndWith($name, $value, Logic::OR);
    }

    public function group(callable $callback, Logic $logic): static
    {
        $condition = new static();
        $this->append($condition, $logic);

        $callback($condition);
        return $this;
    }

    public function or(callable $callback): static
    {
        return $this->group($callback, Logic::OR);
    }

    public function and(callable $callback): static
    {
        return $this->group($callback, Logic::AND);
    }

    public function explain(): string
    {
        return $this->explainer->compileExpressionTree($this);
    }
}
